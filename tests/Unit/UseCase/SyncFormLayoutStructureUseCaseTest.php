<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\UseCase;

use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormLayout;
use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormLayoutField;
use AppoloDev\FormBuilderBundle\UseCase\SyncFormLayoutStructureUseCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SyncFormLayoutStructureUseCaseTest extends TestCase
{
    public function testPreservesExistingFieldIdentityAndCreatesNewOnes(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            ['id' => 'kept', 'type' => 'ShortText', 'label' => 'Kept'],
        ]);
        $keptField = $formLayout->getFieldByKey('kept');
        self::assertNotNull($keptField);

        $persisted = null;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->willReturnCallback(static function (object $object) use (&$persisted): void {
            $persisted = $object;
        });
        $entityManager->expects(self::never())->method('remove');

        $useCase = new SyncFormLayoutStructureUseCase($entityManager);

        $useCase($formLayout, [
            ['id' => 'kept', 'type' => 'ShortText', 'label' => 'Kept (renamed)'],
            ['id' => 'new', 'type' => 'ShortText', 'label' => 'New'],
        ]);

        // Le champ existant est mis à jour en place (même identité), pas recréé.
        self::assertSame($keptField, $formLayout->getFieldByKey('kept'));
        self::assertSame('Kept (renamed)', $keptField->getLabel());
        // Le nouveau champ n'apparaît dans $formLayout->getFields() qu'après flush (relation
        // Doctrine à sens unique côté FormLayoutField) : on vérifie donc l'entité persistée.
        self::assertInstanceOf(TestFormLayoutField::class, $persisted);
        self::assertSame('new', $persisted->getFieldKey());
    }

    public function testRemovesFieldsAbsentFromTheNewContent(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            ['id' => 'kept', 'type' => 'ShortText', 'label' => 'Kept'],
            ['id' => 'removed', 'type' => 'ShortText', 'label' => 'Removed'],
        ]);
        $removedField = $formLayout->getFieldByKey('removed');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with($removedField);

        $useCase = new SyncFormLayoutStructureUseCase($entityManager);

        $useCase($formLayout, [
            ['id' => 'kept', 'type' => 'ShortText', 'label' => 'Kept'],
        ]);
    }
}
