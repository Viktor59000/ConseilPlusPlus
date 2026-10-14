package fr.conseilplus.app.widget

import android.app.PendingIntent
import android.appwidget.AppWidgetManager
import android.appwidget.AppWidgetProvider
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.widget.RemoteViews
import fr.conseilplus.app.MainActivity
import fr.conseilplus.app.R
import org.json.JSONObject

/**
 * Widget « Le Petit Moyen » : mot (presque) du jour, sa définition (ou la chute du sketch) et un conseil.
 * Les données viennent de api/widget.php, chargées par MiseAJourWorker et gardées en mémoire :
 * le widget affiche toujours la dernière version reçue, même hors ligne.
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

    override fun onReceive(context: Context, intent: Intent) {
        super.onReceive(context, intent)
        if (intent.action == ACTION_RAFRAICHIR) {
            MiseAJourWorker.maintenant(context)
        }
    }

    companion object {
        const val ACTION_RAFRAICHIR = "fr.conseilplus.app.RAFRAICHIR"
        private const val PREFS = "widget"
        private const val CLE_JSON = "derniere_reponse"

        fun enregistrer(context: Context, json: String) {
            context.getSharedPreferences(PREFS, Context.MODE_PRIVATE).edit().putString(CLE_JSON, json).apply()
        }

        /** Redessine tous les widgets posés avec la dernière réponse connue. */
        fun afficher(context: Context) {
            val manager = AppWidgetManager.getInstance(context)
            val ids = manager.getAppWidgetIds(ComponentName(context, MotDuJourWidget::class.java))
            if (ids.isEmpty()) return

            val vues = RemoteViews(context.packageName, R.layout.widget_mot)
            val json = context.getSharedPreferences(PREFS, Context.MODE_PRIVATE).getString(CLE_JSON, null)
            if (json != null) {
                val d = JSONObject(json)
                vues.setTextViewText(R.id.date, d.optString("date").uppercase())
                vues.setTextViewText(R.id.mot, d.optString("mot"))
                vues.setTextViewText(R.id.nature, d.optString("nature"))
                vues.setTextViewText(R.id.ligne1, d.optString("ligne1"))
                vues.setTextViewText(R.id.conseil, d.optString("conseil"))
            } else {
                vues.setTextViewText(R.id.date, context.getString(R.string.widget_titre).uppercase())
                vues.setTextViewText(R.id.mot, context.getString(R.string.widget_chargement))
                vues.setTextViewText(R.id.nature, "")
                vues.setTextViewText(R.id.ligne1, context.getString(R.string.widget_chargement_detail))
                vues.setTextViewText(R.id.conseil, "")
            }

            // Toucher le widget : ouvre Le Petit Moyen dans l'appli
            val ouvrir = Intent(context, MainActivity::class.java)
                .putExtra(MainActivity.EXTRA_CHEMIN, "petit-moyen.php")
                .addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
            vues.setOnClickPendingIntent(
                R.id.racine,
                PendingIntent.getActivity(context, 0, ouvrir, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
            )
            // Toucher ↻ : nouveau conseil
            val rafraichir = Intent(context, MotDuJourWidget::class.java).setAction(ACTION_RAFRAICHIR)
            vues.setOnClickPendingIntent(
                R.id.rafraichir,
                PendingIntent.getBroadcast(context, 1, rafraichir, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
            )

            manager.updateAppWidget(ids, vues)
        }
    }
}
