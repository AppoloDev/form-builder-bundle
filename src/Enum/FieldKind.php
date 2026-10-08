<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Enum;

enum FieldKind: string
{
    /** No answer value of its own (Title, Paragraph). */
    case DisplayOnly = 'display_only';

    /** Groups other fields but holds no value itself (FieldSet). */
    case Container = 'container';

    /** Holds a list of repeated child value-sets (Repeatable). */
    case Repeatable = 'repeatable';

    /** Plain leaf field addressed directly by its own id. */
    case Value = 'value';
}
