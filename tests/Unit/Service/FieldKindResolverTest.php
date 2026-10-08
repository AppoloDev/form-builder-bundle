<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Service;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Service\FieldKindResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FieldKindResolverTest extends TestCase
{
    #[DataProvider('typeProvider')]
    public function testResolve(string $type, FieldKind $expected): void
    {
        self::assertSame($expected, FieldKindResolver::resolve($type));
    }

    /**
     * @return iterable<string, array{string, FieldKind}>
     */
    public static function typeProvider(): iterable
    {
        yield 'Title' => ['Title', FieldKind::DisplayOnly];
        yield 'Paragraph' => ['Paragraph', FieldKind::DisplayOnly];
        yield 'FieldSet' => ['FieldSet', FieldKind::Container];
        yield 'Repeatable' => ['Repeatable', FieldKind::Repeatable];
        yield 'TextInput' => ['TextInput', FieldKind::Value];
        yield 'Signature' => ['Signature', FieldKind::Value];
        yield 'FileInput' => ['FileInput', FieldKind::Value];
        yield 'unknown type defaults to Value' => ['SomeFutureFieldType', FieldKind::Value];
        yield 'empty string defaults to Value' => ['', FieldKind::Value];
    }
}
