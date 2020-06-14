<?php
declare(strict_types=1);

/**
 * Combination of aws php sdk and myclabs for flyweight enums
 *
 * @link https://github.com/aws/aws-sdk-php/
 * @link http://github.com/myclabs/php-enum
 */

namespace Gdbots\QueryParser\Enum;

abstract class AbstractEnum implements \JsonSerializable
{
    /**
     * The value of the current enum.
     * @var int|string
     */
    protected $value;

    /**
     * Store existing constant values in a static cache per object.
     * @var array
     */
    private static array $values = [];

    /**
     * Only one instance will exist per enum class and enum value.
     * @var self[]
     */
    private static array $instances = [];

    /**
     * private constructor to ensure flyweight construction.
     *
     * @param int|string $value
     */
    final private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param string|int $value
     *
     * @return static
     * @throws \UnexpectedValueException
     */
    final public static function create($value): self
    {
        $key = static::class . $value;

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        $values = static::values();
        if (!in_array($value, $values)) {
            throw new \UnexpectedValueException("Value '$value' is not part of the enum " . static::class);
        }

        self::$instances[$key] = new static($value);
        return self::$instances[$key];
    }

    /**
     * Returns the name/key of the constant this enum value matches.  Note that this
     * may not return the exact constant name you'd expect if you have multiple
     * constants that share the same value.
     *
     * @return string
     */
    final public function getName(): string
    {
        return array_flip(static::values())[$this->value];
    }

    /**
     * @return int|string
     */
    final public function getValue()
    {
        return $this->value;
    }

    final public function __toString()
    {
        return (string)$this->value;
    }

    final public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * Returns the names (or keys) of all of constants in the enum
     *
     * @return string[]
     */
    final public static function keys(): array
    {
        return array_keys(static::values());
    }

    /**
     * Return the names and values of all the constants in the enum
     *
     * @return array
     */
    final public static function values(): array
    {
        $class = static::class;

        if (!isset(self::$values[$class])) {
            self::$values[$class] = (new \ReflectionClass($class))->getConstants();
        }

        return self::$values[$class];
    }

    /**
     * Compares this enum (which has a value) to the value provided.  If the value
     * is itself an Enum then we'll get the value of the provided enum and compare
     * it to our value.
     *
     * @param self|int|string $value
     *
     * @return bool
     */
    final public function equals($value): bool
    {
        if ($value instanceof AbstractEnum) {
            return $this->value == $value->getValue();
        }

        return $this->value == $value;
    }

    /**
     * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return static
     * @throws \BadMethodCallException
     */
    final public static function __callStatic($name, $arguments)
    {
        if (defined("static::$name")) {
            return static::create(constant("static::$name"));
        }

        throw new \BadMethodCallException("No static method or enum constant '$name' in class " . static::class);
    }
}
