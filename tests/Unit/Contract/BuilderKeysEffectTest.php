<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Contract;

use AppoloDev\FormBuilderBundle\Tests\Support\FormHarness;
use AppoloDev\FormBuilderBundle\Tests\Support\FormRenderingHarness;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Complète FrontContractTest (qui vérifie qu'une clé est *lue*) : chaque clé que le builder peut émettre doit
 * aussi *changer le formulaire rendu*. On part du bloc par défaut du builder (`blocks.json`), on renseigne la clé
 * avec une valeur non triviale et on compare le HTML rendu. Une clé sans effet visible fait échouer le test,
 * sauf si elle est listée dans IGNORED avec la raison.
 */
class BuilderKeysEffectTest extends TestCase
{
    /**
     * Clés sans effet sur le HTML rendu, avec l'endroit où leur effet est vérifié.
     *
     * @var array<string, string>
     */
    private const IGNORED = [
        'FileInput.acceptedFile' => 'contrainte de validation du fichier (FileInputTest)',
        '*.conditions' => 'lu par ConditionalStructure avant la génération des champs (ConditionalStructureTest, ConditionalFieldTypeTest)',
    ];

    /** Valeurs « non triviales » par clé ; `Type.clé` l'emporte sur `clé`. */
    private const VALUES = [
        'label' => 'Autre libellé',
        'helpText' => 'Autre aide',
        'placeHolder' => 'Autre indice',
        'required' => true,
        'readOnly' => true,
        'multiple' => true,
        'customOption' => true,
        'hasCurrentDate' => true,
        'allowMultiple' => true,
        'maxItems' => 4,
        'rows' => 9,
        'min' => 1,
        'max' => 9,
        'step' => 3,
        'mode' => 'date',
        'heading' => 'h5',
        'text' => 'Autre texte',
        'defaultValue' => 'valeur',
        'NumberInput.defaultValue' => 7,
        'HourMinuteInput.defaultValue' => '09:15',
        'options' => [['label' => 'Oui', 'isSelected' => true], ['label' => 'Non', 'isSelected' => false]],
        'children' => [['id' => 'child', 'type' => 'TextInput', 'label' => 'Enfant']],
    ];

    /**
     * @param array<string, mixed> $defaults
     */
    #[DataProvider('builderKeys')]
    public function testTheKeyChangesTheRenderedForm(string $type, string $key, array $defaults): void
    {
        if (isset(self::IGNORED[$type.'.'.$key]) || isset(self::IGNORED['*.'.$key])) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $value = self::VALUES[$type.'.'.$key] ?? self::VALUES[$key] ?? null;
        self::assertNotNull($value, \sprintf('Aucune valeur de test pour la clé "%s" : l\'ajouter à VALUES (ou à IGNORED avec la raison).', $key));

        $base = [...$defaults, 'id' => 'f'];
        // Un choix sans option ne montre pas l'effet de `multiple`, `required`… : on part de deux options.
        if (\in_array($type, ['Select', 'ChoiceGroup'], true) && 'options' !== $key) {
            $base['options'] = self::VALUES['options'];
        }
        $changed = [...$base, $key => $value];
        self::assertNotSame($base[$key] ?? null, $value, 'La valeur de test est identique à celle par défaut.');

        self::assertNotSame(
            self::render($base),
            self::render($changed),
            \sprintf('"%s.%s" est émise par le builder mais ne change pas le formulaire rendu.', $type, $key),
        );
    }

    /**
     * @return iterable<string, array{string, string, array<string, mixed>}>
     */
    public static function builderKeys(): iterable
    {
        $contract = json_decode((string) file_get_contents(__DIR__.'/../../../assets/builder/contract/blocks.json'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($contract);
        $types = $contract['types'] ?? null;
        self::assertIsArray($types);

        foreach ($types as $type => $definition) {
            self::assertIsString($type);
            self::assertIsArray($definition);
            $defaults = $definition['defaults'] ?? [];
            $keys = $definition['keys'] ?? [];
            self::assertIsArray($defaults);
            self::assertIsArray($keys);

            $typedDefaults = [];
            foreach ($defaults as $name => $default) {
                if (\is_string($name)) {
                    $typedDefaults[$name] = $default;
                }
            }

            foreach ($keys as $key) {
                if (\is_string($key)) {
                    yield $type.'.'.$key => [$type, $key, $typedDefaults];
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $block
     */
    private static function render(array $block): string
    {
        return (new FormRenderingHarness())->render((new FormHarness())->form([$block]));
    }
}
