<?php

namespace Gdbots\QueryParser\Enum;

use Gdbots\Common\Enum;

/**
 * @method static BoolOperator OPTIONAL()
 * @method static BoolOperator REQUIRED()
 * @method static BoolOperator PROHIBITED()
 */
final class BoolOperator extends Enum
{
    const OPTIONAL = 0;
    const REQUIRED = 1;
    const PROHIBITED = 2;
}
