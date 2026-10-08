<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Service;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerInterface;
use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;

/**
 * Fichiers envoyés dans une réponse, repérés d'après la structure du modèle (champs `FileInput`, y compris dans
 * un `FieldSet`, un `Repeatable` ou un bloc conditionnel) et non d'après le format des ids.
 */
final class AnswerFiles
{
    /**
     * @return list<array<array-key, mixed>> données de chaque fichier (`filename`, `originalFilename`, `extension`)
     */
    public function of(FormAnswerInterface $answer): array
    {
        return $this->collect(
            FormLayoutBlock::listFromArray($answer->getFormLayout()->getStructure()),
            $answer->getAnswerData(),
        );
    }

    /**
     * @return list<string> noms de fichiers stockés
     */
    public function filenames(FormAnswerInterface $answer): array
    {
        $filenames = [];
        foreach ($this->of($answer) as $file) {
            if (\is_string($file['filename'] ?? null)) {
                $filenames[] = $file['filename'];
            }
        }

        return $filenames;
    }

    /**
     * Le fichier `$filename` appartient-il bien à cette réponse ?
     */
    public function owns(FormAnswerInterface $answer, string $filename): bool
    {
        return \in_array($filename, $this->filenames($answer), true);
    }

    /**
     * @param FormLayoutBlock[]       $blocks
     * @param array<array-key, mixed> $data   valeurs des blocs, indexées par id de bloc
     *
     * @return list<array<array-key, mixed>>
     */
    private function collect(array $blocks, array $data): array
    {
        $files = [];
        foreach ($blocks as $block) {
            $value = null === $block->id ? null : ($data[$block->id] ?? null);
            if (!\is_array($value)) {
                continue;
            }

            if ('FileInput' === $block->type) {
                foreach ($value as $item) {
                    if (\is_array($item) && \is_array($item['file'] ?? null)) {
                        $files[] = $item['file'];
                    }
                }
                continue;
            }

            $nested = match (FieldKindResolver::resolve((string) $block->type)) {
                FieldKind::Container => $this->collect($block->children, $value),
                FieldKind::Repeatable => $this->collectRows($block->children, $value),
                default => [],
            };
            array_push($files, ...$nested);
        }

        return $files;
    }

    /**
     * @param FormLayoutBlock[]       $children
     * @param array<array-key, mixed> $rows
     *
     * @return list<array<array-key, mixed>>
     */
    private function collectRows(array $children, array $rows): array
    {
        $files = [];
        foreach ($rows as $row) {
            if (\is_array($row)) {
                array_push($files, ...$this->collect($children, $row));
            }
        }

        return $files;
    }
}
