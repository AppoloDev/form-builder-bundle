# Installation

## 1. Register the bundle

`config/bundles.php` (Flex does it automatically):

```php
AppoloDev\FormBuilderBundle\FormBuilderBundle::class => ['all' => true],
```

## 2. Create the four entities

Extend the base classes. Each entity needs a `Uuid` id (`getId(): ?Uuid`) because the bundle's associations
join on `id`. Doctrine forbids `OneToMany` in a mapped superclass, so the inverse sides come from traits that
you `use` and initialise in the constructor.

```php
use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use AppoloDev\FormBuilderBundle\Entity\AbstractFormLayout;
use AppoloDev\FormBuilderBundle\Entity\Concern\HasFormLayoutFields;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class FormLayout extends AbstractFormLayout
{
    use HasFormLayoutFields;                       // fields (OneToMany)

    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM'), ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    public function __construct() { $this->initializeFormLayoutFields(); }
    public function getId(): ?Uuid { return $this->id; }
    public function createField(): FormLayoutFieldInterface { return new FormLayoutField(); }
}
```

| Base class | Trait | Constructor call | Abstract method |
|---|---|---|---|
| `AbstractFormLayout` | `HasFormLayoutFields` | `initializeFormLayoutFields()` | `createField()` |
| `AbstractFormLayoutField` | `HasFormLayoutFieldChildren` | `initializeFormLayoutFieldChildren()` | — |
| `AbstractFormAnswer` | `HasFormAnswerFieldValues` | `initializeFormAnswerFieldValues()` | `createFieldValue()` |
| `AbstractFormAnswerFieldValue` | — | — | — |

Each also needs `#[ORM\Entity]`, an id, and `getId()`. Add your own columns freely (company, user,
timestamps, reference…). The bundle ships no migration: generate it with `make:migration` /
`doctrine:schema:update`.

## 3. Configure

```yaml
# config/packages/form_builder.yaml
form_builder:
    classes:
        form_layout: App\Entity\FormLayout
        form_layout_field: App\Entity\FormLayoutField
        form_answer: App\Entity\FormAnswer
        form_answer_field_value: App\Entity\FormAnswerFieldValue
    file_url_resolver: App\Form\FormAnswerFileUrlResolver
```

The bundle derives Doctrine's `resolve_target_entities` from `classes`. All options: [reference](reference.md#configuration).

## 4. Implement the file URL resolver

Used to link uploaded files in the answers view.

```php
use AppoloDev\FormBuilderBundle\Contract\FormAnswerFileUrlResolverInterface;

final class FormAnswerFileUrlResolver implements FormAnswerFileUrlResolverInterface
{
    public function __construct(private UrlGeneratorInterface $urls) {}

    public function resolveDownloadUrl(string $formAnswerId, string $filename): string
    {
        return $this->urls->generate('answer_file_download', ['id' => $formAnswerId, 'filePath' => $filename],
            UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
```

## 5. Wire the front end

See [frontend.md](frontend.md#host-setup): import the two custom elements, register the Stimulus controllers,
add the Tailwind `@source`, install the JS dependencies.

## 6. Check the form theme

Your global form theme must render standard types (text, choice, date…). The bundle only themes its own
types (fieldset, title, paragraph, repeatable, address, signature, file list). The default theme needs the
`UI:Shadcn:Button` component and Phosphor icons `ph:plus`, `ph:x`, `ph:file-pdf`. To use another design system:

```yaml
form_builder:
    form_theme: 'form/my_form_builder_theme.html.twig'   # must define the same blocks, see reference
```
