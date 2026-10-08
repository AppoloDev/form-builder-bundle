# Reference

## Configuration

| Option | Default | Description |
|---|---|---|
| `classes.form_layout` | **required** | Concrete layout entity |
| `classes.form_layout_field` | **required** | Concrete field entity |
| `classes.form_answer` | **required** | Concrete answer entity |
| `classes.form_answer_field_value` | **required** | Concrete value entity |
| `file_url_resolver` | **required** | Service id implementing `FormAnswerFileUrlResolverInterface` |
| `upload_path` | `%kernel.project_dir%/uploads/form-files/` | Where uploaded answer files are stored |
| `form_theme` | `@FormBuilder/form_theme/shadcn.html.twig` | Twig theme for the bundle's own form types |
| `answers_template` | `@FormBuilder/answers/shadcn.html.twig` | Template whose `answers_view` block renders answers |
| `answers_pdf_template` | same as `answers_template` | Same, when `form_builder_answers(…, true)` |

The bundle also registers `@FormBuilder/form_theme/structure.html.twig` (the builder widget) in
`twig.form_themes`, and Doctrine `resolve_target_entities` for the four contracts.

## Autowirable services

| Service | Purpose |
|---|---|
| `Form\FormTypeGenerator` | `buildForm(array $structure, array $data = [], array $options = [])`, `buildNamedForm($name, …)` |
| `Answer\AnswerGenerator` | `generate($structure, $answerData)` → structure enriched with `value`s; `flattenToFields()` |
| `File\FormFileUploader` | `upload()`, `getFile($filename)`, `removeFile(?array)`, `hydrate()` |
| `UseCase\SyncFormLayoutStructureUseCase` | Merge a structure into a layout that may already have answers |
| `UseCase\DeleteRemovedFormLayoutFieldValuesUseCase` | Delete values of removed fields, returns the count |

## Form types

| Type | Options |
|---|---|
| `FormType\FormStructureType` | `answers_count` (int, default 0). Data: structure array ⇄ JSON in a hidden input |
| `FormType\FormBuilderType` | `edit` (bool). Root type used by `FormTypeGenerator` |
| `FormType\DatePickerType`, `DateTimePickerType` | Wrappers over `DateType` / `DateTimeType` |

## Twig

| Name | Signature |
|---|---|
| `form_builder_answers` | `(array $answers, FormAnswerInterface $formAnswer, bool $pdf = false)` → HTML |
| `form_builder_file_url` | `(FormAnswerInterface $formAnswer, string $filename)` → URL from your resolver |
| `formImageFileUri` | `(string $filename)` → `file://…` path or `null` |
| `convertToBase64` | `(string $filename)` → data URI or `null` |
| `form_builder_date_format` | `(array $block)` → PHP `date` format for a `DateTimeInput` answer (`d/m/Y`, `H:i`, `d/m/Y H:i`) |

## Structure format

A structure is a list of blocks:

```json
[
  {"id": "name", "type": "TextInput", "label": "Name", "required": true},
  {"id": "grp", "type": "FieldSet", "children": [{"id": "city", "type": "TextInput", "label": "City"}]},
  {"id": "intro", "type": "Title", "text": "Section"}
]
```

`id` and `type` are mandatory (blocks without them are skipped). `label` and `text` are stored in their own
columns; every other key goes to the field's `config` JSON column. `children` nests blocks. `id` is the answer
key and must be unique in the layout.

### Field kinds

| Kind | Types | Has a value |
|---|---|---|
| DisplayOnly | `Title`, `Paragraph` (use `text`) | no |
| Container | `FieldSet` | no, groups `children` |
| Repeatable | `Repeatable` | rows of its `children` |
| Value | everything else | yes |

### Supported field types and config keys

| Type | Config keys (besides `id`, `label`) |
|---|---|
| `TextInput`, `EmailInput`, `TelInput`, `UrlInput` | `required`, `readOnly`, `placeHolder`, `helpText`, `defaultValue` |
| `TextareaInput` | same + `rows` |
| `NumberInput` | `required`, `readOnly`, `helpText`, `placeHolder`, `defaultValue`, `min`, `max`, `step` (a non-integer `step` selects the decimal type); legacy `allowDecimal` |
| `DateTimeInput` | `required`, `readOnly`, `helpText`, `mode` (`date`, `time`, `datetime-local`), `hasCurrentDate`; legacy `showDate` / `showHour` when `mode` is absent |
| `Select` | `options` (`[{label, isSelected?}]`, extra keys such as `id` ignored), `multiple`, `checkCases` (radio/checkbox), `customOption`, `required`, `readOnly`, `helpText` |
| `FileInput` | `acceptedFile`, `allowMultiple` (`false` ⇒ one file; otherwise up to `maxItems`, default 5), `required`, `helpText` |
| `AddressInput` | `required`, `helpText`, `placeHolder` |
| `Signature` | `required`, `helpText` |
| `FieldSet` | `children` |
| `Repeatable` | `children`, `maxItems` (default 5; `0` = unlimited) |
| `Title` | `text`, `heading` (`h1`…`h6`, default `h3`) |
| `Paragraph` | `text` |

## Answer data

`getAnswerData()` / `setAnswerData()` use a map `fieldId → value`:

| Field | Value |
|---|---|
| Simple fields | scalar, or list for multiple selects |
| `DateTimeInput` | JSON form of a `DateTime` (`date`, `timezone_type`, `timezone`) |
| `Repeatable` | list of rows: `[{childId: value}, …]` (stored per row index) |
| `FieldSet` | nested map under the fieldset id |
| `FileInput` | list of `{file: {filename, originalFilename, extension}}` |
| `Signature` | data URI |

## Overriding rendering

**Form theme** (`form_theme`) must define: `fieldset_row`, `title_row`, `paragraph_row`, `repeatable_row`,
`repeatable_children_prototype`, `repeatable_child_row`, `add_btn`, `remove_btn`, `address_row`,
`signature_row`, `file_repeatable_row`, `file_repeatable_children_prototype`, `file_repeatable_child_row`.
Collection behaviour comes from the `form-builder-collection` Stimulus controller (`data-prototype`,
`data-collection-child-selector`, `data-remove-btn-target`, `data-add-auto`, `data-max-items`,
`data-delete-selector`), the add/remove buttons from `<template id="add-btn">` / `<template id="remove-btn">`.

**Answers template** must define block `answers_view` (variables `answers`, `formAnswer`) and one block per
field type used (`FieldSet`, `Repeatable`, `Title`, `Paragraph`, `TextInput`, …, `FileInput`, `Select`,
`Signature`). Extend the default one with `{% extends '@FormBuilder/answers/shadcn.html.twig' %}` and override
only what you need (e.g. `FileInput`, `fileList` for PDF).

## Translations

Domain `form_builder_bundle` (French only for now): builder-widget confirmation, theme buttons, signature
labels, answers view. Override by providing the same keys in your own `translations/` directory.

## Known limitations

- The bundled builder emits block types the PHP side does not handle yet: `ChoiceGroup` and `HourMinuteInput`
  (planned, see `Plan.md`). A block with an unknown type throws `UnknownFieldTypeException` when
  `kernel.debug` is on, and is skipped with a `warning` log otherwise.
- Select `conditions` (conditional blocks) are stored in `config` but not evaluated server-side (planned).
- The builder UI text is French only.
- Custom PHP field types cannot be registered yet (`FieldFactory` resolves types in the bundle namespace).
