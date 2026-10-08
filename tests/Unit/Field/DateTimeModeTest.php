<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\DateTimeMode;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateTimeModeTest extends TestCase
{
    /**
     * @param array<string, mixed> $config
     * @param array{bool, bool}    $expected
     */
    #[DataProvider('modeProvider')]
    public function testResolve(array $config, array $expected, string $format): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'd-1', 'type' => 'DateTimeInput', ...$config]);

        self::assertSame($expected, DateTimeMode::resolve($block));
        self::assertSame($format, DateTimeMode::displayFormat($block));
    }

    /**
     * @return iterable<string, array{array<string, mixed>, array{bool, bool}, string}>
     */
    public static function modeProvider(): iterable
    {
        yield 'mode date' => [['mode' => 'date'], [true, false], 'd/m/Y'];
        yield 'mode time' => [['mode' => 'time'], [false, true], 'H:i'];
        yield 'mode datetime-local' => [['mode' => 'datetime-local'], [true, true], 'd/m/Y H:i'];
        yield 'legacy date only' => [['showDate' => true, 'showHour' => false], [true, false], 'd/m/Y'];
        yield 'legacy hour only' => [['showDate' => false, 'showHour' => true], [false, true], 'H:i'];
        yield 'legacy both' => [['showDate' => true, 'showHour' => true], [true, true], 'd/m/Y H:i'];
        yield 'nothing configured' => [[], [false, false], 'd/m/Y H:i'];
        yield 'mode wins over legacy keys' => [['mode' => 'date', 'showDate' => false, 'showHour' => true], [true, false], 'd/m/Y'];
        yield 'unknown mode falls back to legacy' => [['mode' => 'weird', 'showHour' => true], [false, true], 'H:i'];
    }
}
