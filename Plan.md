# Plan d'alignement front (builder) ↔ PHP (bundle)

Objectif : que **tout ce que le builder permet de configurer soit géré côté PHP** (formulaire, validation, stockage,
affichage, export), et que **tout ce que le PHP sait faire soit pilotable depuis le builder** quand c'est pertinent.
Pas de tag `v1.0.0` tant que les phases 1 à 6 ne sont pas terminées.

Sources comparées : `assets/builder/components/Blocks/Definition.ts` (+ composants de bloc, store) ↔
`src/Field/*.php`, `src/FormType/*`, `templates/form_theme/shadcn.html.twig`, `templates/answers/shadcn.html.twig`,
et les consommateurs côté OSCAR (`ResourceAnswer.html.twig`, `FormAnswerCsvExportBuilder`, `ShowDoneControlController`).

---

## 0. Constats transverses

| # | Constat | Gravité |
|---|---|---|
| T1 | Le front émet 4 types inconnus du PHP : `TextareaInput`, `AddressInput`, `ChoiceGroup`, `HourMinuteInput`. `FieldFactory` les ignore **silencieusement** → le champ n'apparaît pas dans le formulaire de réponse. | **Bloquant** |
| T2 | Options de `Select` : front `{id, label}`, PHP exige `{label: string, isSelected: bool}` (`FormLayoutBlock::configOptions`). Toute option du builder est rejetée → **liste déroulante vide**. | **Bloquant** |
| T3 | Format des ids : le front génère `"<Type>-<uuid>"` (`generateBlockId`). Le listener `PreRemoveRemoveFormAnswerFile` repère les fichiers par préfixe `file_`, `fieldset_`, `repeatable_` → **les fichiers ne sont plus supprimés** avec la réponse. | Haute |
| T4 | Clé `name` (slug du libellé) émise par tous les blocs : jamais lue par le PHP (la clé de réponse est `id`). | Faible |
| T5 | `conditions` (blocs enfants conditionnés à une option, sur `Select` et `ChoiceGroup`) sont enregistrées dans `config` mais **aucune ligne `FormLayoutField` n'est créée pour les enfants** → leurs réponses ne peuvent être ni stockées ni affichées. | **Bloquant** (D5) |
| T6 | Valeurs par défaut posées à chaque création de réponse : `NumberInput` préremplit `0`, `DateTimeInput` peut préremplir « maintenant ». Comportement PHP-only, non pilotable depuis le front (sauf `hasCurrentDate`, absent du front). | Moyenne |
| T7 | Toute l'UI du builder est en français codé en dur (`Definition.ts`, composants). | Moyenne (i18n) |
| T8 | Les types sont dispatchés par **nom de classe/chaîne** à 5 endroits : `FieldFactory`, `FieldKindResolver`, blocs de `answers/shadcn.html.twig` (`{% block <Type> %}`), `ResourceAnswer.html.twig`, `FormAnswerCsvExportBuilder`. Renommer un type = toucher les 5. | À encadrer par des tests |
| T9 | Aucun test ne détecte la dérive front/PHP (c'est comme ça que T1/T2 sont passés). | Haute |

---

## 1. Décisions à prendre (avec recommandation)

| # | Question | Recommandation |
|---|---|---|
| D1 | Nom canonique pour `TextareaInput`/`TextAreaInput` et `AddressInput`/`Address` | **Noms du front** (`TextareaInput`, `AddressInput`) : cohérents avec `EmailInput`/`UrlInput`/… et avec `TextareaType` de Symfony. Les classes PHP sont renommées ; OSCAR migre ses données avec `migrations.sql` (2 `UPDATE` sur `form_form_layout_field.type`, aucun consommateur OSCAR ne cite ces noms). **Pas d'alias** : le SQL est lancé avec le déploiement. **Décidé.** |
| D2 | `HourMinuteInput` : doublon de `DateTimeInput` `mode: "time"` | **Garder un type distinct `HourMinuteInput`** (classe PHP dédiée, `TimeType`, valeur stockée en chaîne `"HH:MM"`). `DateTimeInput` garde `mode: "time"`. **Décidé.** |
| D3 | `ChoiceGroup` (radio / cases) vs `Select.checkCases` (PHP) | **Classe PHP `ChoiceGroup`** (ChoiceType `expanded`), code partagé avec `Select`. `Select.checkCases` n'est plus proposé par le builder ; sa lecture PHP est supprimée avec le reste du legacy une fois OSCAR migré. **Décidé.** |
| D4 | `defaultValue` et `readOnly` (PHP-only) | **Ajoutés au front** (« Valeur par défaut », « Lecture seule ») sur Texte/Zone de texte/Email/Tél/URL/Nombre (+ `readOnly` sur Heure, Date, Select, ChoiceGroup). **Décidé.** |
| D5 | `conditions` | **Dans `v1.0.0`** : la phase 6 est bloquante pour le tag. **Décidé.** |
| D6 *(retenu)* | `NumberInput` : `allowDecimal` (PHP) vs `min/max/step` (front) | **Garder `min/max/step`**, les implémenter en PHP (attributs HTML + contraintes), dériver entier/décimal de `step` (`step` entier ⇒ `IntegerType`, sinon `NumberType`). `allowDecimal` reste lu en legacy. |
| D7 *(retenu)* | Type inconnu à la génération du formulaire | **Exception en `kernel.debug`, log `warning` + saut en prod**. Fini le silence. |
| D8 *(retenu)* | `Title.heading` (h1…h6) | **Respecter le niveau** côté thème de formulaire et vue des réponses. |
| D9 | Validation `TelInput` : exactement 10 chiffres (contrainte FR en dur) | Hors périmètre : à documenter ; option `pattern` configurable à étudier plus tard. |

---

## 2. Matrice par type (diff détaillé)

Légende : **F→P** = implémenter côté PHP ce que le front émet ; **P→F** = exposer dans le front ce que le PHP sait faire ;
**=** déjà aligné ; **✂** à retirer.

### 2.1 `Title`
| Propriété | Front | PHP | Action |
|---|---|---|---|
| `text` | oui | oui (`validateDefinition` exige `text` non vide) | = |
| `heading` (`h1`…`h6`) | oui (défaut `h1`) | ignoré ; thème en `<h2>` fixe | **F→P** (D8) : passer `heading` à `TitleType`, rendre `<hN>` dans `title_row` et dans `answers/shadcn` |
| `label` | non | non | = |

### 2.2 `Paragraph`
| `text` | oui | oui | = |
|---|---|---|---|
Aucun écart.

### 2.3 `TextInput`
| Propriété | Front | PHP | Action |
|---|---|---|---|
| `label`, `helpText`, `required`, `placeHolder` | oui | oui | = |
| `readOnly` | dans le type TS, **sans UI** | oui (`disabled`) | **P→F** (D4) |
| `defaultValue` | non | oui | **P→F** (D4) |
| `name` | oui | ignoré | ✂ côté front (T4) ou ignorer |

### 2.4 `TextareaInput` (ancien PHP : `TextAreaInput`, D1)
| Propriété | Front | PHP | Action |
|---|---|---|---|
| Nom du type | `TextareaInput` | `TextAreaInput` | **Renommer la classe PHP** en `TextareaInput` (T1) |
| `rows` | oui (défaut 5) | oui (défaut 5) | = |
| `label`, `helpText`, `required`, `placeHolder` | oui | oui | = |
| `readOnly`, `defaultValue` | non | oui | **P→F** (D4) |

### 2.5 `EmailInput`, `TelInput`, `UrlInput`
| Propriété | Front | PHP | Action |
|---|---|---|---|
| `label`, `helpText`, `required`, `placeHolder` | oui | oui | = |
| `readOnly`, `defaultValue` | non | oui | **P→F** (D4) |
| Validation | — | Email ; Tél : `^\+?[0-9]\d*$` + longueur exacte 10 ; URL : protocole `https` par défaut + `Url` | = (D9 pour Tél) |

### 2.6 `NumberInput` (D6)
| Propriété | Front | PHP | Action |
|---|---|---|---|
| `min`, `max`, `step` | oui (chaînes) | **absents** | **F→P** : attributs `min/max/step` + contraintes `GreaterThanOrEqual`/`LessThanOrEqual` |
| `allowDecimal` | non | oui (bascule `IntegerType`/`NumberType`) | Dériver de `step` ; garder en legacy |
| `placeHolder` | oui | **ignoré** | **F→P** : `attr.placeholder` |
| `defaultValue` | non | oui, **préremplit `0` par défaut** (T6) | **P→F** (D4) ; **PHP : défaut `null`**, pas `0` |
| `readOnly` | non | oui | **P→F** (D4) |
| `label`, `helpText`, `required` | oui | oui | = |

### 2.7 `DateTimeInput` (D2)
| Propriété | Front | PHP | Action |
|---|---|---|---|
| Mode | `mode` ∈ `date`, `datetime-local`, `time` | `showDate`, `showHour` (booléens) | **F→P** : lire `mode` ; garder `showDate/showHour` en legacy (priorité à `mode` si présent) |
| `hasCurrentDate` | non | oui | **P→F** : case « Préremplir avec la date/heure courante » |
| `placeHolder` | oui (inutile sur input natif) | ignoré | ✂ front (retirer du schéma d'édition de ce bloc) |
| `readOnly` | non | oui | **P→F** (D4) |
| `label`, `helpText`, `required` | oui | oui | = |
| Affichage réponse | — | `answers/shadcn` lit `block.showDate/showHour` pour le format | Lire `mode` aussi (format `d/m/Y`, `d/m/Y H:i`, `H:i`) ; idem `ResourceAnswer.html.twig` |

### 2.8 `HourMinuteInput` (D2)
| Propriété | Front | PHP | Action |
|---|---|---|---|
| Type | `HourMinuteInput` | **absent** (T1) | **F→P** : créer `Field\HourMinuteInput` (`TimeType`, `input: string`, `widget: single_text`) ; valeur stockée `"HH:MM"` |
| `label`, `helpText`, `required` | oui | via la classe | = |
| `placeHolder` | oui (sans effet sur un input `time` natif) | — | ✂ front : retirer du schéma d'édition |
| `readOnly`, `defaultValue` (`"HH:MM"`) | non | à prévoir | **P→F** (D4) |
| Affichage réponse | — | pas de bloc | Ajouter `{% block HourMinuteInput %}` (chaîne brute) ; `ResourceAnswer.html.twig` et export CSV : valeur telle quelle |

Doublon assumé avec `DateTimeInput` `mode: "time"` (valeur `DateTime` JSON) : les deux restent valides.

### 2.9 `AddressInput` (ancien PHP : `Address`, D1)
| Propriété | Front | PHP | Action |
|---|---|---|---|
| Nom du type | `AddressInput` | `Address` | **Renommer la classe PHP** en `AddressInput` |
| `placeHolder` (défaut « Indiquez un lieu… ») | oui | **ignoré** | **F→P** : `AddressType` accepte `attr.placeholder` |
| `label`, `helpText`, `required` | oui | oui | = |

### 2.10 `FileInput`
| Propriété | Front | PHP | Action |
|---|---|---|---|
| `acceptedFile` (`image`/`file`/`both`) | oui | oui (mêmes valeurs) | = |
| Multiplicité | `allowMultiple` (bool) | `maxItems` (int, défaut 5 ; `0` ⇒ 5) | **F→P** : `allowMultiple=false` ⇒ `maxItems=1` ; `true` ⇒ `maxItems` ou 5 ; garder `maxItems` en legacy |
| `label`, `helpText`, `required` | oui | oui | = |
| Id | `FileInput-<uuid>` | listener `PreRemove…` attend `file_` | **Corriger le listener** (T3) : parcourir la structure du layout et supprimer les fichiers des blocs `FileInput`, y compris sous `FieldSet`/`Repeatable`, sans dépendre du format d'id |

### 2.11 `Select` (T2)
| Propriété | Front | PHP | Action |
|---|---|---|---|
| `options` | `[{id, label}]` | `[{label, isSelected}]` obligatoire | **F→P** : accepter `{id?, label, isSelected?}` (`isSelected` défaut `false`) |
| `multiple` | oui | oui | = |
| `label`, `helpText`, `required` | oui | oui | = |
| `isSelected` (option présélectionnée) | non | oui | **P→F** : case par option (ou radio « par défaut ») |
| `customOption` (créer une option à la volée) | non | oui (tom-select) | **P→F** : case « Autoriser une saisie libre » |
| `readOnly` | non | oui | **P→F** (D4) |
| `checkCases` | non (remplacé par `ChoiceGroup`) | oui | Legacy, voir D3 |
| `conditions` | oui | **ignorées** (T5) | Phase 6 |

### 2.12 `ChoiceGroup` (D3, T1)
| Propriété | Front | PHP | Action |
|---|---|---|---|
| Type | `ChoiceGroup` | **absent** | **F→P** : créer `Field\ChoiceGroup` (ChoiceType `expanded=true`, `multiple` ⇒ cases, sinon radios), partageant le code de `Select` |
| `options` | `[{id, label, value}]` (`value` = `label`) | — | Stocker **le libellé** comme valeur (cohérent avec `Select`) ; ignorer `value` |
| `multiple`, `required`, `helpText`, `label` | oui | via `Select` | = |
| `conditions` | oui | — | Phase 6 |
| Affichage réponse | — | pas de bloc `ChoiceGroup` dans `answers/shadcn` | Ajouter `{% block ChoiceGroup %}` (liste comme `Select`) ; idem `ResourceAnswer.html.twig` et CSV |

### 2.13 `Signature`
| `label`, `helpText`, `required` | oui | oui | = |
|---|---|---|---|
Aucun écart (valeur : data URI SVG).

### 2.14 `FieldSet`
| `children` | oui | oui | = |
|---|---|---|---|
Aucun écart (pas de libellé ni de légende des deux côtés). Réponses stockées imbriquées sous l'id du fieldset.

### 2.15 `Repeatable`
| Propriété | Front | PHP | Action |
|---|---|---|---|
| `children` | oui | oui | = |
| `maxItems` | oui, défaut **1**, `0` = illimité | défaut **5**, `0` géré par le thème comme illimité | = sur `0` ; **aligner le défaut** (PHP : 1 si absent, ou documenter) |

### 2.16 Clés front sans équivalent PHP à ce jour
`name` (tous blocs), `heading` (Title, voir 2.1), `conditions` (T5), `placeHolder` sur `DateTimeInput`.

### 2.17 Clés PHP sans pilotage front
`defaultValue`, `readOnly` (tous champs de saisie), `hasCurrentDate`, `customOption`, `isSelected` (options), `checkCases`, `allowDecimal`, `showDate/showHour`.

---

## 3. Phases d'exécution

### Phase 1 — Débloquer (T1, T2, T3) · petit
1. PHP : renommer `Field\TextAreaInput` → `TextareaInput` et `Field\Address` → `AddressInput` (classes, tests, blocs de `answers/shadcn.html.twig`, docs) ; type inconnu ⇒ comportement D7. OSCAR : exécuter `migrations.sql` (racine du dépôt OSCAR) avec le déploiement.
2. PHP `FormLayoutBlock::configOptions` : accepter `{id?, label, isSelected?}`.
3. Front : rien à renommer (noms de référence).
4. PHP `PreRemoveRemoveFormAnswerFile` : parcours par structure (T3), couvert par test avec ids `FileInput-<uuid>`, fichier dans `FieldSet` et dans `Repeatable`.
5. Tests : un cas par type avec les blocs **tels qu'émis par le front** (voir phase 5).

### Phase 2 — Options manquantes côté PHP (F→P)
`Title.heading` ; `NumberInput` min/max/step/placeholder + défaut `null` ; `DateTimeInput.mode` (+ legacy) ;
`FileInput.allowMultiple` ; `Address.placeHolder` ; `Repeatable` défaut ; formats de date dans `answers/shadcn`,
`ResourceAnswer.html.twig`.

### Phase 3 — Nouveaux types et retraits
`Field\ChoiceGroup` (D3) et `Field\HourMinuteInput` (D2) ; blocs `ChoiceGroup` et `HourMinuteInput` dans `answers/shadcn`,
`ResourceAnswer.html.twig`, `FormAnswerCsvExportBuilder` (formatage de valeur).

### Phase 4 — Exposer le PHP dans le front (P→F)
`readOnly` + `defaultValue` (D4) ; `hasCurrentDate` ; `isSelected` et `customOption` pour `Select` ; retirer `name` (T4)
et `placeHolder` des blocs date et heure.

### Phase 5 — Garde-fous anti-dérive (T9) · **condition du tag `v1.0.0`**
1. Script `pnpm run export-contract` : dumpe `blockDefinitions` en `assets/builder/contract/blocks.json`
   (type, clés de `defaultProps`, clés de `editionSchema`).
2. Test PHPUnit qui, pour **chaque type du contrat** : (a) `FieldFactory` le résout, (b) un bloc généré à partir de
   `defaultProps` passe `validateDefinition()` et se construit en formulaire Symfony, (c) chaque clé du contrat est
   lue par la classe PHP (liste explicite des clés ignorées volontairement). Il échoue si le front ajoute un type ou une clé.
3. Test inverse : chaque clé PHP lue figure au contrat ou est listée comme « legacy ».
4. CI : générer le contrat avant PHPUnit ; échouer si `blocks.json` n'est pas à jour.
5. Côté OSCAR : ajuster `ResourceAnswer.html.twig`, `FormAnswerCsvExportBuilder`, `ShowDoneControlController` si un nom de type change (aucun avec D1 : ces consommateurs ne citent pas les deux types renommés).

### Phase 6 — Conditions (bloquante pour `v1.0.0`, D5)
Conception à valider avant code :
- Stockage : un enfant conditionnel = `FormLayoutField` avec `parent` = le Select/ChoiceGroup et une marque `conditionId` (+ `operator`, `optionLabel`) dans `config` ; `FormLayoutFieldHydrator::buildContent()` reconstitue `conditions[].children`; `SyncFormLayoutStructureUseCase` les synchronise.
- Formulaire : `Select`/`ChoiceGroup` ajoutent les enfants conditionnels ; contrôleur Stimulus `form-builder-conditions` (affiche/masque selon l'option choisie, `is` / `is_not`).
- Validation : un enfant masqué n'est ni obligatoire ni enregistré ; `setAnswerData` ignore les valeurs d'enfants inactifs.
- Réponses : `AnswerGenerator` et `answers/shadcn` n'affichent que les enfants actifs.
- Tests : structure ↔ lignes (aller-retour), sélection/désélection, requis masqué, affichage.

---

## 4. Définition de « prêt pour v1.0.0 »
- [ ] Phases 1 à 6 terminées (D1…D8 tranchées).
- [ ] `blocks.json` généré en CI, tests de contrat verts, `composer qa` vert.
- [ ] Un formulaire de **chaque type** créé dans le builder, répondu, ré-édité, affiché (web + PDF) et exporté sans perte dans OSCAR.
- [ ] Section « Known limitations » de `docs/reference.md` et du skill mise à jour (i18n du builder) ; types et options documentés (`ChoiceGroup`, `HourMinuteInput`, `conditions`, `readOnly`, `defaultValue`).
- [ ] Tag `v1.0.0` créé sur le dépôt du bundle.
