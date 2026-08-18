# Traductions

Orion utilise le domaine `orion26` et charge les catalogues depuis `languages/`.

| Langue | Locale | Fichiers |
|---|---|---|
| Français | `fr_FR` | `fr_FR.po`, `fr_FR.mo` |
| English | `en_US` | `en_US.po`, `en_US.mo` |
| Español | `es_ES` | `es_ES.po`, `es_ES.mo` |
| Italiano | `it_IT` | `it_IT.po`, `it_IT.mo` |
| Português | `pt_PT` | `pt_PT.po`, `pt_PT.mo` |

Après modification de chaînes, régénérez `orion26.pot`, fusionnez chaque PO avec `msgmerge` puis compilez avec `msgfmt`. Les fichiers Gettext sont marqués dans `.gitattributes` afin que GitHub Linguist les identifie correctement.
