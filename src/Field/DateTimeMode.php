<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;

/**
 * Interprète la config d'un bloc DateTimeInput : `mode` (`date`, `datetime-local`, `time`), ou à défaut les
 * anciennes clés booléennes `showDate` / `showHour`.
 */
final class DateTimeMode
{
    public const FORMAT_DATE_TIME = 'd/m/Y H:i';
    public const FORMAT_DATE = 'd/m/Y';
    public const FORMAT_TIME = 'H:i';

    /**
     * @return array{0: bool, 1: bool} [showDate, showHour]
     */
    public static function resolve(FormLayoutBlock $block): array
    {
        return match ($block->configString('mode')) {
            'date' => [true, false],
            'time' => [false, true],
            'datetime-local' => [true, true],
            default => [$block->configBool('showDate') ?? false, $block->configBool('showHour') ?? false],
        };
    }

    /**
     * Format d'affichage de la réponse correspondant à la config du bloc.
     */
    public static function displayFormat(FormLayoutBlock $block): string
    {
        [$showDate, $showHour] = self::resolve($block);

        return match (true) {
            $showDate && !$showHour => self::FORMAT_DATE,
            !$showDate && $showHour => self::FORMAT_TIME,
            default => self::FORMAT_DATE_TIME,
        };
    }
}
