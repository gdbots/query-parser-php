<?php
declare(strict_types=1);

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;
use Gdbots\QueryParser\Enum\BoolOperator;

final class Word extends Node
{
    const NODE_TYPE = 'word';
    const SUPPORTS_FUZZY = true;

    /** @var array */
    public static $stopWords = [
        'a', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by', 'for', 'if', 'in', 'into', 'is',
        'it', 'no', 'not', 'of', 'on', 'or', 'such', 'that', 'the', 'their', 'then', 'there',
        'these', 'they', 'this', 'to', 'was', 'will', 'with',
    ];

    /** @var bool */
    private $trailingWildcard = false;

    /**
     * Word constructor.
     *
     * @param string       $value
     * @param BoolOperator $boolOperator
     * @param bool         $useBoost
     * @param float        $boost
     * @param bool         $useFuzzy
     * @param int          $fuzzy
     * @param bool         $trailingWildcard
     */
    public function __construct(
        string $value,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST,
        bool $useFuzzy = false,
        int $fuzzy = self::DEFAULT_FUZZY,
        bool $trailingWildcard = false
    ) {
        parent::__construct($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy);
        $this->trailingWildcard = $trailingWildcard;
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data = [])
    {
        $value = isset($data['value']) ? $data['value'] : '';
        $useBoost = isset($data['use_boost']) ? (bool)$data['use_boost'] : false;
        $boost = isset($data['boost']) ? (float)$data['boost'] : self::DEFAULT_BOOST;
        $useFuzzy = isset($data['use_fuzzy']) ? (bool)$data['use_fuzzy'] : false;
        $fuzzy = isset($data['fuzzy']) ? (int)$data['fuzzy'] : self::DEFAULT_FUZZY;
        $trailingWildcard = isset($data['trailing_wildcard']) ? (bool)$data['trailing_wildcard'] : false;

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::create($data['bool_operator']) : null;
        } catch (\Exception $e) {
            $boolOperator = null;
        }

        return new self($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy, $trailingWildcard);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        if (!$this->trailingWildcard) {
            return $array;
        }

        $array['trailing_wildcard'] = $this->trailingWildcard;
        return $array;
    }

    /**
     * @return bool
     */
    public function hasTrailingWildcard(): bool
    {
        return $this->trailingWildcard;
    }

    /**
     * @return bool
     */
    public function isStopWord(): bool
    {
        return in_array(strtolower($this->getValue()), self::$stopWords);
    }

    /**
     * @param QueryBuilder $builder
     */
    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addWord($this);
    }
}
