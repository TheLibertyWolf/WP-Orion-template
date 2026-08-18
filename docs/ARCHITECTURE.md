# Architecture

`functions.php` initialise les supports WordPress, les assets, les composants éditoriaux, les publicités compatibles et le SEO de repli. `inc/settings.php` contient l’unique schéma et les valeurs par défaut. `inc/admin.php` génère les sept écrans et applique une validation par type. `inc/category-meta.php` gère les données de rubrique via `term_meta`. `inc/consent.php` et `assets/js/consent.js` forment le chargeur de consentement.

Les templates restent classiques et surchargeables dans un thème enfant. Le design repose sur des variables CSS produites depuis les réglages ; les presets n’ajoutent que des variations de composition et d’expression.
