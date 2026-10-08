# appolodev/form-builder-bundle

Symfony bundle that lets users build custom forms (React builder), then renders them as Symfony forms,
stores the answers, and displays them.

| Concern | Provided by the bundle | Left to your app |
|---|---|---|
| Form structure | Base entities, sync use cases, `FormStructureType` (embeds the builder) | Concrete entities, controllers, access control |
| Answering | `FormTypeGenerator` (structure → Symfony form), `AbstractFormAnswer::setAnswerData()` | Routes, persistence, extra columns (company, user…) |
| Display | `form_builder_answers()` Twig function + templates | Layout, PDF styling |
| Files / signatures | Upload storage, cleanup listener | Download route and voter |
| Front | Builder (React), custom elements, Stimulus controllers | Vite/Tailwind wiring |

No controllers, routes, voters or migrations are shipped.

## Requirements

- PHP ≥ 8.4, Symfony 8.1, Doctrine ORM 3, `symfony/uid` (entity ids are `Uuid`)
- Default theme and templates use `symfony/ux-twig-component`, `symfony/ux-icons` (Phosphor `ph:` icons)
  and the shadcn kit of `symfony/ux-toolkit` (`UI:Shadcn:*` components). Other themes can be configured.
- Front: Vite (or any bundler handling TSX), Tailwind 4, Stimulus.

## Install

```bash
composer require appolodev/form-builder-bundle
```

Then follow [docs/installation.md](docs/installation.md).

## Documentation

- [Installation](docs/installation.md) — entities, configuration, front wiring
- [Usage](docs/usage.md) — admin, answering, display, files, PDF
- [Reference](docs/reference.md) — configuration, services, Twig, data shapes, field types
- [Front end](docs/frontend.md) — custom elements, Stimulus controllers, builder
- [`skills/form-builder-integration`](skills/form-builder-integration/SKILL.md) — Claude Code skill (backend/front model + integration checklist). Install: copy the folder to `.claude/skills/` of your project.

## Development

```bash
composer install
composer qa        # php-cs-fixer + PHPStan (max, strict rules) + PHPUnit
pnpm install
pnpm run typecheck # tsc --noEmit on assets/builder
pnpm run test      # vitest, including the front/PHP contract check
pnpm run export-contract   # regenerate assets/builder/contract/blocks.json after changing a block
```

CI runs the same on PHP 8.4 and 8.5, plus the front checks. Tests are standalone unit tests; host entities are simulated by
`tests/Fixtures`.

## License

MIT
