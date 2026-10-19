<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Support;

use AppoloDev\FormBuilderBundle\Field\AbstractChoiceField;
use AppoloDev\FormBuilderBundle\Field\FieldFactory;
use AppoloDev\FormBuilderBundle\Field\FieldInterface;
use AppoloDev\FormBuilderBundle\File\FormFileUploader;
use AppoloDev\FormBuilderBundle\Form\FormTypeGenerator;
use AppoloDev\FormBuilderBundle\FormType\ConditionalFieldType;
use AppoloDev\FormBuilderBundle\FormType\FormBuilderType;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Assemble, sans conteneur, la chaîne réelle structure JSON → FormTypeGenerator → FormTypes du bundle
 * (les champs, les types de formulaire, la validation), pour les tests de rendu et de contrat.
 */
final class FormHarness
{
    private const FIELD_NAMESPACE = 'AppoloDev\\FormBuilderBundle\\Field\\';
    private const TYPE_NAMESPACE = 'AppoloDev\\FormBuilderBundle\\FormType\\';

    public readonly FormFactoryInterface $factory;
    public readonly FormTypeGenerator $generator;

    public function __construct()
    {
        $fields = [];
        $translator = new ArrayTranslator();
        foreach (self::files('Field') as $file) {
            $class = self::FIELD_NAMESPACE.basename($file, '.php');
            if (class_exists($class) && is_subclass_of($class, FieldInterface::class) && !(new \ReflectionClass($class))->isAbstract()) {
                $fields[$class] = static fn (): FieldInterface => is_subclass_of($class, AbstractChoiceField::class) ? new $class($translator) : new $class();
            }
        }

        // Les FormTypes ont besoin du générateur, qui a besoin de la fabrique de formulaires : on lie les deux après coup.
        $factoryProxy = new LateFormFactory();
        $this->generator = new FormTypeGenerator($factoryProxy, new FieldFactory(new ServiceLocator($fields)));
        $this->factory = TestForms::factory($this->types());
        $factoryProxy->set($this->factory);
    }

    /**
     * @param array<int, array<mixed, mixed>> $structure
     * @param array<string, mixed>            $data
     * @param array<string, mixed>            $options
     */
    public function form(array $structure, array $data = [], array $options = []): FormInterface
    {
        return $this->generator->buildForm($structure, $data, $options);
    }

    /**
     * Gabarits des messages d'erreur de tout le formulaire (clés de traduction, avant traduction).
     *
     * @return list<string>
     */
    public static function errors(FormInterface $form): array
    {
        $templates = [];
        foreach ($form->getErrors(true, true) as $error) {
            if ($error instanceof FormError) {
                $templates[] = $error->getMessageTemplate();
            }
        }

        return $templates;
    }

    /**
     * @return list<string>
     */
    private static function files(string $directory): array
    {
        $files = glob(__DIR__.'/../../src/'.$directory.'/*.php');

        return false === $files ? [] : $files;
    }

    /**
     * @return list<FormTypeInterface>
     */
    private function types(): array
    {
        $types = [];
        $uploader = new FormFileUploader(sys_get_temp_dir().'/form-builder-tests/');
        foreach (self::files('FormType') as $file) {
            $class = self::TYPE_NAMESPACE.basename($file, '.php');
            if (!class_exists($class)) {
                continue;
            }
            $reflection = new \ReflectionClass($class);
            if ($reflection->isAbstract() || !$reflection->implementsInterface(FormTypeInterface::class) || FormBuilderType::class === $class) {
                continue;
            }

            $args = [];
            foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
                $name = $parameter->getType() instanceof \ReflectionNamedType ? $parameter->getType()->getName() : '';
                if (FormTypeGenerator::class === $name) {
                    $args[] = $this->generator;
                } elseif (FormFileUploader::class === $name) {
                    $args[] = $uploader;
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $args[] = $parameter->getDefaultValue();
                }
            }

            /** @var FormTypeInterface $type */
            $type = $reflection->newInstanceArgs($args);
            $types[] = $type;
        }
        $types[] = new FormBuilderType();
        unset($types[array_search(ConditionalFieldType::class, array_map('get_class', $types), true)]);
        $types[] = new ConditionalFieldType($this->generator);

        return array_values($types);
    }
}
