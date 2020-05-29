<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Enum;

/**
 * @method static BoolOperator OPTIONAL()
 * @method static BoolOperator REQUIRED()
 * @method static BoolOperator PROHIBITED()
 */
final class BoolOperator extends AbstractEnum
{
    const OPTIONAL = 0;
    const REQUIRED = 1;
    const PROHIBITED = 2;
}
