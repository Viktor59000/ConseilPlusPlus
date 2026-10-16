// Application Android de ConseilPlus+ : le site dans une appli + le widget « mot du jour ».
// Construite par GitHub Actions (.github/workflows/android.yml), aucune installation locale nécessaire.
pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}
dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google()
        mavenCentral()
    }
}
rootProject.name = "ConseilPlus"
include(":app")
