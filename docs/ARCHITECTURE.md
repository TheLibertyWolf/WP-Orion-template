# Architecture

`functions.php` initialise les supports WordPress, les assets, les composants éditoriaux, les publicités compatibles et le SEO de repli. `inc/settings.php` contient l’unique schéma et les valeurs par défaut. `inc/admin.php` génère les sept écrans et applique une validation par type. `inc/category-meta.php` gère les données de rubrique via `term_meta`. `inc/consent.php` et `assets/js/consent.js` forment le chargeur de consentement.

Les templates restent classiques et surchargeables dans un thème enfant. Le design repose sur des variables CSS produites depuis les réglages ; les huit presets n’ajoutent que des variations de composition et d’expression. Le Header dispose de son propre groupe de variables et les niveaux H1 à H6 de variables dédiées afin d’éviter les styles codés en dur.
