<?php

declare(strict_types=1);

namespace Sudoku\Grid\Cell;

use Stringable;
use Sudoku\DataStructure\Comparable;
use Webmozart\Assert\Assert;

/**
 * @implements Comparable<Value>
 */
final readonly class Value implements Comparable, Stringable
{
    public const MIN = 1;
    public const MAX = 9;

    /**
     * @param int<self::MIN, self::MAX> $value
     */
    private function __construct(
        public int $value,
    ) {
        Assert::greaterThanEq($this->value, self::MIN);
        Assert::lessThanEq($this->value, self::MAX);
    }

    /**
     * @param int<self::MIN, self::MAX> $value
     */
    public static function from(int $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function equals(Comparable $other): bool
    {
        return $this->value === $other->value;
    }
}
