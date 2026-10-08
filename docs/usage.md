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
- Serve files from your own route and voter, and check that the file belongs to the answer (a voter on the answer
  alone would let anyone who can read answer A download the files of answer B):

```php
#[Route('/answers/{id}/files/{filePath}', name: 'answer_file_download', requirements: ['filePath' => '.+'])]
public function download(string $filePath, FormAnswer $answer, FormFileUploader $uploader, AnswerFiles $answerFiles): Response
{
    $this->denyAccessUnlessGranted('VIEW', $answer);
    $file = $answerFiles->owns($answer, $filePath) ? $uploader->getFile($filePath) : null;
    return new BinaryFileResponse($file ?? throw $this->createNotFoundException());
}
```

- For PDF engines (wkhtmltopdf), Twig functions `formImageFileUri(filename)` (`file://` path) and
  `convertToBase64(filename)` are available.
- A signature is stored as a data URI (`data:image/png|jpeg|svg+xml;base64,…`, max 1 MB); anything else is rejected
  by validation and never rendered as an image.

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
