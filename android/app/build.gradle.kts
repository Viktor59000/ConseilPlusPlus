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

    // Clé de signature partagée (APK distribué hors Play Store) : la même pour toutes les versions,
    // sinon Android refuse la mise à jour. À remplacer par une clé secrète avant une publication sur le Play Store.
    signingConfigs {
        create("partage") {
            storeFile = file("conseilplus.p12")
            storeType = "pkcs12"
            storePassword = "conseilplus"
            keyAlias = "conseilplus"
            keyPassword = "conseilplus"
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
