# Rendering contract

The bundle only themes **its own** form types (fieldset, title, paragraph, repeatable, address, signature, file
list, conditional wrapper — see [reference.md](reference.md#overriding-rendering)). Every other block is generated
as a standard Symfony form type and rendered by the form theme of your application. This page states what that
theme must be able to render.

The default theme is checked against Symfony's `form_div_layout.html.twig`, i.e. what an application with no global
form theme gets. `tests/Unit/Rendering/FormRenderingTest.php` renders one block of every builder type with all its
options; `tests/Support/FormRenderingHarness.php` lets an application run the same test against its own theme (pass
the theme list to the constructor).

## Conformance table

| Builder type | Symfony type and options generated | The theme must render |
|---|---|---|
| `TextInput` | `TextType`; `attr.placeholder`, `required`, `disabled` (`readOnly`), `help`, `data` (`defaultValue`) | `text_widget` with `placeholder`, `required`, `disabled`, `value`; label; help; errors |
| `EmailInput` | `EmailType`, same options | `email_widget` (`type="email"`) |
| `TelInput` | `TelType`, same options + pattern/length constraints | `tel_widget` |
| `UrlInput` | `UrlType`, same options | `url_widget` (`inputmode="url"`) |
| `TextareaInput` | `TextareaType`; `attr.rows` | `textarea_widget` with `rows` |
| `NumberInput` | `IntegerType` (integer step) or `NumberType` (decimal step); `attr.min` / `max` / `step` / `placeholder` | `integer_widget` (`type="number"`) honouring `min`, `max`, `step` |
| `DateTimeInput` `mode: date` | `DatePickerType` → `DateType`, `widget=single_text`, `html5=true` | `date_widget` single text → `<input type="date">`, never three `<select>` |
| `DateTimeInput` `mode: time` | `TimeType`, `widget=single_text`, `with_seconds=false` | `time_widget` single text → `<input type="time">` |
| `DateTimeInput` `mode: datetime-local` | `DateTimePickerType` (`widget=text`, `date_widget` and `time_widget` = `single_text`) | `datetime_widget` → one date input + one time input |
| `HourMinuteInput` | `TimeType`, `widget=single_text`, `with_seconds=false`, `input=string` | `time_widget` single text; stored as `HH:MM:SS` |
| `Select` | `ChoiceType`, not expanded; `multiple`; empty option when optional and single (label `choice.empty`, domain `form_builder_bundle`); with `symfony/ux-autocomplete`: `autocomplete`, `allow_options_create` (`customOption`) | `choice_widget_collapsed` with its options and the empty option |
| `ChoiceGroup` (single) | `ChoiceType`, `expanded=true`, `multiple=false`; an extra empty radio when optional | `choice_widget_expanded` → `radio_widget` **with the option label** |
| `ChoiceGroup` (multiple) | `ChoiceType`, `expanded=true`, `multiple=true` | `choice_widget_expanded` → `checkbox_widget` with the option label |
| `FileInput` | `FileRepeatableType` (collection of `file` + hidden `delete` checkbox); `label`, `help`, `required`, `attr.maxItems` and `Count` constraints on the **collection** | `file_repeatable_row` (override): the field label (with the required mark), help and collection errors, shown even before any file is added; the « Attached files » title only when there is no label |
| `AddressInput` | `AddressType` | `address_row` (override) |
| `Signature` | `SignatureType` | `signature_row` (override) |
| `FieldSet`, `Repeatable`, `Title`, `Paragraph` | bundle types | the bundle blocks (override) |

Common to every value field: label (`label`), help text (`help`), `required` mark, `disabled`, and the errors.
`formBuilder="formBuilder"` is an `attr` marker added to every generated field (an application theme may ignore it).

## Titles and paragraphs

The default theme is neutral (`fb-title`, `fb-paragraph`). The builder shows these blocks with the classes below
(`assets/builder/utilities/typography.tsx`), so an application theme should use the same ones to keep the builder
WYSIWYG. `title_row` must output the requested `h1`…`h6` tag, and `paragraph_row` keeps line breaks (`nl2br`).

| Block | Classes |
|---|---|
| `Title` `h1` | `text-3xl font-bold text-foreground` |
| `Title` `h2` | `text-2xl font-bold text-foreground` |
| `Title` `h3` | `text-xl font-bold text-foreground` |
| `Title` `h4` | `text-lg font-bold text-foreground` |
| `Title` `h5` | `text-base font-bold text-foreground` |
| `Title` `h6` | `text-sm font-bold text-foreground` |
| `Paragraph` | `text-base text-muted-foreground` |

## Behaviour decisions

- **`readOnly` → `disabled`.** A disabled field is not posted by the browser, but Symfony keeps its initial data:
  the default value on creation, the stored answer in edition (`ReadOnlyFieldsTest`). A posted value is ignored.
  A field added to the structure *after* an answer was saved has no stored value and stays empty in edition.
- **Optional single choice.** An optional `Select` or `ChoiceGroup` (single) has an empty value, so the browser does
  not pre-select and submit the first option. Required and multiple choices have none.
- **Empty choice label.** It is translated by the `Field` (domain `form_builder_bundle`), so lists and radios both show « Aucun » / « None » whatever the form's `translation_domain`.
- **Server-side limits.** `FileInput` `required` and `maxItems` / `allowMultiple`, and `Repeatable` `maxItems`
  (`0` = unlimited) are enforced by `Count` constraints on the collection, not only by the JavaScript.
- **`symfony/ux-autocomplete` is optional.** Without it `Select` is a native `ChoiceType` and `customOption` is ignored.
- **Hidden delete checkbox.** The file `delete` checkbox row uses the HTML `hidden` attribute, not a framework class.
  The classes read by the Stimulus controllers (`repeatable`, `repeatable--item`, `collec_child`, `repeatable_child`,
  `file_child`, `file-name`, `remove-container`, `input-delete`) are unchanged.
