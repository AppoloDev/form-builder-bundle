<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\ValueObject;

use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlockTreeWalker;
use PHPUnit\Framework\TestCase;

class FormLayoutBlockTreeWalkerTest extends TestCase
{
    public function testVisitsEveryBlockInPreOrderIncludingNestedChildren(): void
    {
        $blocks = FormLayoutBlock::listFromArray([
            [
                'id' => 'section',
                'type' => 'FieldSet',
                'children' => [
                    ['id' => 'field-a', 'type' => 'ShortText'],
                    [
                        'id' => 'repeatable',
                        'type' => 'Repeatable',
                        'children' => [
                            ['id' => 'field-b', 'type' => 'ShortText'],
                        ],
                    ],
                ],
            ],
            ['id' => 'field-c', 'type' => 'ShortText'],
        ]);

        $visitedIds = [];
        FormLayoutBlockTreeWalker::walk($blocks, static function (FormLayoutBlock $block) use (&$visitedIds): void {
            $visitedIds[] = $block->id;
        });

        self::assertSame(['section', 'field-a', 'repeatable', 'field-b', 'field-c'], $visitedIds);
    }

    public function testVisitsBlocksEvenWithoutIdOrType(): void
    {
        $blocks = FormLayoutBlock::listFromArray([
            ['type' => 'ShortText'], // no id
            ['id' => 'no-type'],
        ]);

        $visited = 0;
        FormLayoutBlockTreeWalker::walk($blocks, static function () use (&$visited): void {
            ++$visited;
        });

        self::assertSame(2, $visited);
    }

    public function testDoesNothingOnEmptyTree(): void
    {
        $visited = 0;
        FormLayoutBlockTreeWalker::walk([], static function () use (&$visited): void {
            ++$visited;
        });

        self::assertSame(0, $visited);
    }
}
