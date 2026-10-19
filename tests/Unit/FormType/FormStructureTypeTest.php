<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\FormType;

use AppoloDev\FormBuilderBundle\Enum\FieldCategory;
use AppoloDev\FormBuilderBundle\FormType\FormStructureType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFilter;

class FormStructureTypeTest extends TestCase
{
    public function testTheStructureIsExposedAsJsonInTheView(): void
    {
        $structure = [['id' => 'a', 'type' => 'TextInput', 'label' => 'A']];

        $view = Forms::createFormFactory()
            ->create(FormStructureType::class, $structure, ['answers_count' => 4])
            ->createView();

        self::assertSame(json_encode($structure, \JSON_THROW_ON_ERROR), $view->vars['value']);
        self::assertSame(4, $view->vars['answers_count']);
        self::assertTrue($view->vars['has_structure']);
    }

    public function testAnEmptyStructureIsFlaggedAsHavingNoStructure(): void
    {
        $view = Forms::createFormFactory()->create(FormStructureType::class, [])->createView();

        self::assertFalse($view->vars['has_structure']);
        self::assertSame(0, $view->vars['answers_count']);
    }

    public function testTheBuilderLocaleDefaultsToTheRequestLocaleThenToEnglish(): void
    {
        $request = new Request();
        $request->setLocale('fr');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $factory = Forms::createFormFactoryBuilder()
            ->addType(new FormStructureType($requestStack))
            ->getFormFactory();

        self::assertSame('fr', $factory->create(FormStructureType::class)->createView()->vars['builder_locale']);
        self::assertSame('en', $factory->create(FormStructureType::class, null, ['locale' => 'en'])->createView()->vars['builder_locale']);
        self::assertSame('en', Forms::createFormFactory()->create(FormStructureType::class)->createView()->vars['builder_locale']);
    }

    public function testSubmittedJsonIsDecodedBackToAnArray(): void
    {
        $form = Forms::createFormFactory()->create(FormStructureType::class);

        $form->submit('[{"id":"a","type":"TextInput"}]');

        self::assertSame([['id' => 'a', 'type' => 'TextInput']], $form->getData());
    }

    public function testBlankOrInvalidSubmissionsYieldNull(): void
    {
        $blank = Forms::createFormFactory()->create(FormStructureType::class);
        $blank->submit('');
        self::assertNull($blank->getData());

        $invalid = Forms::createFormFactory()->create(FormStructureType::class);
        $invalid->submit('not json');
        self::assertNull($invalid->getData());
    }

    public function testEverythingIsOfferedByDefault(): void
    {
        $view = Forms::createFormFactory()->create(FormStructureType::class)->createView();

        self::assertNull($view->vars['allowed_categories']);
        self::assertNull($view->vars['allowed_types']);
    }

    public function testCategoriesAndTypesAreExposedToTheView(): void
    {
        $view = Forms::createFormFactory()
            ->create(FormStructureType::class, null, ['categories' => ['text', 'choice'], 'types' => ['Signature']])
            ->createView();

        self::assertSame(['text', 'choice'], $view->vars['allowed_categories']);
        self::assertSame(['Signature'], $view->vars['allowed_types']);
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('invalidRestrictions')]
    public function testAnInvalidRestrictionIsRejectedWithAClearMessage(array $options, string $message): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage($message);

        Forms::createFormFactory()->create(FormStructureType::class, null, $options);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function invalidRestrictions(): iterable
    {
        yield 'unknown category' => [['categories' => ['text', 'nope']], '"nope" in the "categories" option'];
        yield 'unknown type' => [['types' => ['Foo']], '"Foo" in the "types" option'];
        yield 'empty categories' => [['categories' => []], 'cannot be empty'];
        yield 'empty types' => [['types' => []], 'cannot be empty'];
    }

    public function testCategoriesAreAlignedWithTheBuilderContract(): void
    {
        $contract = json_decode((string) file_get_contents(__DIR__.'/../../../assets/builder/contract/blocks.json'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($contract);
        self::assertIsArray($contract['types']);

        $fromContract = [];
        foreach ($contract['types'] as $type => $definition) {
            self::assertIsArray($definition);
            self::assertIsString($definition['category'] ?? null, \sprintf('Le type "%s" n\'a pas de catégorie dans blocks.json.', $type));
            $fromContract[$definition['category']][] = $type;
        }

        $fromEnum = [];
        foreach (FieldCategory::cases() as $category) {
            $types = $category->types();
            sort($types);
            $fromEnum[$category->value] = $types;
        }
        foreach ($fromContract as &$types) {
            sort($types);
        }
        unset($types);
        ksort($fromContract);
        ksort($fromEnum);

        self::assertSame($fromContract, $fromEnum);
    }

    public function testTheRestrictionReachesTheBuilderElement(): void
    {
        $twig = new Environment(new ArrayLoader([
            'structure' => (string) file_get_contents(__DIR__.'/../../../templates/form_theme/structure.html.twig').'{{ block("form_builder_structure_widget") }}',
        ]));
        $twig->addFilter(new TwigFilter('trans', static fn (string $m): string => $m));
        $render = static fn (array $vars): string => $twig->createTemplate(
            '{% extends "structure" %}'
        )->render($vars);

        $base = ['value' => '', 'id' => 'x', 'full_name' => 'x', 'answers_count' => 0, 'builder_locale' => 'en', 'has_structure' => false];

        self::assertStringContainsString('<form-builder locale="en"></form-builder>', $render([...$base, 'allowed_categories' => null, 'allowed_types' => null]));
        self::assertStringContainsString('<form-builder locale="en" categories="text,choice" types="Signature"></form-builder>', $render([...$base, 'allowed_categories' => ['text', 'choice'], 'allowed_types' => ['Signature']]));
    }
}
