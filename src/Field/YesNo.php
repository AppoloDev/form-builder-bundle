<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;

/**
 * Question fermée à deux réponses dont les libellés sont modifiables (`yesLabel`, `noLabel`), affichées en boutons :
 * radios natives habillées par le bloc de thème `yes_no_widget`. La valeur stockée est le libellé choisi, comme pour ChoiceGroup.
 */
class YesNo extends AbstractChoiceField
{
    protected function readChoices(FormLayoutBlock $block): void
    {
        // Libellé vide ignoré, doublon fusionné : Symfony indexe les choix par libellé.
        $labels = array_unique(array_filter(
            [trim($block->configString('yesLabel') ?? ''), trim($block->configString('noLabel') ?? '')],
            static fn (string $label): bool => '' !== $label,
        ));
        $this->options = array_map(static fn (string $label): array => ['label' => $label, 'isSelected' => false], array_values($labels));
        $this->multiple = false;
    }

    protected function isExpanded(): bool
    {
        return true;
    }

    /**
     * Pas de 3e bouton « Aucun » quand la question est facultative : elle reste binaire.
     */
    protected function decorateFieldOptions(array $fieldOptions): array
    {
        return [...$fieldOptions, 'placeholder' => false, 'block_prefix' => 'yes_no'];
    }
}
