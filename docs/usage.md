# Usage

## Manage layouts (admin)

Embed the builder in any Symfony form with `FormStructureType`. Its data is the structure (array of blocks).

```php
$form = $this->createFormBuilder($layout)
    ->add('title', TextType::class)
    ->add('content', FormStructureType::class, [
        'mapped' => false,
        'data' => $layout->getStructure(),
        'answers_count' => $answersCount,   // asks for confirmation before deleting fields that hold answers
    ])
    ->getForm();
```

**Create** — the layout has no fields yet:

```php
$layout->setStructure($form->get('content')->getData() ?? []);
$em->persist($layout); $em->flush();
```

**Update** — never call `setStructure()` on a layout that already has fields (it throws): it would recreate
fields and break existing answers. Merge in place instead:

```php
$structure = $form->get('content')->getData() ?? [];
$deleteRemovedValues($layout, $structure);   // optional, returns the number of deleted values
$syncStructure($layout, $structure);         // update existing fields, create new ones, remove missing ones
$em->flush();
```

`SyncFormLayoutStructureUseCase` and `DeleteRemovedFormLayoutFieldValuesUseCase` are autowirable services.
Removing a field also removes its values (foreign keys are `ON DELETE CASCADE`).

## Answer a form

```php
$form = $formTypeGenerator->buildForm($layout->getStructure());           // FormTypeGenerator
$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    $answer->setFormLayout($layout);
    $answer->setAnswerData($form->getData());                             // replaces all values
    $em->persist($answer); $em->flush();
}
```

Editing an existing answer: pass its data **and** the `edit` option. Without `edit`, every field's configured
default value (or "current date") overrides the data you pass.

```php
$form = $formTypeGenerator->buildForm($layout->getStructure(), $answer->getAnswerData(), ['edit' => true]);
```

Keys that match no field of the layout are ignored by `setAnswerData()`. Add your own fields to the
generated form (e.g. a reference) and strip them from the array before calling it.

`buildNamedForm($name, $structure, $data, $options)` builds several forms on one page.

## Display answers

```php
$answers = $answerGenerator->generate($layout->getStructure(), $answer->getAnswerData());   // AnswerGenerator
```

```twig
{{ form_builder_answers(answers, formAnswer) }}
{# PDF variant: uses form_builder.answers_pdf_template #}
{{ form_builder_answers(answers, formAnswer, true) }}
```

## Files and signatures

- `FileInput` rows are stored as `['file' => ['filename', 'originalFilename', 'extension']]`; the file lives in
  `form_builder.upload_path`.
- Deleting an answer entity removes its files (Doctrine `preRemove` listener, active automatically).
- Serve files from your own route and voter:

```php
#[Route('/answers/{id}/files/{filePath}', name: 'answer_file_download', requirements: ['filePath' => '.+'])]
public function download(string $filePath, FormAnswer $answer, FormFileUploader $uploader): Response
{
    $this->denyAccessUnlessGranted('VIEW', $answer);
    return new BinaryFileResponse($uploader->getFile($filePath) ?? throw $this->createNotFoundException());
}
```

- For PDF engines (wkhtmltopdf), Twig functions `formImageFileUri(filename)` (`file://` path) and
  `convertToBase64(filename)` are available.
- A signature is stored as a data URI (SVG) in the answer value.

## Duplicate a layout

Rebuild the structure in `__clone()` (your entity):

```php
public function __clone(): void
{
    $structure = $this->getStructure();
    $this->id = null;
    $this->initializeFormLayoutFields();
    $this->setStructure($structure);
}
```
