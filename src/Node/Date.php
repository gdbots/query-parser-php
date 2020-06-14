<?php
declare(strict_types=1);

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

    private static ?\DateTimeZone $utc = null;
    private ComparisonOperator $comparisonOperator;

    public function __construct(
        string $value,
        ?BoolOperator $boolOperator = null,
        bool $useBoost = false,
        float $boost = self::DEFAULT_BOOST,
        bool $useFuzzy = false,
        int $fuzzy = self::DEFAULT_FUZZY,
        ?ComparisonOperator $comparisonOperator = null
    ) {
        parent::__construct($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy);
        $this->comparisonOperator = $comparisonOperator ?: ComparisonOperator::EQ();
    }

    public static function fromArray(array $data = []): self
    {
        $value = $data['value'] ?? '';
        $useBoost = (bool)($data['use_boost'] ?? false);
        $boost = (float)($data['boost'] ?? self::DEFAULT_BOOST);
        $useFuzzy = (bool)($data['use_fuzzy'] ?? false);
        $fuzzy = (int)($data['fuzzy'] ?? self::DEFAULT_FUZZY);

        try {
            $boolOperator = isset($data['bool_operator']) ? BoolOperator::create($data['bool_operator']) : null;
        } catch (\Throwable $e) {
            $boolOperator = null;
        }

        try {
            $comparisonOperator = isset($data['comparison_operator']) ? ComparisonOperator::create($data['comparison_operator']) : null;
        } catch (\Throwable $e) {
            $comparisonOperator = null;
        }

        return new self($value, $boolOperator, $useBoost, $boost, $useFuzzy, $fuzzy, $comparisonOperator);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        if ($this->comparisonOperator->equals(ComparisonOperator::EQ())) {
            return $array;
        }

        $array['comparison_operator'] = $this->comparisonOperator;
        return $array;
    }

    public function useComparisonOperator(): bool
    {
        return !$this->comparisonOperator->equals(ComparisonOperator::EQ());
    }

    public function getComparisonOperator(): ComparisonOperator
    {
        return $this->comparisonOperator;
    }

    /**
     * Always returns a DateTime in UTC.  Use the time zone option to inform this class
     * that the value it holds is localized and should be converted to UTC.
     *
     * @param \DateTimeZone $timeZone
     *
     * @return \DateTimeInterface
     */
    public function toDateTime(?\DateTimeZone $timeZone = null): \DateTimeInterface
    {
        if (null === self::$utc) {
            self::$utc = new \DateTimeZone('UTC');
        }

        $date = \DateTime::createFromFormat('!Y-m-d', $this->getValue(), $timeZone ?: self::$utc);
        if (!$date instanceof \DateTimeInterface) {
            $date = \DateTime::createFromFormat('!Y-m-d', (new \DateTime())->format('Y-m-d'), $timeZone ?: self::$utc);
        }

        if ($date->getOffset() !== 0) {
            $date->setTimezone(self::$utc);
        }

        return $date;
    }

    public function acceptBuilder(QueryBuilder $builder): void
    {
        $builder->addDate($this);
    }
}
