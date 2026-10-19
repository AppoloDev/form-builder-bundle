<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Rendering;

use AppoloDev\FormBuilderBundle\Tests\Support\FormHarness;
use AppoloDev\FormBuilderBundle\Tests\Support\FormRenderingHarness;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Contrat de rendu : pour chaque type du builder, un bloc configuré avec toutes ses options est converti en
 * formulaire Symfony puis rendu avec le thème du bundle (sur `form_div_layout`). Chaque cas liste des expressions
 * XPath qui doivent toutes trouver au moins un nœud : libellé, aide, attributs, options de choix, contrôleurs
 * Stimulus. Une application qui fournit son propre thème peut reprendre ce test avec `FormRenderingHarness`.
 */
class FormRenderingTest extends TestCase
{
    private const INPUT = "//input[@id='form_builder_f']";
    private const OPTIONS = [['label' => 'Oui', 'isSelected' => false], ['label' => 'Non', 'isSelected' => false]];

    /**
     * @param array<string, mixed> $block
     * @param list<string>         $expected
     */
    #[DataProvider('cases')]
    public function testEveryBuilderTypeIsRenderedWithItsOptions(array $block, array $expected): void
    {
        $html = (new FormRenderingHarness())->render((new FormHarness())->form([['id' => 'f', ...$block]]));
        $xpath = FormRenderingHarness::dom($html);

        foreach ($expected as $query) {
            $nodes = $xpath->query($query);
            self::assertNotFalse($nodes, $query);
            self::assertGreaterThan(0, $nodes->length, \sprintf("%s : « %s » introuvable dans\n%s", $block['type'], $query, $html));
        }

        // Aucun champ date / heure ne retombe sur des listes déroulantes.
        if (str_starts_with((string) $block['type'], 'Date') || 'HourMinuteInput' === $block['type']) {
            self::assertStringNotContainsString('<select', $html);
        }
    }

    public function testEveryTypeOfTheBuilderContractHasARenderingCase(): void
    {
        $contract = json_decode((string) file_get_contents(__DIR__.'/../../../assets/builder/contract/blocks.json'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($contract);
        self::assertIsArray($contract['types']);

        $covered = array_unique(array_map(static fn (array $case): string => (string) $case[0]['type'], iterator_to_array(self::cases(), false)));
        $missing = array_values(array_diff(array_keys($contract['types']), $covered));

        self::assertSame([], $missing, 'Types du builder sans cas de rendu : ajoutez-les à FormRenderingTest::cases().');
    }

    /**
     * @return iterable<string, array{array<string, mixed>, list<string>}>
     */
    public static function cases(): iterable
    {
        $text = static fn (string $type, string $htmlType): array => [
            ['type' => $type, 'label' => 'Nom', 'helpText' => 'Aide', 'placeHolder' => 'Saisir', 'defaultValue' => 'abc', 'required' => true, 'readOnly' => true],
            [
                "//label[@for='form_builder_f'][normalize-space()='Nom']",
                self::INPUT."[@type='$htmlType'][@required][@disabled][@placeholder='Saisir'][@value='abc']",
                "//*[@id='form_builder_f_help'][normalize-space()='Aide']",
            ],
        ];

        yield 'TextInput' => $text('TextInput', 'text');
        yield 'EmailInput' => $text('EmailInput', 'email');
        yield 'TelInput' => $text('TelInput', 'tel');
        // UrlType rend un champ texte avec `inputmode="url"` (le protocole par défaut est ajouté à la saisie).
        yield 'UrlInput' => $text('UrlInput', 'text');

        yield 'TextareaInput' => [
            ['type' => 'TextareaInput', 'label' => 'Message', 'helpText' => 'Aide', 'placeHolder' => 'Saisir', 'defaultValue' => 'abc', 'required' => true, 'rows' => 7],
            [
                "//label[@for='form_builder_f'][normalize-space()='Message']",
                "//textarea[@id='form_builder_f'][@rows='7'][@required][@placeholder='Saisir'][normalize-space()='abc']",
                "//*[@id='form_builder_f_help'][normalize-space()='Aide']",
            ],
        ];

        yield 'NumberInput' => [
            ['type' => 'NumberInput', 'label' => 'Quantité', 'helpText' => 'Aide', 'placeHolder' => 'Nb', 'defaultValue' => 4, 'min' => 2, 'max' => 10, 'step' => 2, 'required' => true],
            [
                "//label[@for='form_builder_f'][normalize-space()='Quantité']",
                self::INPUT."[@type='number'][@min='2'][@max='10'][@step='2'][@required][@placeholder='Nb'][@value='4']",
                "//*[@id='form_builder_f_help'][normalize-space()='Aide']",
            ],
        ];

        yield 'DateTimeInput (date)' => [
            ['type' => 'DateTimeInput', 'label' => 'Jour', 'helpText' => 'Aide', 'mode' => 'date', 'required' => true, 'hasCurrentDate' => true],
            ["//label[normalize-space()='Jour']", self::INPUT."[@type='date'][@required][@value]", "//*[@id='form_builder_f_help']"],
        ];
        yield 'DateTimeInput (time)' => [
            ['type' => 'DateTimeInput', 'label' => 'Heure', 'mode' => 'time', 'required' => true],
            ["//label[normalize-space()='Heure']", self::INPUT."[@type='time'][@required]"],
        ];
        yield 'DateTimeInput (datetime-local)' => [
            ['type' => 'DateTimeInput', 'label' => 'Moment', 'mode' => 'datetime-local', 'required' => true],
            ["//label[normalize-space()='Moment']", "//input[@id='form_builder_f_date'][@type='date']", "//input[@id='form_builder_f_time'][@type='time']"],
        ];
        yield 'HourMinuteInput' => [
            ['type' => 'HourMinuteInput', 'label' => 'Départ', 'helpText' => 'Aide', 'defaultValue' => '08:30', 'required' => true],
            ["//label[normalize-space()='Départ']", self::INPUT."[@type='time'][@required][@value='08:30']", "//*[@id='form_builder_f_help']"],
        ];

        yield 'Select' => [
            ['type' => 'Select', 'label' => 'Réponse', 'helpText' => 'Aide', 'options' => self::OPTIONS],
            [
                "//label[normalize-space()='Réponse']",
                "//select[@id='form_builder_f']/option[@value=''][normalize-space()='choice.empty']",
                "//select[@id='form_builder_f']/option[@value='Oui'][normalize-space()='Oui']",
                "//select[@id='form_builder_f']/option[@value='Non'][normalize-space()='Non']",
                "//*[@id='form_builder_f_help'][normalize-space()='Aide']",
            ],
        ];
        yield 'Select (multiple, requis)' => [
            ['type' => 'Select', 'label' => 'Réponses', 'multiple' => true, 'required' => true, 'options' => self::OPTIONS],
            ["//select[@id='form_builder_f'][@multiple][@required]/option[@value='Oui'][normalize-space()='Oui']"],
        ];
        yield 'ChoiceGroup' => [
            ['type' => 'ChoiceGroup', 'label' => 'Choix', 'helpText' => 'Aide', 'required' => true, 'options' => self::OPTIONS],
            [
                "//label[normalize-space()='Choix']",
                "//input[@type='radio'][@name='form_builder[f]'][@value='Oui']",
                "//label[@for='form_builder_f_0'][normalize-space()='Oui']",
                "//label[@for='form_builder_f_1'][normalize-space()='Non']",
                "//*[@id='form_builder_f_help'][normalize-space()='Aide']",
            ],
        ];
        yield 'ChoiceGroup (multiple)' => [
            ['type' => 'ChoiceGroup', 'label' => 'Cases', 'multiple' => true, 'options' => self::OPTIONS],
            ["//input[@type='checkbox'][@value='Oui']", "//label[@for='form_builder_f_1'][normalize-space()='Non']"],
        ];

        yield 'FileInput' => [
            ['type' => 'FileInput', 'label' => 'Pièce', 'helpText' => 'Aide', 'required' => true, 'acceptedFile' => 'image', 'allowMultiple' => true, 'maxItems' => 3],
            [
                "//div[@data-controller='form-builder-collection'][@data-max-items='3']",
                "//div[@data-controller='form-builder-collection'][contains(@data-prototype,'type=\"file\"')][contains(@data-prototype,'Pièce')]",
            ],
        ];
        yield 'AddressInput' => [
            ['type' => 'AddressInput', 'label' => 'Adresse', 'helpText' => 'Aide', 'placeHolder' => 'Où ?', 'required' => true],
            ["//label[normalize-space()='Adresse']", "//div[@data-controller='form-builder-geo-complete']//input[@required]", "//*[@id='form_builder_f_help']"],
        ];
        yield 'Signature' => [
            ['type' => 'Signature', 'label' => 'Signer', 'helpText' => 'Aide', 'required' => true],
            ["//label[normalize-space()='Signer']", "//div[@data-controller='form-builder-sign-area']", "//*[@id='form_builder_f_help']"],
        ];

        yield 'Title' => [
            ['type' => 'Title', 'text' => 'Mon titre', 'heading' => 'h2'],
            ["//h2[@id='form_builder_f'][normalize-space()='Mon titre']"],
        ];
        yield 'Paragraph' => [
            ['type' => 'Paragraph', 'text' => 'Un paragraphe'],
            ["//p[@id='form_builder_f'][normalize-space()='Un paragraphe']"],
        ];
        yield 'FieldSet' => [
            ['type' => 'FieldSet', 'children' => [['id' => 'child', 'type' => 'TextInput', 'label' => 'Enfant']]],
            ["//fieldset[@id='form_builder_f']//label[normalize-space()='Enfant']", "//fieldset//input[@id='form_builder_f_child']"],
        ];
        yield 'Repeatable' => [
            ['type' => 'Repeatable', 'maxItems' => 3, 'children' => [['id' => 'child', 'type' => 'TextInput', 'label' => 'Ligne']]],
            [
                "//div[@data-controller='form-builder-collection'][@data-max-items='3'][contains(@data-prototype,'Ligne')]",
                "//div[@data-controller='form-builder-collection'][contains(@data-prototype,'template id=\"add-btn\"')][contains(@data-prototype,'template id=\"remove-btn\"')]",
            ],
        ];
    }
}
