<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Field\Concern\ValueFieldKind;
use AppoloDev\FormBuilderBundle\FormType\FileRepeatableItemType;
use AppoloDev\FormBuilderBundle\FormType\FileRepeatableType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File;

class FileInput implements FieldInterface
{
    use ValueFieldKind;

    private string $id = '';
    private string $label = '';
    private const DEFAULT_MAX_ITEMS = 5;

    /**
     * Documents au sens large (`acceptedFile: document`) : images, PDF, traitement de texte, tableurs,
     * présentations (Office et OpenDocument), texte et CSV. La contrainte File vérifie l'extension et que le
     * contenu correspond aux types MIME de cette extension (table du composant Mime), sauf types précisés ici.
     * Sert aussi au filtre du sélecteur. SVG exclu (peut porter du script).
     */
    private const DOCUMENT_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'heic', 'heif', 'bmp',
        'pdf', 'doc', 'docx', 'odt', 'rtf', 'txt', 'xls', 'xlsx', 'ods', 'ppt', 'pptx', 'odp',
        // Le contenu d'un CSV est détecté `text/plain`, absent de la table du composant Mime pour `.csv`.
        'csv' => ['text/csv', 'application/csv', 'text/plain'],
    ];

    private int $maxItems = self::DEFAULT_MAX_ITEMS;
    private bool $required = false;
    private string $helpText = '';
    private string $acceptedFile = '';

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder->add($this->id, FileRepeatableType::class, $this->getFieldOptions());

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->label = $block->label ?? '';
        $this->helpText = $block->configString('helpText') ?? '';
        // `image` ou `document` ; les anciennes valeurs (`file` = PDF, `both` = images et PDF) deviennent `document`.
        $this->acceptedFile = 'image' === $block->configString('acceptedFile') ? 'image' : 'document';
        $this->required = $block->configBool('required') ?? false;

        // `maxItems` (builder) : nombre maximum de fichiers, vide ou 0 = illimité. Sans cette clé, ancien bloc :
        // `allowMultiple: false` = un seul fichier, sinon 5.
        $this->maxItems = match (true) {
            \array_key_exists('maxItems', $block->config) => max(0, (int) ($block->configNumeric('maxItems') ?? 0)),
            false === $block->configBool('allowMultiple') => 1,
            default => self::DEFAULT_MAX_ITEMS,
        };

        return '' !== $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        // Libellé et aide appartiennent à la collection (visibles même sans fichier ajouté) : les lignes gardent
        // leur libellé par défaut au lieu de les répéter.
        $fileOptions = [
            'required' => $this->required,
        ];

        $fileOptions['constraints'] = [
            'image' === $this->acceptedFile
                ? new File(mimeTypes: ['image/*'], mimeTypesMessage: 'validators.form_builder.file_only_image')
                : new File(
                    extensions: self::DOCUMENT_EXTENSIONS,
                    extensionsMessage: 'validators.form_builder.file_only_document',
                    mimeTypesMessage: 'validators.form_builder.file_only_document',
                ),
        ];

        // Le `required` des entrées ne s'applique qu'aux fichiers ajoutés : sans contrainte sur la collection,
        // un champ requis passe la validation sans aucun fichier, et `maxItems` ne vivrait que côté JS.
        $collectionConstraints = $this->maxItems > 0 ? [new Count(max: $this->maxItems, maxMessage: 'validators.form_builder.too_many_items')] : [];
        if ($this->required) {
            array_unshift($collectionConstraints, new Count(min: 1, minMessage: 'validators.form_builder.file_required'));
        }

        return [
            'constraints' => $collectionConstraints,
            'label' => '' === $this->label ? false : $this->label,
            'help' => '' === $this->helpText ? null : $this->helpText,
            'required' => $this->required,
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => FileRepeatableItemType::class,
            'entry_options' => [
                'file_options' => $fileOptions,
            ],
            'attr' => [
                // 0 = illimité.
                'maxItems' => $this->maxItems,
                // Filtre du sélecteur et de la zone de dépôt (confort seulement : la contrainte File fait foi).
                'accept' => 'image' === $this->acceptedFile
                    ? 'image/*'
                    : implode(',', array_map(
                        // `'pdf'` ou `'csv' => [types MIME]` : l'extension est la valeur, ou la clé.
                        static fn (int|string $key, string|array $value): string => '.'.(\is_string($value) ? $value : (string) $key),
                        array_keys(self::DOCUMENT_EXTENSIONS),
                        self::DOCUMENT_EXTENSIONS,
                    )),
                // Textes de la zone (thème : file_limit_text) : `image` ou `document`.
                'fileKind' => $this->acceptedFile,
                'formBuilder' => true,
            ],
        ];
    }
}
