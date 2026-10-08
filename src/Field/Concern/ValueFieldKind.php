<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field\Concern;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;

trait ValueFieldKind
{
    public function getKind(): FieldKind
    {
        return FieldKind::Value;
    }
}
