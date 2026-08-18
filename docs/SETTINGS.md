# Réglages et stockage

Les réglages sont enregistrés dans l’option non autoloadée `orion26_settings`, structurée par sections : `identity`, `design`, `navigation`, `homepage`, `footer`, `social` et `consent`. Utilisez `orion26_setting('section.key', $fallback)` pour lire une valeur.

La capacité virtuelle `manage_orion26_settings` est accordée aux administrateurs et aux comptes explicitement sélectionnés. Seuls les administrateurs peuvent déléguer cet accès. Les champs de scripts exigent en plus `unfiltered_html`.

Le sélecteur de menus utilise les menus WordPress standards. Les listes de rubriques sont des identifiants de termes et restent donc portables avec les exports WordPress usuels.
