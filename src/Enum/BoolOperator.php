<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Enum;

enum BoolOperator: int
{
    case OPTIONAL = 0;
    case REQUIRED = 1;
    case PROHIBITED = 2;
}
