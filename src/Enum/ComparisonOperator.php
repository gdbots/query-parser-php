<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Enum;

/**
 * @method static ComparisonOperator EQ()
 * @method static ComparisonOperator GT()
 * @method static ComparisonOperator GTE()
 * @method static ComparisonOperator LT()
 * @method static ComparisonOperator LTE()
 */
final class ComparisonOperator extends AbstractEnum
{
    const EQ = 'eq';
    const GT = 'gt';
    const GTE = 'gte';
    const LT = 'lt';
    const LTE = 'lte';
}
