# form-builder-bundle

Bundle Symfony 8.1 (PHP ≥ 8.4, Doctrine ORM 3) + builder React 19 dans `assets/builder`. Périmètre : README.md ; détails : `docs/`.
`Plan.md` = plan d'alignement front↔PHP (conditionne la v1.0.0), `TODO.md` = futurs types de champs.

## Commandes

```bash
composer qa                # php-cs-fixer (dry-run) + PHPStan (niveau max, strict) + PHPUnit
composer cs-fix
pnpm run typecheck
pnpm run test              # vitest, y compris la vérification que le contrat est à jour
pnpm run export-contract   # régénère assets/builder/contract/blocks.json — après TOUTE modification d'un bloc
```

## Organisation

- `src/Field/<Type>.php` : une classe par type de bloc. `FieldFactory` résout `type` → classe par son nom.
- `assets/builder/components/Blocks/Definition.ts` (définitions des blocs), `Blocks/Categories.ts`, `components/BlockRegistry.tsx` (type → composant)
- `templates/form_theme/default.html.twig` (formulaire de réponse), `templates/answers/shadcn.html.twig` (`{% block <Type> %}`)
- `assets/controllers/` : contrôleurs Stimulus (préfixe `form-builder-`) ; `assets/lib/condition.js` : logique des conditions
- `tests/Fixtures` : fausses entités hôtes ; `tests/Support` : harnais de formulaire et de rendu

## Contrat front ↔ PHP

Ajouter ou renommer un type de bloc touche : `Definition.ts`, `BlockRegistry.tsx`, `Categories.ts`, `i18n/{fr,en}.ts`,
`src/Field/<Type>.php`, `Enum/FieldCategory.php`, le bloc du template des réponses, `docs/reference.md` (non testé),
puis `pnpm run export-contract`.
- `tests/Unit/Contract/FrontContractTest.php` échoue si une clé émise par le builder n'est pas lue par PHP, ou l'inverse ;
  les clés encore lues mais plus émises vont dans sa constante `LEGACY`.
- `BuilderKeysEffectTest` : chaque clé du builder doit modifier le formulaire rendu.

## Pièges

- Les classes Tailwind vont uniquement dans des fichiers `.tsx` — le `@source` de l'hôte scanne `assets/builder/**/*.tsx`.
- Les imports du builder restent relatifs (pas d'alias `@/` ; ne pas lancer la CLI shadcn telle quelle).
- `i18n.test.ts` impose des clés et des placeholders identiques dans `fr.ts` / `en.ts`.
- Ne jamais référencer les entités de l'hôte depuis le code du bundle ; passer par les interfaces de `src/Contract/*`.

## Conventions

- Commits : `type(form-builder): <description en français>`. Commentaires du code en français.
