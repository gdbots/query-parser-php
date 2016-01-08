<?php

namespace Gdbots\QueryParser\Enum;

use Gdbots\Common\Enum;

/**
 * @method static FieldType SIMPLE()
 * @method static FieldType RANGE()
 * @method static FieldType SUBQUERY()
 */
final class FieldType extends Enum
{
    const SIMPLE = 0;
    const RANGE = 1;
    const SUBQUERY = 2;
}
