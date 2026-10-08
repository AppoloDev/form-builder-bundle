# Front end

## How it fits together

`FormStructureType` renders (the `<form-builder>` element receives a `locale` attribute, `fr` or `en`):

```html
<form-builder-manager data-answers-count="3" data-confirm-template="…">
    <input type="hidden" name="…[content]" value="[…structure JSON…]">
    <form-builder json="[…structure JSON…]"></form-builder>
</form-builder-manager>
```

- `<form-builder>` mounts the React builder. It reads the `json` attribute and dispatches a `change` event whose
  `detail` is the new structure (array of blocks).
- `<form-builder-manager>` copies `detail` into the hidden input as JSON, and on submit of the enclosing
  `<form>` asks `window.confirm` when blocks that existed at load were removed and `answers-count > 0`.
  Message parts come from `data-confirm-template`, `data-fields-one|many`, `data-answers-one|many`
  (translated server-side).

## Host setup

1. **Install JS dependencies** (see `package.json` of the bundle): `react`, `react-dom`, `zustand`, `uuid`,
   `@base-ui/react`, `@dnd-kit/core|sortable|utilities`, `class-variance-authority`, `cn`, `lucide-react`,
   `signature_pad`, `@hotwired/stimulus`. Resolve the bundle's `assets/` path from `vendor/appolodev/form-builder-bundle/assets`
   (or its real path when installed as a path repository).

2. **Import the custom elements** in your entry point:

   ```js
   import '../vendor/appolodev/form-builder-bundle/assets/form-builder';
   import '../vendor/appolodev/form-builder-bundle/assets/form-builder-manager';
   ```

3. **Register the Stimulus controllers** with the `form-builder-` prefix (the theme refers to these names):

   ```js
   const controllers = import.meta.glob('../vendor/appolodev/form-builder-bundle/assets/controllers/*_controller.js', { eager: true });
   for (const path in controllers) {
       const name = path.match(/\/controllers\/(.+)_controller\.js$/)[1].replace(/_/g, '-');
       application.register(`form-builder-${name}`, controllers[path].default);
   }
   ```

4. **Tell Tailwind about the builder's classes** (`tailwind.css`):

   ```css
   @source '../vendor/appolodev/form-builder-bundle/assets/builder/**/*.tsx';
   ```

   The builder uses shadcn design tokens (`--background`, `--border`, `--primary`, …); define them in your theme.

5. **Vite**: React plugin (`@vitejs/plugin-react`) is required for TSX. No alias is needed (imports in
   `assets/builder` are relative).

## Controllers

| Identifier | Used by | Notes |
|---|---|---|
| `form-builder-collection` | `repeatable_row`, `file_repeatable_row` | Add/remove rows from a `data-prototype` |
| `form-builder-geo-complete` | `address_row` | Google Places autocomplete. The app must load the Maps JS API and dispatch `google-maps:ready` on `document` (or set `window.googleMapsReady`) |
| `form-builder-condition` | `form_builder_conditional_field_row` | Shows/hides a conditional field from the owner's value (`<select>`, or radio/checkbox container with the owner's id); disables hidden controls so they are not submitted |
| `form-builder-sign-area` | `signature_row` | `signature_pad` canvas; writes an SVG data URI into the hidden input. Values: `input`, `clear-label`, `modify-label` |

## Builder sources

`assets/builder/` is the React app (Zustand store, dnd-kit, base-ui). Entry: `FormBuilder.tsx` (props: `json`,
`onChange`). Block types are declared in `components/Blocks/Definition.ts` (`BlockDefinitions`,
`BlockRegistry.tsx`). Imports must stay relative — do not run the shadcn CLI as-is, it generates `@/…` aliases.
