# Migration

À la première ouverture de l’administration, Orion recherche ses anciennes options ACF/AH19 et copie les valeurs reconnues vers `orion26_settings`. La migration conserve les options sources et inscrit un horodatage `orion26_settings_migrated_at`.

Orion 26+ correspond désormais au preset `expressive`. Après vérification, un site peut sélectionner Orion 26 et conserver ce preset. Les logos, couleurs, listes de rubriques, réseaux, vérifications, Analytics, AdSense et Matomo sont repris lorsqu’ils existent. La migration 3.5 déplace automatiquement les anciens réglages de logo/menu vers `header`, le logo de pied de page vers `footer`, les comptes délégués vers `access` et le comportement des réseaux vers la politique globale de liens externes.

Avant toute migration en production, sauvegardez la base de données et le dossier `wp-content`.
