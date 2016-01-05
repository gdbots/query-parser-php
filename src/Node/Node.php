<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\Common\FromArray;
use Gdbots\Common\ToArray;
use Gdbots\Common\Util\NumberUtils;
use Gdbots\Common\Util\StringUtils;
use Gdbots\QueryParser\Enum\BoolOperator;

abstract class Node implements FromArray, ToArray, \JsonSerializable
{
    const NODE_TYPE = 'node';

    // "^" BOOST refers to scoring parts of the query.
    const SUPPORTS_BOOST = true;
    const DEFAULT_BOOST = 1.0;
    const MIN_BOOST = 0.0;
    const MAX_BOOST = 10.0;

    // "~" FUZZY refers to fuzzy matching terms and proximity on phrases and distance
    const SUPPORTS_FUZZY = false;
    const DEFAULT_FUZZY = 1;
    const MIN_FUZZY = 1;
    const MAX_FUZZY = 2;

    /** @var mixed */
    private $value = null;

    /** @var BoolOperator */
    private $boolOperator;

    /** @var bool */
    private $useBoost = false;

    /** @var float */
    private $boost = self::DEFAULT_BOOST;

    /** @var bool */
    private $useFuzzy = false;

    /** @var int */
    private $fuzzy = self::DEFAULT_FUZZY;

    /**
     * Node constructor.
     *
     * @param mixed $value
     * @param BoolOperator $boolOperator
     * @param bool $useBoost
     * @param float $boost
     * @param bool $useFuzzy
     * @param int $fuzzy
     */
    public function __construct(
        $value,
        BoolOperator $boolOperator = null,
        $useBoost = false,
        $boost = self::DEFAULT_BOOST,
        $useFuzzy = false,
        $fuzzy = self::DEFAULT_FUZZY
    ) {
        $this->value = $value;
        $this->boolOperator = $boolOperator ?: BoolOperator::OPTIONAL();

        $this->useBoost = (bool)$useBoost && static::SUPPORTS_BOOST;
        if ($this->useBoost) {
            $this->boost = (float)$boost;
            if ($this->boost < static::MIN_BOOST) {
                $this->boost = static::MIN_BOOST;
            }

            if ($this->boost > static::MAX_BOOST) {
                $this->boost = static::MAX_BOOST;
            }
        }

        $this->useFuzzy = (bool)$useFuzzy && static::SUPPORTS_FUZZY;
        if ($this->useFuzzy) {
            $this->fuzzy = NumberUtils::bound($fuzzy, static::MIN_FUZZY, static::MAX_FUZZY);
        }
    }

    /**
     * @param array $data
     * @return static
     * @throws \InvalidArgumentException
     */
    public static function factory(array $data = [])
    {
        /** @var Node $class */
        $class = 'Gdbots\QueryParser\Node\\' . StringUtils::toCamelFromSnake($data['type']);
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Node type [%s] does not exist.', $data['type']));
        }

        return $class::fromArray($data);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = ['type' => static::NODE_TYPE];

        if (null !== $this->value) {
            $array['value'] = $this->value;
        }

        if (!$this->isOptional()) {
            $array['bool_operator'] = $this->boolOperator;
        }

        if ($this->useBoost) {
            $array['use_boost'] = $this->useBoost;
            $array['boost'] = $this->boost;
        }

        if ($this->useFuzzy) {
            $array['use_fuzzy'] = $this->useFuzzy;
            $array['fuzzy'] = $this->fuzzy;
        }

        return $array;
    }

    /**
     * @return array
     */
    final public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return bool
     */
    final public function hasValue()
    {
        return null !== $this->value;
    }

    /**
     * @return mixed|null
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * @return BoolOperator
     */
    final public function getBoolOperator()
    {
        return $this->boolOperator;
    }

    /**
     * @return bool
     */
    final public function isOptional()
    {
        return $this->boolOperator->equals(BoolOperator::OPTIONAL());
    }

    /**
     * @return bool
     */
    final public function isRequired()
    {
        return $this->boolOperator->equals(BoolOperator::REQUIRED());
    }

    /**
     * @return bool
     */
    final public function isProhibited()
    {
        return $this->boolOperator->equals(BoolOperator::PROHIBITED());
    }

    /**
     * @return bool
     */
    final public function useBoost()
    {
        return $this->useBoost;
    }

    /**
     * @return float
     */
    final public function getBoost()
    {
        return $this->boost;
    }

    /**
     * @return bool
     */
    final public function useFuzzy()
    {
        return $this->useFuzzy;
    }

    /**
     * @return int
     */
    final public function getFuzzy()
    {
        return $this->fuzzy;
    }
}
