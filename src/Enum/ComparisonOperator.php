<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Enum;

enum ComparisonOperator: string
{
    case EQ = 'eq';
    case GT = 'gt';
    case GTE = 'gte';
    case LT = 'lt';
    case LTE = 'lte';
}
