<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;

final class Date extends AbstractDatetime
{
    const NODE_TYPE = 'date';

    /**
     * Always returns a DateTime in UTC.  Use the time zone option to inform this class
     * that the value it holds is localized and should be converted to UTC.
     *
     * @param \DateTimeZone $timeZone
     * @return \DateTime
     */
    public function toDateTime(\DateTimeZone $timeZone = null)
    {
        if (null === self::$utc) {
            self::$utc = new \DateTimeZone('UTC');
        }

        $date = \DateTime::createFromFormat('!Y-m-d', $this->getValue(), $timeZone ?: self::$utc);
        if (!$date instanceof \DateTime) {
            $date = \DateTime::createFromFormat('!Y-m-d', (new \DateTime())->format('Y-m-d'), $timeZone ?: self::$utc);
        }

        if ($date->getOffset() !== 0) {
            $date->setTimezone(self::$utc);
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
