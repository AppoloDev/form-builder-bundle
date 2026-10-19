# TODO — champs à envisager (inspirés de Tally.so)

Comparaison avec la liste de blocs de Tally. Les catégories du bundle sont `text`, `choice`, `number`, `datetime`,
`file`, `special`, `layout`, `group` (voir `docs/reference.md`). Les « kind » sont ceux de `Enum\FieldKind`.

## Déjà couvert

| Tally | Bundle | Catégorie |
|---|---|---|
| Short answer | `TextInput` | text |
| Long answer | `TextareaInput` | text |
| Email / Phone number / Link | `EmailInput` / `TelInput` / `UrlInput` | text |
| Multiple choice / Checkboxes | `ChoiceGroup` (`multiple`) | choice |
| Dropdown / Multi-select | `Select` (`multiple`) | choice |
| Number | `NumberInput` | number |
| Date / Time | `DateTimeInput` (`mode`) / `HourMinuteInput` | datetime |
| File upload | `FileInput` | file |
| Signature | `Signature` | special |
| Text / Heading 1-3 / Title | `Paragraph` / `Title` (`h1`…`h6`) | layout |
| Conditional logic | `conditions` sur `Select` et `ChoiceGroup` | — |
| *(pas chez Tally)* | `AddressInput`, `FieldSet`, `Repeatable` | special / group |

## À faire plus tard, par ordre d'intérêt

Effort : S = quelques heures, M = une journée, L = plusieurs jours (nouveau type de valeur, de rendu et d'export).

| # | Tally | Nom proposé | Catégorie | Kind | Travail | Effort |
|---|---|---|---|---|---|---|
| 1 | Divider | `Divider` | layout | DisplayOnly | Affichage seul : composant builder, bloc `<hr>` dans le thème et les réponses. Aucune valeur. | S |
| 2 | Hidden fields | `HiddenInput` | special | Value | Valeur fixée par le formulaire (paramètre d'URL, utilisateur courant…) : `HiddenType`, clé `source`. À cadrer : d'où vient la valeur ? | M |
| 3 | Rating | `RatingInput` | number | Value | Note 1..N (étoiles) : `ChoiceType` ou `IntegerType` borné, clé `max`, rendu en étoiles dans le thème + réponses. | M |
| 4 | Linear scale | `ScaleInput` | number | Value | Échelle min..max avec libellés d'extrémités ; peut partager le code de `RatingInput` (même valeur entière). | M |
| 5 | Page break / Thank-you page | `PageBreak` | layout | DisplayOnly | Formulaire en plusieurs étapes : demande un découpage du formulaire côté rendu (étapes, navigation, validation par étape). Pas un simple bloc. | L |
| 6 | Image / Video / Audio / Embed | `MediaBlock` | layout *(ou nouvelle catégorie `embed`)* | DisplayOnly | Affichage d'un média par URL ou upload côté éditeur ; attention au stockage des fichiers du *layout* (≠ fichiers de réponse) et à l'échappement de l'embed. | M–L |
| 7 | Label | `Label` | layout | DisplayOnly | Petit texte d'étiquette : probablement un `Paragraph` avec une variante (`variant`). | S |
| 8 | Matrix | `MatrixInput` | choice | Value | Grille lignes × colonnes (une réponse par ligne) : nouveau type de valeur (map `ligne → colonne`), éditeur de lignes/colonnes, rendu, export CSV. | L |
| 9 | Ranking | `RankingInput` | choice | Value | Classement d'options par glisser-déposer : valeur = liste ordonnée, contrôleur Stimulus, accessibilité clavier. | L |
| 10 | Calculated fields | `CalculatedField` | advanced *(à créer)* | Value | Valeur calculée à partir d'autres champs : moteur d'expressions, dépendances, boucles. À éviter tant qu'aucun besoin précis. | L |
| 11 | Respondent's country | `CountryInput` | special | Value | Liste de pays (`CountryType`) ; la détection par IP du répondant relève plutôt de l'application. | S |
| 12 | reCAPTCHA | — | — | — | Hors périmètre du bundle : à poser sur le formulaire de l'application, pas sur la structure. | — |
| 13 | Payment | — | — | — | Hors périmètre : dépend d'un prestataire de paiement et de l'application. | — |

## Extensions de l'existant à noter

- **Conditions** : aujourd'hui uniquement sur `Select` / `ChoiceGroup`. Étendre aux nombres (`>`, `<`), aux cases
  cochées et aux dates si le besoin apparaît.
- **Catégorie `advanced`** : à recréer (sous ce nom) le jour où des champs de logique arrivent (`HiddenInput`,
  `CalculatedField`) ; elle a été volontairement remplacée par `special` pour `AddressInput` et `Signature`, qui sont
  des champs de saisie chez Tally.
- **Catégorie `embed`** : à créer avec `MediaBlock` s'il y a plus d'un type média.

## Pour ajouter un type (rappel)

1. Builder : `Definition.ts` (props, `category`, `editionSchema`), composant dans `Blocks/`, `BlockRegistry.tsx`,
   clés i18n fr/en, `pnpm run export-contract`.
2. PHP : classe `Field\…`, `Enum\FieldKind` via `ValueFieldKind`, bloc dans `answers/shadcn.html.twig`, export CSV,
   `Enum\FieldCategory::types()`.
3. Tests : contrat (`FrontContractTest`, `BuilderKeysEffectTest`), cas dans `FormRenderingTest::cases()`.
