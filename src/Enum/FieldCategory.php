<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Enum;

/**
 * Catégories de types de champs du builder (présentation du menu d'ajout et restriction des champs proposés par
 * `FormStructureType`). Alignées sur `assets/builder/contract/blocks.json` (vérifié par un test).
 * Ce n'est pas une donnée de la structure d'un formulaire : aucun bloc n'enregistre sa catégorie.
 */
enum FieldCategory: string
{
    case Text = 'text';
    case Choice = 'choice';
    case Number = 'number';
    case DateTime = 'datetime';
    case File = 'file';
    case Special = 'special';
    case Layout = 'layout';
    case Group = 'group';

    /**
     * @return list<string> types du builder de la catégorie
     */
    public function types(): array
    {
        return match ($this) {
            self::Text => ['TextInput', 'TextareaInput', 'EmailInput', 'TelInput', 'UrlInput'],
            self::Choice => ['Select', 'ChoiceGroup'],
            self::Number => ['NumberInput'],
            self::DateTime => ['DateTimeInput', 'HourMinuteInput'],
            self::File => ['FileInput'],
            self::Special => ['AddressInput', 'Signature'],
            self::Layout => ['Title', 'Paragraph'],
            self::Group => ['FieldSet', 'Repeatable'],
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $category): string => $category->value, self::cases());
    }

    /**
     * @return list<string> tous les types connus du builder
     */
    public static function allTypes(): array
    {
        return array_merge(...array_map(static fn (self $category): array => $category->types(), self::cases()));
    }
}
