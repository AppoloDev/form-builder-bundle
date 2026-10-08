# appolodev/form-builder-bundle

Moteur de formulaires personnalisés pour Symfony. Un administrateur construit un formulaire avec le
builder JS (`form-builder`), le bundle :

- stocke sa **structure** (arbre de champs) et la synchronise avec les réponses (supprimer un champ
  supprime ses valeurs partout) ;
- génère un **formulaire Symfony** à partir de cette structure et enregistre les **réponses** ;
- **affiche les réponses** (et leurs fichiers, signatures, champs répétables) ;
- fournit un champ de formulaire `FormStructureType` qui embarque le builder, comme un `TextType`
  affiche un `<input>`.

Le schéma de base de données et les migrations restent à la charge de l'application.

## Installation

```json
"repositories": [{ "type": "path", "url": "lib/form-builder-bundle", "options": { "symlink": true } }],
"require": { "appolodev/form-builder-bundle": "@dev" }
```

Prérequis : `symfony/ux-twig-component` + le kit shadcn de `symfony/ux-toolkit` (composants
`UI:Shadcn:*`, utilisés par le thème par défaut), `symfony/ux-icons`, `symfony/stimulus-bundle`.

## Entités

Le bundle fournit des classes de base ; l'application crée ses entités concrètes (noms libres) et
ajoute ses propres colonnes (société, utilisateur, référence interne, dates de publication…).

| Base du bundle                | Trait à utiliser dans l'entité concrète | À implémenter            |
|-------------------------------|------------------------------------------|--------------------------|
| `AbstractFormLayout`          | `HasFormLayoutFields`                    | `createField()`          |
| `AbstractFormLayoutField`     | `HasFormLayoutFieldChildren`             | —                        |
| `AbstractFormAnswer`          | `HasFormAnswerFieldValues`               | `createFieldValue()`     |
| `AbstractFormAnswerFieldValue`| —                                        | —                        |

Doctrine interdit un `OneToMany` dans une superclasse mappée : les traits portent ces associations
et leurs constructeurs doivent appeler `initializeFormLayoutFields()`,
`initializeFormLayoutFieldChildren()` et `initializeFormAnswerFieldValues()`. Chaque entité
concrète garde son identifiant (ex. trait `Identifiable`).

## Configuration

```yaml
# config/packages/form_builder.yaml
form_builder:
    classes:                                  # obligatoire : entités concrètes de l'application
        form_layout: App\Entity\FormLayout
        form_layout_field: App\Entity\FormLayoutField
        form_answer: App\Entity\FormAnswer
        form_answer_field_value: App\Entity\FormAnswerFieldValue
    file_url_resolver: App\Form\FormAnswerFileUrlResolver   # obligatoire : service implémentant FormAnswerFileUrlResolverInterface
    upload_path: '%kernel.project_dir%/uploads/form-files/'
    form_theme: '@FormBuilder/form_theme/shadcn.html.twig'  # thème des types du form-builder
    answers_template: '@FormBuilder/answers/shadcn.html.twig'
    answers_pdf_template: ~                                  # défaut : answers_template
```

Le bundle déclare lui-même les `resolve_target_entities` de Doctrine à partir de `classes`, et
enregistre `form_theme` dans `twig.form_themes`.

## Utilisation

- **Champ du builder** : `->add('content', FormStructureType::class, ['mapped' => false, 'answers_count' => $n])`.
  Sa donnée est la structure (tableau) ; `answers_count` active la confirmation avant de supprimer
  des champs qui effaceraient des réponses.
- **Sauvegarde** : `SyncFormLayoutStructureUseCase` met à jour les champs en place (à utiliser sur un
  modèle déjà répondu) et `DeleteRemovedFormLayoutFieldValuesUseCase` supprime les valeurs des champs
  retirés. `FormLayout::setStructure()` ne sert qu'à la création.
- **Formulaire de réponse** : `FormTypeGenerator::buildForm($structure, $data, $options)`.
- **Réponses** : `$formAnswer->setAnswerData($data)` / `getAnswerData()`.
- **Affichage** : `{{ form_builder_answers(answers, formAnswer) }}` où `answers` vient de
  `AnswerGenerator::generate($structure, $formAnswer->getAnswerData())`.

## Assets JS

`assets/` contient tout le front :

- `builder/` : le builder React (ex-dépôt `AppoloDev/form-builder`, fusionné ici — il ne s'utilise plus en
  standalone). Les imports y sont **relatifs** (pas d'alias `@/`) pour que l'application hôte n'ait rien à
  configurer côté Vite ; n'utilisez donc pas la CLI shadcn telle quelle (elle génère des imports `@/…`).
- `form-builder.jsx` / `form-builder-manager.js` : les custom elements `<form-builder>` et
  `<form-builder-manager>` rendus par `FormStructureType` ;
- `controllers/` : contrôleurs Stimulus `form-builder-collection`, `form-builder-geo-complete` et
  `form-builder-sign-area`.

L'hôte importe les deux custom elements, enregistre les contrôleurs (préfixe `form-builder-`) et ajoute
`@source '…/form-builder-bundle/assets/builder/**/*.tsx'` à sa feuille Tailwind. Dépendances JS : voir
`package.json` (à déclarer aussi côté application). Le champ adresse attend que l'application charge l'API
Google Maps Places et émette l'événement `google-maps:ready`.

## Tests

```
vendor/bin/phpunit -c lib/form-builder-bundle/phpunit.dist.xml
```
