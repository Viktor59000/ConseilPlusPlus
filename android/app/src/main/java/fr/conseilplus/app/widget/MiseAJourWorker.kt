package fr.conseilplus.app.widget

import android.content.Context
import androidx.work.Constraints
import androidx.work.ExistingPeriodicWorkPolicy
import androidx.work.ExistingWorkPolicy
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.PeriodicWorkRequestBuilder
import androidx.work.WorkManager
import androidx.work.Worker
import androidx.work.WorkerParameters
import androidx.work.workDataOf
import fr.conseilplus.app.MainActivity
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL
import java.util.concurrent.TimeUnit

/**
 * Charge api/widget.php et met le widget à jour. Lancé à la pose du widget, toutes les 3 heures,
 * et quand on touche ↻ (NOUVEAU_CONSEIL : remplace le conseil du jour). Délais longs : le site gratuit peut mettre une minute à se réveiller.
 */
class MiseAJourWorker(context: Context, params: WorkerParameters) : Worker(context, params) {

    override fun doWork(): Result {
        val connexion = URL(MainActivity.SITE + "api/widget.php").openConnection() as HttpURLConnection
        return try {
            connexion.connectTimeout = 90_000
            connexion.readTimeout = 90_000
            connexion.setRequestProperty("Accept", "application/json")
            connexion.setRequestProperty("Cache-Control", "no-cache")
            if (connexion.responseCode != 200) return Result.retry()
            val json = connexion.inputStream.bufferedReader(Charsets.UTF_8).use { it.readText() }
            JSONObject(json).getString("mot") // vérifie que la réponse est complète
            MotDuJourWidget.enregistrer(applicationContext, json, inputData.getBoolean(NOUVEAU_CONSEIL, false))
            MotDuJourWidget.afficher(applicationContext)
            Result.success()
        } catch (e: Exception) {
            if (runAttemptCount < 3) Result.retry() else Result.failure()
        } finally {
            connexion.disconnect()
        }
    }

    companion object {
        private const val UNIQUE = "widget-maintenant"
        private const val PERIODIQUE = "widget-periodique"
        private const val NOUVEAU_CONSEIL = "nouveau_conseil"
        private val reseau = Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build()

        fun maintenant(context: Context, nouveauConseil: Boolean = false) {
            val requete = OneTimeWorkRequestBuilder<MiseAJourWorker>()
                .setConstraints(reseau)
                .setInputData(workDataOf(NOUVEAU_CONSEIL to nouveauConseil))
                .build()
            // File séparée pour ↻ : une mise à jour automatique simultanée ne l'annule pas
            val nom = if (nouveauConseil) "$UNIQUE-conseil" else UNIQUE
            WorkManager.getInstance(context).enqueueUniqueWork(nom, ExistingWorkPolicy.REPLACE, requete)
        }

        fun planifier(context: Context) {
            val requete = PeriodicWorkRequestBuilder<MiseAJourWorker>(3, TimeUnit.HOURS).setConstraints(reseau).build()
            WorkManager.getInstance(context).enqueueUniquePeriodicWork(PERIODIQUE, ExistingPeriodicWorkPolicy.KEEP, requete)
        }

        fun annuler(context: Context) {
            WorkManager.getInstance(context).cancelUniqueWork(PERIODIQUE)
        }
    }
}
