# Orion 26

Orion 26 est un thème WordPress éditorial moderne, rapide et responsive. Il réunit une mise en page d’actualité, quatre directions visuelles, un panneau de réglages natif indépendant d’ACF, un mode clair/sombre et un gestionnaire de consentement opt-in.

## Points forts

- Homepage magazine : Une, fil récent, rubriques prioritaires, hub de rubriques et sélections éditoriales.
- Archives catégorie, tag, auteur, recherche, page d’articles et page « autres rubriques ».
- Quatre presets : Minimal, Expressif, Éditorial et Contraste.
- Réglages natifs sous le menu **Orion**, avec sept écrans et prévisualisation directe du design.
- Six familles de polices locales, sans appel à un CDN.
- Logos distincts clair, sombre et footer ; dimensions réglables.
- Menu WordPress configurable, recherche animée, header fixe et lien final optionnel.
- Métadonnées de rubrique natives : présentation longue et liens officiel/Facebook/Instagram/YouTube.
- Consentement par catégories, prise en compte de GPC et chargement différé des scripts.
- SEO de repli : données structurées `NewsArticle`, `ProfilePage` et `BreadcrumbList`, métadescription, robots et images optimisées. Le thème laisse la priorité à SEOPress lorsqu’il est présent.
- Compatibilité optionnelle avec un post type publicitaire `ad` et les métadonnées éditoriales `sponso`, `cp`, `histo`.

## Prérequis

- WordPress 6.5 ou supérieur (testé sur 7.0.4)
- PHP 8.1 ou supérieur

## Installation

1. Téléchargez l’archive de la dernière release.
2. Dans WordPress, ouvrez **Apparence → Thèmes → Ajouter un thème → Téléverser**.
3. Activez Orion 26.
4. Ouvrez **Orion → Identité et apparence**, choisissez un preset et vos logos.
5. Affectez vos menus dans **Orion → Navigation** et **Orion → Footer**.

Les données d’un site ne sont jamais incluses dans le dépôt. Lorsqu’Orion détecte les anciennes options France Racing/AH19 lors de sa première initialisation, il les migre dans l’option unique `orion26_settings` sans modifier les valeurs sources.

## Réglages

| Écran | Contenu |
|---|---|
| Identité et apparence | Preset, logos, clair/sombre, largeur et délégation d’accès |
| Couleurs / typographies | Palette complète, polices, tailles, citations, code, aperçu direct |
| Navigation | Menu principal, recherche, header fixe, lien final |
| Homepage et rubriques | Tag de Une, groupes de rubriques, volumes, sélections |
| Footer | Description, rubriques, colonnes, menu, copyright dynamique |
| Réseaux sociaux | Facebook, Instagram, X, YouTube, LinkedIn, TikTok, Twitch, RSS |
| Consentement | Bandeau, catégories, GPC, Analytics, AdSense et scripts conditionnels |

Consultez [la documentation des réglages](docs/SETTINGS.md), [l’architecture](docs/ARCHITECTURE.md), [le consentement](docs/CONSENT.md) et [la migration](docs/MIGRATION.md).

## Traductions

Le domaine est `orion26`. Le catalogue POT et les fichiers PO/MO sont fournis pour le français, l’anglais, l’espagnol, l’italien et le portugais. Les contributions linguistiques sont détaillées dans [TRANSLATIONS.md](TRANSLATIONS.md).

## Développement

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
./bin/build-release.sh
```

Le ZIP produit dans `dist/` contient uniquement le thème installable. Les contributions suivent [CONTRIBUTING.md](CONTRIBUTING.md).

## Confidentialité et droit

Le gestionnaire fourni est un outil technique et ne constitue pas un avis juridique ni une garantie de conformité. Il ne faut pas activer deux gestionnaires de consentement simultanément. Orion 26 est distribué sous licence GPL-2.0-or-later.
