<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\FormType;

use AppoloDev\FormBuilderBundle\FormType\FormStructureType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
}
