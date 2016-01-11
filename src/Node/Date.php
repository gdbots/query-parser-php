<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;
use Gdbots\QueryParser\Enum\ComparisonOperator;

final class Date extends Node
{
    const NODE_TYPE = 'date';

    // todo: enable "fuzzy" aka "proximity" for date fields?  technically a range accomplishes this already.
    /*
    const SUPPORTS_FUZZY = true;
    const MAX_FUZZY = 5;
    */

    /** @var \DateTimeZone */
    private static $utc;

    /** @var ComparisonOperator */
    private $comparisonOperator;

    /**
     * Date constructor.
     *
     * @param string $value
     * @param BoolOperator $boolOperator
     * @param bool $useBoost
     * @param float $boost
     * @param bool $useFuzzy
     * @param int $fuzzy
     * @param ComparisonOperator $comparisonOperator
     */
    public function __construct(
        $value,
        BoolOperator $boolOperator = null,
        $useBoost = false,
        $boost = self::DEFAULT_BOOST,
        $useFuzzy = false,
        $fuzzy = self::DEFAULT_FUZZY,
        ComparisonOperator $comparisonOperator = null
    ) {
        parent::__construct($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy);
        $this->comparisonOperator = $comparisonOperator ?: ComparisonOperator::EQ();
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
        $useFuzzy = isset($data['use_fuzzy']) ? (bool)$data['use_fuzzy'] : false;
        $fuzzy    = isset($data['fuzzy']) ? (int)$data['fuzzy'] : self::DEFAULT_FUZZY;

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::create($data['bool_operator']) : null;
        } catch (\Exception $e) {
            $boolOperator = null;
        }

        try {
            $comparisonOperator = isset($data['comparison_operator']) ? ComparisonOperator::create($data['comparison_operator']) : null;
        } catch (\Exception $e) {
            $comparisonOperator = null;
        }

        return new self($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy, $comparisonOperator);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        if ($this->comparisonOperator->equals(ComparisonOperator::EQ())) {
            return $array;
        }

        $array['comparison_operator'] = $this->comparisonOperator;
        return $array;
    }

    /**
     * @return bool
     */
    public function useComparisonOperator()
    {
        return !$this->comparisonOperator->equals(ComparisonOperator::EQ());
    }

    /**
     * @return ComparisonOperator
     */
    public function getComparisonOperator()
    {
        return $this->comparisonOperator;
    }

    /**
     * @return \DateTime
     */
    public function toDateTime()
    {
        if (null === self::$utc) {
            self::$utc = new \DateTimeZone('UTC');
        }

        $date = \DateTime::createFromFormat('!Y-m-d', $this->getValue(), self::$utc);
        if (!$date instanceof \DateTime) {
            $date = \DateTime::createFromFormat('!Y-m-d', (new \DateTime())->format('Y-m-d'), self::$utc);
        }

        return $date;
    }

    /**
     * @param QueryBuilder $builder
     */
    public function acceptBuilder(QueryBuilder $builder)
    {
        $builder->addDate($this);
    }
}
