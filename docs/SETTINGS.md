# Réglages et stockage

Les réglages sont enregistrés dans l’option non autoloadée `orion26_settings`, structurée par sections : `identity`, `design`, `navigation`, `homepage`, `social`, `consent`, `access`, `header` et `footer`. Utilisez `orion26_setting('section.key', $fallback)` pour lire une valeur.

La capacité virtuelle `manage_orion26_settings` est accordée aux administrateurs et aux comptes explicitement sélectionnés. Seuls les administrateurs peuvent déléguer cet accès. Les champs de scripts exigent en plus `unfiltered_html`.

Le sélecteur de menus utilise les menus WordPress standards. Les listes à cocher de rubriques stockent uniquement des identifiants de termes et restent donc portables avec les exports WordPress usuels. Les styles de titre sont stockés sous `design.headings.h1` à `design.headings.h6`. Les titres éditoriaux des cartes et rubriques utilisent `design.title_styles`, et le zoom des miniatures `design.thumbnail_zoom`.

L’icône du site est enregistrée sous `identity.site_icon_id`. Lors de la mise à niveau, Orion reprend prioritairement l’icône WordPress puis la favicon AH19 de plus grande définition.

L’option `navigation.external_new_tab` centralise le comportement de tous les liens externes. Les réseaux sociaux ne possèdent plus de réglage concurrent.
