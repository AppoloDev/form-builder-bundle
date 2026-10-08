<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\FormBuilderInterface;

#[AutoconfigureTag('form_builder.field')]
interface FieldInterface
{
    /**
     * @param array<string, mixed> $formOptions options passées au formulaire englobant (ex. 'edit')
     */
    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool;

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface;

    public function getKind(): FieldKind;
}
