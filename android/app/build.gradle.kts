plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

// Numéro de version = numéro de construction GitHub : chaque nouvel APK s'installe par-dessus le précédent
val numero = (System.getenv("GITHUB_RUN_NUMBER") ?: "1").toInt()

android {
    namespace = "fr.conseilplus.app"
    compileSdk = 35

    defaultConfig {
        applicationId = "fr.conseilplus.app"
        minSdk = 24
        targetSdk = 34 // 35 imposerait l'affichage bord à bord (site sous la barre d'état)
        versionCode = numero
        versionName = "1.0.$numero"
    }

    // Clé de signature : jamais dans le dépôt. GitHub Actions la reconstitue depuis ses secrets
    // (ANDROID_CLE_P12 en base64, ANDROID_CLE_MDP) ; en local, définir les mêmes variables d'environnement.
    // La même clé doit servir pour toutes les versions, sinon Android refuse la mise à jour.
    signingConfigs {
        create("partage") {
            storeFile = file(System.getenv("ANDROID_CLE_FICHIER") ?: "conseilplus.p12")
            storeType = "pkcs12"
            storePassword = System.getenv("ANDROID_CLE_MDP")
            keyAlias = "conseilplus"
            keyPassword = System.getenv("ANDROID_CLE_MDP")
        }
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            signingConfig = signingConfigs.getByName("partage")
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
    kotlinOptions {
        jvmTarget = "17"
    }
}

dependencies {
    implementation("androidx.work:work-runtime-ktx:2.9.1")
}
