package fr.conseilplus.app.widget

import android.app.PendingIntent
import android.appwidget.AppWidgetManager
import android.appwidget.AppWidgetProvider
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.content.SharedPreferences
import android.view.View
import android.widget.RemoteViews
import fr.conseilplus.app.MainActivity
import fr.conseilplus.app.R
import org.json.JSONObject

/**
 * Widget-carte à deux faces : « Conseil du jour » et « Le Petit Moyen » (mot du jour).
 * - ⇄ retourne la carte (chaque widget posé garde sa face) ;
 * - ↻ (face conseil) tire un autre conseil ;
 * - toucher la carte ouvre la page correspondante dans l'appli.
 * Le conseil reste le même toute la journée (sauf ↻). Les données viennent de api/widget.php
 * (MiseAJourWorker) et sont gardées en mémoire : la carte s'affiche même hors ligne.
 */
class MotDuJourWidget : AppWidgetProvider() {

    override fun onUpdate(context: Context, manager: AppWidgetManager, ids: IntArray) {
        afficher(context)
        MiseAJourWorker.maintenant(context)
    }

    override fun onEnabled(context: Context) {
        MiseAJourWorker.planifier(context)
    }

    override fun onDisabled(context: Context) {
        MiseAJourWorker.annuler(context)
    }

    override fun onDeleted(context: Context, ids: IntArray) {
        prefs(context).edit().apply { ids.forEach { remove(cleFace(it)) } }.apply()
    }

    override fun onReceive(context: Context, intent: Intent) {
        super.onReceive(context, intent)
        when (intent.action) {
            ACTION_RAFRAICHIR -> {
                // Réaction immédiate pendant que le nouveau conseil arrive (jusqu'à une minute si le site dort)
                val manager = AppWidgetManager.getInstance(context)
                val vues = RemoteViews(context.packageName, R.layout.widget_mot)
                vues.setTextViewText(R.id.conseil, context.getString(R.string.widget_reflexion))
                manager.partiallyUpdateAppWidget(manager.getAppWidgetIds(ComponentName(context, MotDuJourWidget::class.java)), vues)
                MiseAJourWorker.maintenant(context, nouveauConseil = true)
            }
            ACTION_RETOURNER -> {
                val id = intent.getIntExtra(AppWidgetManager.EXTRA_APPWIDGET_ID, AppWidgetManager.INVALID_APPWIDGET_ID)
                if (id == AppWidgetManager.INVALID_APPWIDGET_ID) return
                val p = prefs(context)
                val face = if (p.getString(cleFace(id), FACE_CONSEIL) == FACE_CONSEIL) FACE_MOT else FACE_CONSEIL
                p.edit().putString(cleFace(id), face).apply()
                afficher(context)
            }
        }
    }

    companion object {
        const val ACTION_RAFRAICHIR = "fr.conseilplus.app.RAFRAICHIR"
        const val ACTION_RETOURNER = "fr.conseilplus.app.RETOURNER"
        private const val FACE_CONSEIL = "conseil"
        private const val FACE_MOT = "mot"
        private const val PREFS = "widget"
        private const val CLE_JSON = "derniere_reponse"
        private const val CLE_CONSEIL = "conseil"
        private const val CLE_DOSSIER = "dossier"
        private const val CLE_JOUR_CONSEIL = "jour_conseil"

        private fun prefs(context: Context): SharedPreferences =
            context.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

        private fun cleFace(id: Int) = "face_$id"

        /**
         * Enregistre une réponse de l'API. Le conseil n'est remplacé que s'il en faut un nouveau
         * (premier chargement, nouveau jour, ou ↻) : c'est le conseil « du jour ».
         */
        fun enregistrer(context: Context, json: String, nouveauConseil: Boolean) {
            val d = JSONObject(json)
            val p = prefs(context)
            val edition = p.edit().putString(CLE_JSON, json)
            if (nouveauConseil || p.getString(CLE_JOUR_CONSEIL, null) != d.optString("date")) {
                edition.putString(CLE_CONSEIL, d.optString("conseil"))
                    .putString(CLE_DOSSIER, d.optString("dossier"))
                    .putString(CLE_JOUR_CONSEIL, d.optString("date"))
            }
            edition.apply()
        }

        /** Redessine tous les widgets posés, chacun sur sa face, avec les dernières données connues. */
        fun afficher(context: Context) {
            val manager = AppWidgetManager.getInstance(context)
            val ids = manager.getAppWidgetIds(ComponentName(context, MotDuJourWidget::class.java))
            val p = prefs(context)
            val json = p.getString(CLE_JSON, null)?.let { JSONObject(it) }

            for (id in ids) {
                val vues = RemoteViews(context.packageName, R.layout.widget_mot)
                val face = p.getString(cleFace(id), FACE_CONSEIL)

                // Face conseil
                vues.setTextViewText(R.id.conseil, p.getString(CLE_CONSEIL, null) ?: context.getString(R.string.widget_chargement_detail))
                vues.setTextViewText(R.id.dossier, p.getString(CLE_DOSSIER, null)?.let { "N° $it" } ?: "")
                // Face mot
                if (json != null) {
                    vues.setTextViewText(R.id.date, json.optString("date").uppercase())
                    vues.setTextViewText(R.id.mot, json.optString("mot"))
                    vues.setTextViewText(R.id.nature, json.optString("nature"))
                    vues.setTextViewText(R.id.ligne1, json.optString("ligne1"))
                    vues.setTextViewText(R.id.ligne2, json.optString("ligne2"))
                } else {
                    vues.setTextViewText(R.id.date, context.getString(R.string.widget_titre).uppercase())
                    vues.setTextViewText(R.id.mot, context.getString(R.string.widget_chargement))
                    vues.setTextViewText(R.id.ligne1, context.getString(R.string.widget_chargement_detail))
                }

                val surMot = face == FACE_MOT
                vues.setViewVisibility(R.id.face_mot, if (surMot) View.VISIBLE else View.GONE)
                vues.setViewVisibility(R.id.face_conseil, if (surMot) View.GONE else View.VISIBLE)

                // Toucher la carte : la page correspondante dans l'appli
                val ouvrir = Intent(context, MainActivity::class.java)
                    .putExtra(MainActivity.EXTRA_CHEMIN, if (surMot) "petit-moyen.php" else "index.php")
                    .addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                vues.setOnClickPendingIntent(
                    R.id.racine,
                    PendingIntent.getActivity(context, id * 10, ouvrir, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
                )
                // ⇄ : retourner cette carte-ci
                val retourner = Intent(context, MotDuJourWidget::class.java)
                    .setAction(ACTION_RETOURNER)
                    .putExtra(AppWidgetManager.EXTRA_APPWIDGET_ID, id)
                val intentionRetourner = PendingIntent.getBroadcast(
                    context, id * 10 + 1, retourner, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
                )
                vues.setOnClickPendingIntent(R.id.retourner_conseil, intentionRetourner)
                vues.setOnClickPendingIntent(R.id.retourner_mot, intentionRetourner)
                // ↻ : un autre conseil
                val rafraichir = Intent(context, MotDuJourWidget::class.java).setAction(ACTION_RAFRAICHIR)
                vues.setOnClickPendingIntent(
                    R.id.rafraichir,
                    PendingIntent.getBroadcast(context, 2, rafraichir, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
                )

                manager.updateAppWidget(id, vues)
            }
        }
    }
}
