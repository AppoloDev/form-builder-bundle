---
name: form-builder-integration
description: Integrate, configure, debug or extend appolodev/form-builder-bundle (custom form builder, rendering, answers) in a Symfony project. Use when a task mentions form-builder, FormLayout/FormAnswer entities, FormStructureType, form_builder_answers, the React builder, or the bundle's Stimulus controllers.
---

# form-builder-bundle integration

Read `docs/` of the bundle for details. This file is the mental model and the checklist.

## Mental model

**Backend**
1. A *layout* = title + a **structure**: tree of blocks `{id, type, label?, text?, children?, ...config}`.
   Persisted as `FormLayoutField` rows (`fieldKey`=id, `type`, `label`, `text`, `config` JSON, `position`, `parent`).
2. `FormTypeGenerator::buildForm($structure, $data, $options)` turns a structure into a Symfony form
   (type → class `AppoloDev\FormBuilderBundle\Field\<Type>` → form type).
3. An *answer* = `FormAnswer` + `FormAnswerFieldValue` rows (one per field, one per row index for repeatables).
   `setAnswerData(array)` / `getAnswerData()` convert between the form's array and those rows.
4. `AnswerGenerator::generate($structure, $answerData)` merges values into the structure;
   `form_builder_answers()` (Twig) renders it through the configured template.
5. Editing a layout with answers: `SyncFormLayoutStructureUseCase` (merge in place) — never `setStructure()`.

**Frontend**
`FormStructureType` → hidden input + `<form-builder>` (React) inside `<form-builder-manager>`; the builder
emits `change` with the structure, the manager writes JSON into the input and confirms on submit if removed
fields hold answers. Row add/remove, address autocomplete and signature are Stimulus controllers prefixed
`form-builder-`.

## Bundle vs host

| Bundle | Host app |
|---|---|
| Base entities/traits/contracts, field types, form types, use cases, theme, answers template, JS | Concrete entities + extra columns, migrations, controllers/routes, voters, file download route, URL resolver, PDF, Vite/Tailwind wiring |

Never reference host entities from bundle code; use the `Contract\*` interfaces.

## Integration checklist

1. `composer require appolodev/form-builder-bundle`; ensure it is in `config/bundles.php`.
2. Create 4 entities extending `AbstractFormLayout`, `AbstractFormLayoutField`, `AbstractFormAnswer`,
   `AbstractFormAnswerFieldValue`. Each: `Uuid` id + `getId()`; `use` the matching `Has*` trait and call its
   `initialize*()` in the constructor (layout → `createField()`, answer → `createFieldValue()`).
3. `config/packages/form_builder.yaml`: `classes` (4 FQCNs) + `file_url_resolver` (service id).
4. Implement `FormAnswerFileUrlResolverInterface` (+ a download route that uses `FormFileUploader::getFile()`).
5. Generate the migration (the bundle ships none). Run `doctrine:schema:validate`.
6. Layout form: `FormStructureType` field (`mapped=false`, `data=$layout->getStructure()`, `answers_count`).
   Create → `setStructure()`; update → `SyncFormLayoutStructureUseCase` (+ optionally
   `DeleteRemovedFormLayoutFieldValuesUseCase`), then flush.
7. Answer form: `buildForm($layout->getStructure(), $data, ['edit' => true] on edit)`; save with
   `setAnswerData($form->getData())`.
8. Display: `AnswerGenerator::generate()` → `{{ form_builder_answers(answers, formAnswer) }}`.
9. Front: import `assets/form-builder` and `assets/form-builder-manager`, register `assets/controllers/*` as
   `form-builder-<name>`, add Tailwind `@source` for `assets/builder/**/*.tsx`, install JS deps
   (see bundle `package.json`), define shadcn CSS tokens, load Google Maps and fire `google-maps:ready` if
   the address field is used.
10. Verify: `bin/console lint:container`, `doctrine:schema:validate`, render a page with every field type,
    save and re-display an answer, then remove a field and check its values are gone.

## Pitfalls

- `setStructure()` throws on a layout that already has fields (by design — it would break answer FKs).
- Extra keys in `setAnswerData()` are ignored; extra form fields you add (reference, etc.) must be handled
  and removed from the array by the host.
- Without the `edit` option, each field's default (or "current date") replaces the data you pass.
- Field ids (`id`) are the answer keys: unique per layout. Blocks without `id`/`type` are skipped; a block
  with an unknown `type` throws `UnknownFieldTypeException` in debug and is skipped + logged in production.
  Files of a deleted answer are removed by walking the layout structure (`FileInput`, also inside
  `FieldSet`/`Repeatable`); ids may have any format.
- FieldSet answers are nested under the fieldset id; Repeatable answers are a list of rows. Conditional answers sit
  at the same level as their owner.
- Doctrine forbids `OneToMany` in mapped superclasses: that is why the `Has*` traits exist. A missing trait
  or `initialize*()` call gives "uninitialized property $fields/$children/$fieldValues".
- Do not run the shadcn CLI inside `assets/builder` (generates `@/…` alias imports); keep imports relative.
- Conditions: `Select`/`ChoiceGroup` `conditions` are stored flat (conditional blocks are marked siblings of their
  owner) and rendered through `ConditionalFieldType` + the `form-builder-condition` controller; always go through
  `FormLayoutBlock::listFromArray()` / `getStructure()` instead of reading `conditions` by hand.
- Known gaps: builder UI text is French only; custom PHP field types are not pluggable (see
  "Known limitations" in `docs/reference.md`).
- Translation domain `form_builder_bundle` (fr). `FormBuilderType` defaults `translation_domain` to
  `form_builder` — make sure labels from user structures are not unintentionally translated.

## Where to look

| Need | File |
|---|---|
| Config tree, prepend of Twig/Doctrine config | `src/FormBuilderBundle.php` |
| Field types and their config keys | `src/Field/*.php`, `docs/reference.md` |
| Structure ↔ rows | `src/Service/FormLayoutFieldHydrator.php`, `src/Entity/AbstractFormLayout.php` |
| Answer ↔ rows | `src/Entity/AbstractFormAnswer.php` |
| Rendering blocks | `templates/form_theme/*.twig`, `templates/answers/shadcn.html.twig` |
| Front | `assets/` (`builder/`, custom elements, `controllers/`) |
| Test doubles for host entities | `tests/Fixtures/` |
| Front/PHP drift guard | `assets/builder/contract/blocks.json` (generated by `pnpm run export-contract`), checked by `tests/Unit/Contract/FrontContractTest.php` and vitest |

Quality gate for bundle changes: `composer qa` (cs-fixer, PHPStan max + strict rules, PHPUnit).
