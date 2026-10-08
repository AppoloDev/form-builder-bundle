<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\ValueObject;

/**
 * Parcours générique et inconditionnel (pré-ordre, tous les blocs y compris ceux avec
 * id/type manquants) de l'arbre {@see FormLayoutBlock}. Pour les traversées qui doivent au
 * contraire ignorer tout un sous-arbre dès que le bloc racine est invalide — construction de
 * FormLayoutField dans FormLayout::hydrateFields() et SyncFormLayoutStructureUseCase::processBlocks() —
 * ce walker ne convient pas, ces deux-là gardent leur propre boucle avec `continue`.
 */
final class FormLayoutBlockTreeWalker
{
    /**
     * @param FormLayoutBlock[]               $blocks
     * @param callable(FormLayoutBlock): void $visit
     */
    public static function walk(array $blocks, callable $visit): void
    {
        foreach ($blocks as $block) {
            $visit($block);

            if ([] !== $block->children) {
                self::walk($block->children, $visit);
            }
        }
    }
}
