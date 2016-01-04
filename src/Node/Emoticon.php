<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Enum\BoolOperator;

final class Emoticon extends Node
{
    const NODE_TYPE = 'emoticon';

    /**
     * Emoticon constructor.
     *
     * @param string $value
     * @param BoolOperator $boolOperator
     * @param bool $useBoost
     * @param float $boost
     */
    public function __construct(
        $value,
        BoolOperator $boolOperator = null,
        $useBoost = false,
        $boost = self::DEFAULT_BOOST
    ) {
        parent::__construct($value, $boolOperator, $useBoost, $boost);
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data = [])
    {
        $value    = isset($data['value']) ? $data['value'] : null;
        $useBoost = isset($data['use_boost']) ? (bool)$data['use_boost'] : false;
        $boost    = isset($data['boost']) ? (float)$data['boost'] : self::DEFAULT_BOOST;

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::create($data['bool_operator']) : null;
        } catch (\Exception $e) {
            $boolOperator = null;
        }

        return new self($value, $boolOperator, $useBoost, $boost);
    }
}
