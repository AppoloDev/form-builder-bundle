<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\UseCase;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFieldValueInterface;
use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormLayout;
use AppoloDev\FormBuilderBundle\UseCase\DeleteRemovedFormLayoutFieldValuesUseCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Types\UuidType;

class DeleteRemovedFormLayoutFieldValuesUseCaseTest extends TestCase
{
    public function testDeletesFieldValuesOnlyForKeysRemovedFromTheStructure(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            ['id' => 'kept', 'type' => 'ShortText', 'label' => 'Kept'],
            ['id' => 'removed', 'type' => 'ShortText', 'label' => 'Removed'],
        ]);

        $removedField = $formLayout->getFieldByKey('removed');
        self::assertNotNull($removedField);

        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('setParameter')->with('field', $removedField->getId(), UuidType::NAME)->willReturnSelf();
        $query->expects(self::once())->method('execute')->willReturn(3);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('createQuery')
            ->with(\sprintf('DELETE FROM %s fafv WHERE fafv.formLayoutField = :field', FormAnswerFieldValueInterface::class))
            ->willReturn($query)
        ;

        $useCase = new DeleteRemovedFormLayoutFieldValuesUseCase($entityManager);

        $deleted = $useCase($formLayout, [
            ['id' => 'kept', 'type' => 'ShortText', 'label' => 'Kept'],
        ]);

        self::assertSame(3, $deleted);
    }

    public function testReturnsZeroAndSkipsDeletionWhenNoKeyWasRemoved(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            ['id' => 'kept', 'type' => 'ShortText', 'label' => 'Kept'],
        ]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('createQuery');

        $useCase = new DeleteRemovedFormLayoutFieldValuesUseCase($entityManager);

        $deleted = $useCase($formLayout, [
            ['id' => 'kept', 'type' => 'ShortText', 'label' => 'Kept'],
        ]);

        self::assertSame(0, $deleted);
    }
}
