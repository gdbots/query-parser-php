<?php

namespace Gdbots\QueryParser\Enum;

use Gdbots\Common\Enum;

/**
 * @method static FilterType SIMPLE()
 * @method static FilterType RANGE()
 * @method static FilterType SUBQUERY()
 */
final class FilterType extends Enum
{
    const SIMPLE = 0;
    const RANGE = 1;
    const SUBQUERY = 2;
}
