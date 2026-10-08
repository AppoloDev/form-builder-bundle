<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Contract;

use AppoloDev\FormBuilderBundle\Field\DateTimeMode;
use AppoloDev\FormBuilderBundle\Field\FieldInterface;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Garde-fou front/PHP : `assets/builder/contract/blocks.json` décrit ce que le builder émet (généré par
 * `pnpm run export-contract`, vérifié à jour par vitest). Ces tests échouent dès qu'un type ou une clé
 * du builder n'est pas géré côté PHP, ou qu'une clé lue par le PHP n'existe plus dans le builder.
 */
class FrontContractTest extends TestCase
{
    /** Clés portées par le bloc lui-même, ou traitées pour tous les blocs par ConditionalStructure (`conditions`). */
    private const STRUCTURAL_KEYS = ['id', 'type', 'label', 'text', 'children', 'conditions'];

    /**
     * Clés lues par le PHP que le builder n'émet plus (anciennes configurations conservées en lecture).
     *
     * @var array<string, list<string>>
     */
    private const LEGACY = [
        'DateTimeInput' => ['showDate', 'showHour'],
        'FileInput' => ['maxItems'],
        // Hérité de AbstractValueFieldWithDefault ; sans effet sur un champ heure natif, retiré du builder.
        'HourMinuteInput' => ['placeHolder'],
        'NumberInput' => ['allowDecimal'],
        'Select' => ['checkCases'],
    ];

    /**
     * Classes qui lisent la config pour le compte d'un champ, hors de sa hiérarchie.
     *
     * @var array<string, list<class-string>>
     */
    private const HELPERS = [
        'DateTimeInput' => [DateTimeMode::class],
    ];

    /**
     * @param array<string, mixed> $defaults
     * @param list<string>         $keys
     */
    #[DataProvider('builderTypes')]
    public function testEveryBuilderTypeHasAPhpFieldClass(string $type, array $defaults, array $keys): void
    {
        $class = self::fieldClass($type);

        self::assertTrue(class_exists($class), \sprintf('Le builder émet "%s" mais aucune classe %s n\'existe.', $type, $class));
        self::assertTrue(is_subclass_of($class, FieldInterface::class));
    }

    /**
     * @param array<string, mixed> $defaults
     * @param list<string>         $keys
     */
    #[DataProvider('builderTypes')]
    public function testABlockWithBuilderDefaultsIsAcceptedAndAddsAFormField(string $type, array $defaults, array $keys): void
    {
        $class = self::fieldClass($type);
        $field = new $class();
        self::assertInstanceOf(FieldInterface::class, $field);

        $block = FormLayoutBlock::fromArray(['id' => $type.'-1', ...$defaults]);
        self::assertTrue($field->validateDefinition($block, []), \sprintf('Le bloc par défaut "%s" est refusé par la classe PHP.', $type));

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('form');
        $formBuilder->expects(self::once())->method('add');
        $field->addFieldFromDefinition($formBuilder);
    }

    /**
     * @param array<string, mixed> $defaults
     * @param list<string>         $keys
     */
    #[DataProvider('builderTypes')]
    public function testEveryKeyEmittedByTheBuilderIsReadByPhp(string $type, array $defaults, array $keys): void
    {
        $unsupported = array_values(array_diff($keys, self::STRUCTURAL_KEYS, self::keysReadBy($type)));
        self::assertSame([], $unsupported, \sprintf('Clés émises par le builder pour "%s" mais ignorées par le PHP.', $type));
    }

    /**
     * @param array<string, mixed> $defaults
     * @param list<string>         $keys
     */
    #[DataProvider('builderTypes')]
    public function testEveryKeyReadByPhpIsEmittedByTheBuilderOrDeclaredLegacy(string $type, array $defaults, array $keys): void
    {
        $read = self::keysReadBy($type);
        $legacy = self::LEGACY[$type] ?? [];

        $orphans = array_values(array_diff($read, $keys, $legacy));
        self::assertSame([], $orphans, \sprintf('"%s" : clés lues par le PHP mais absentes du builder (les ajouter au builder ou à LEGACY).', $type));

        $stale = array_values(array_diff($legacy, $read));
        self::assertSame([], $stale, \sprintf('"%s" : ces clés LEGACY ne sont plus lues, les retirer.', $type));
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>, list<string>}>
     */
    public static function builderTypes(): iterable
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
            foreach ($defaults as $key => $value) {
                if (\is_string($key)) {
                    $typedDefaults[$key] = $value;
                }
            }

            yield $type => [$type, $typedDefaults, array_values(array_filter($keys, \is_string(...)))];
        }
    }

    /**
     * @return class-string
     */
    private static function fieldClass(string $type): string
    {
        /** @var class-string $class */
        $class = 'AppoloDev\\FormBuilderBundle\\Field\\'.$type;

        return $class;
    }

    /**
     * Clés de config lues par la classe Field d'un type, ses parents et ses classes d'aide
     * (`$block->configString('x')`, etc.), repérées dans le code source.
     *
     * @return list<string>
     */
    private static function keysReadBy(string $type): array
    {
        $classes = self::HELPERS[$type] ?? [];
        $keys = [];
        for ($reflection = new \ReflectionClass(self::fieldClass($type)); false !== $reflection; $reflection = $reflection->getParentClass()) {
            $classes[] = $reflection->getName();
        }

        foreach ($classes as $class) {
            $file = (new \ReflectionClass($class))->getFileName();
            if (false === $file) {
                continue;
            }

            preg_match_all("/->config(?:String|Bool|Numeric|Options)\\(\\s*'([A-Za-z]+)'\\s*\\)/", (string) file_get_contents($file), $matches);
            array_push($keys, ...$matches[1]);
        }

        $keys = array_values(array_unique($keys));
        sort($keys);

        return $keys;
    }
}
