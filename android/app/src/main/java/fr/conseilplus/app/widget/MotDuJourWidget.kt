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
import org.json.JSONArray
import org.json.JSONObject
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

/**
 * Widget-carte à deux faces : « Conseil du jour » et « Le Petit Moyen » (mot du jour).
 * - « ⇄ Mot » / « ⇄ Conseil » retourne la carte (chaque widget posé garde sa face) ;
 * - ↻ passe au conseil suivant ;
 * - toucher la carte ouvre la page correspondante dans l'appli.
 *
 * Tout ce qui se touche est local et instantané : Android repousse le réseau des applis en arrière-plan,
 * et le site gratuit met jusqu'à une minute à se réveiller. Le widget garde donc une réserve de conseils
 * (api/widget.php?conseils=12), rechargée en arrière-plan par MiseAJourWorker quand elle baisse.
 * Le conseil du jour change au premier affichage d'une nouvelle journée.
 */
class MotDuJourWidget : AppWidgetProvider() {

    override fun onUpdate(context: Context, manager: AppWidgetManager, ids: IntArray) {
        afficher(context)
        MiseAJourWorker.maintenant(context)
        MiseAJourWorker.planifier(context)
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
            ACTION_SUIVANT -> {
                if (!conseilSuivant(context)) {
                    // Réserve vide (premier lancement hors ligne) : on prévient et on recharge
                    afficher(context, context.getString(R.string.widget_reflexion))
                    MiseAJourWorker.maintenant(context)
                    return
                }
                afficher(context)
                if (reserve(context).length() < SEUIL_RECHARGE) MiseAJourWorker.maintenant(context)
            }
            ACTION_RETOURNER -> {
                val id = intent.getIntExtra(AppWidgetManager.EXTRA_APPWIDGET_ID, AppWidgetManager.INVALID_APPWIDGET_ID)
                if (id == AppWidgetManager.INVALID_APPWIDGET_ID) return
                val p = prefs(context)
                val face = if (p.getString(cleFace(id), FACE_CONSEIL) == FACE_CONSEIL) FACE_MOT else FACE_CONSEIL
                p.edit().putString(cleFace(id), face).commit()
                afficher(context)
            }
        }
    }

    companion object {
        private const val ACTION_SUIVANT = "fr.conseilplus.app.CONSEIL_SUIVANT"
        private const val ACTION_RETOURNER = "fr.conseilplus.app.RETOURNER"
        private const val FACE_CONSEIL = "conseil"
        private const val FACE_MOT = "mot"
        private const val PREFS = "widget"
        private const val CLE_JSON = "derniere_reponse"
        private const val CLE_RESERVE = "reserve_conseils"
        private const val CLE_CONSEIL = "conseil"
        private const val CLE_DOSSIER = "dossier"
        private const val CLE_JOUR = "jour_conseil"
        private const val SEUIL_RECHARGE = 4

        private fun prefs(context: Context): SharedPreferences =
            context.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

        private fun cleFace(id: Int) = "face_$id"

        private fun aujourdhui(): String = SimpleDateFormat("yyyy-MM-dd", Locale.FRANCE).format(Date())

        private fun reserve(context: Context): JSONArray =
            prefs(context).getString(CLE_RESERVE, null)?.let { JSONArray(it) } ?: JSONArray()

        /** Sort le prochain conseil de la réserve et en fait le conseil affiché. Faux si la réserve est vide. */
        private fun conseilSuivant(context: Context): Boolean {
            val file = reserve(context)
            if (file.length() == 0) return false
            val c = file.getJSONObject(0)
            file.remove(0)
            prefs(context).edit()
                .putString(CLE_CONSEIL, c.optString("texte"))
                .putString(CLE_DOSSIER, c.optString("dossier"))
                .putString(CLE_JOUR, aujourdhui())
                .putString(CLE_RESERVE, file.toString())
                .commit()
            return true
        }

        /** Enregistre une réponse de l'API (mot du jour + réserve de conseils, ajoutée à la suite). */
        fun enregistrer(context: Context, json: String) {
            val d = JSONObject(json)
            val file = reserve(context)
            d.optJSONArray("conseils")?.let { nouveaux ->
                for (i in 0 until nouveaux.length()) file.put(nouveaux.getJSONObject(i))
            }
            // Pas de réserve dans la réponse : au moins le conseil unique
            if (file.length() == 0 && d.has("conseil")) {
                file.put(JSONObject().put("texte", d.optString("conseil")).put("dossier", d.optString("dossier")))
            }
            prefs(context).edit().putString(CLE_JSON, json).putString(CLE_RESERVE, file.toString()).commit()
        }

        /** Il faut recharger s'il reste peu de conseils en réserve. */
        fun reserveBasse(context: Context): Boolean = reserve(context).length() < SEUIL_RECHARGE

        /**
         * Redessine tous les widgets posés, chacun sur sa face, avec les données en mémoire.
         * Nouveau jour (ou aucun conseil encore) : le conseil du jour est pris dans la réserve.
         */
        fun afficher(context: Context, messageConseil: String? = null) {
            val p = prefs(context)
            if (p.getString(CLE_JOUR, null) != aujourdhui() || p.getString(CLE_CONSEIL, null) == null) {
                conseilSuivant(context)
            }
            val manager = AppWidgetManager.getInstance(context)
            val ids = manager.getAppWidgetIds(ComponentName(context, MotDuJourWidget::class.java))
            val json = p.getString(CLE_JSON, null)?.let { JSONObject(it) }

            for (id in ids) {
                val vues = RemoteViews(context.packageName, R.layout.widget_mot)
                val surMot = p.getString(cleFace(id), FACE_CONSEIL) == FACE_MOT

                // Face conseil
                vues.setTextViewText(
                    R.id.conseil,
                    messageConseil ?: p.getString(CLE_CONSEIL, null) ?: context.getString(R.string.widget_chargement_conseil)
                )
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

                vues.setViewVisibility(R.id.face_mot, if (surMot) View.VISIBLE else View.GONE)
                vues.setViewVisibility(R.id.face_conseil, if (surMot) View.GONE else View.VISIBLE)

                // Toucher la carte : la page correspondante dans l'appli
                val ouvrir = Intent(context, MainActivity::class.java)
                    .putExtra(MainActivity.EXTRA_CHEMIN, if (surMot) "petit-moyen.php" else "index.php")
                    .addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                val pageMot = PendingIntent.getActivity(
                    context, id * 10, ouvrir, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
                )
                vues.setOnClickPendingIntent(R.id.corps_conseil, pageMot)
                vues.setOnClickPendingIntent(R.id.corps_mot, pageMot)

                // Retourner cette carte-ci
                val retourner = Intent(context, MotDuJourWidget::class.java)
                    .setAction(ACTION_RETOURNER)
                    .putExtra(AppWidgetManager.EXTRA_APPWIDGET_ID, id)
                val intentionRetourner = PendingIntent.getBroadcast(
                    context, id * 10 + 1, retourner, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
                )
                vues.setOnClickPendingIntent(R.id.retourner_conseil, intentionRetourner)
                vues.setOnClickPendingIntent(R.id.retourner_mot, intentionRetourner)

                // Conseil suivant
                val suivant = Intent(context, MotDuJourWidget::class.java).setAction(ACTION_SUIVANT)
                vues.setOnClickPendingIntent(
                    R.id.rafraichir,
                    PendingIntent.getBroadcast(context, id * 10 + 2, suivant, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
                )

                manager.updateAppWidget(id, vues)
            }
        }
    }
}
