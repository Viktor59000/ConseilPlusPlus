package fr.conseilplus.app

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.Gravity
import android.view.View
import android.webkit.WebResourceRequest
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.FrameLayout
import android.widget.TextView
import fr.conseilplus.app.widget.MiseAJourWorker

/**
 * L'appli : affiche le site ConseilPlus+ en plein écran.
 * Les liens du site restent dans l'appli ; les liens externes s'ouvrent dans le navigateur.
 * Extra « chemin » (ex. petit-moyen.php) : page à ouvrir, utilisé par le widget.
 */
class MainActivity : Activity() {

    private lateinit var web: WebView
    private lateinit var attente: TextView

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        web = WebView(this).apply {
            settings.javaScriptEnabled = true
            settings.domStorageEnabled = true
            setBackgroundColor(getColor(R.color.papier))
            webViewClient = object : WebViewClient() {
                override fun shouldOverrideUrlLoading(view: WebView, requete: WebResourceRequest): Boolean {
                    if (requete.url.host == Uri.parse(SITE).host) return false
                    startActivity(Intent(Intent.ACTION_VIEW, requete.url))
                    return true
                }

                override fun onPageFinished(view: WebView, url: String) {
                    attente.visibility = View.GONE
                }
            }
        }

        // Le site gratuit peut mettre une minute à se réveiller : on le dit, avec le ton du cabinet
        attente = TextView(this).apply {
            text = getString(R.string.attente)
            setTextColor(getColor(R.color.encre))
            setBackgroundColor(getColor(R.color.papier))
            textSize = 18f
            gravity = Gravity.CENTER
            setPadding(48, 48, 48, 48)
        }

        setContentView(FrameLayout(this).apply {
            addView(web)
            addView(attente)
        })

        if (savedInstanceState != null) {
            web.restoreState(savedInstanceState)
            attente.visibility = View.GONE
        } else {
            web.loadUrl(adresse(intent))
        }

        // Appli au premier plan : Android laisse le réseau passer, on en profite pour recharger le widget
        MiseAJourWorker.maintenant(this)
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        if (intent.hasExtra(EXTRA_CHEMIN)) web.loadUrl(adresse(intent))
    }

    override fun onSaveInstanceState(outState: Bundle) {
        super.onSaveInstanceState(outState)
        web.saveState(outState)
    }

    // Retour arrière : on remonte d'abord l'historique du site
    @Deprecated("Remplacé par OnBackPressedDispatcher (androidx), inutile pour cette appli sans dépendance")
    override fun onBackPressed() {
        if (web.canGoBack()) {
            web.goBack()
        } else {
            super.onBackPressed()
        }
    }

    private fun adresse(intent: Intent?): String =
        SITE + (intent?.getStringExtra(EXTRA_CHEMIN) ?: "index.php?source=appli")

    companion object {
        const val SITE = "https://conseilplus.onrender.com/"
        const val EXTRA_CHEMIN = "chemin"
    }
}
