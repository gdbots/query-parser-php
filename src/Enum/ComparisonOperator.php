<?php

namespace Gdbots\QueryParser\Enum;

use Gdbots\Common\Enum;

/**
 * @method static ComparisonOperator EQ()
 * @method static ComparisonOperator GT()
 * @method static ComparisonOperator GTE()
 * @method static ComparisonOperator LT()
 * @method static ComparisonOperator LTE()
 */
final class ComparisonOperator extends Enum
{
    const EQ = 'eq';
    const GT = 'gt';
    const GTE = 'gte';
    const LT = 'lt';
    const LTE = 'lte';
}
