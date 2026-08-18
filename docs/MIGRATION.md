# Migration

À la première ouverture de l’administration, Orion recherche ses anciennes options ACF/AH19 et copie les valeurs reconnues vers `orion26_settings`. La migration conserve les options sources et inscrit un horodatage `orion26_settings_migrated_at`.

Orion 26+ correspond désormais au preset `expressive`. Après vérification, un site peut sélectionner Orion 26 et conserver ce preset. Les logos, couleurs, listes de rubriques, réseaux, vérifications, Analytics, AdSense et Matomo sont repris lorsqu’ils existent.

Avant toute migration en production, sauvegardez la base de données et le dossier `wp-content`.
