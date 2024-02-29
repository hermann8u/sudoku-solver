<?php

declare(strict_types=1);

namespace Sudoku\Grid\Group;

use Stringable;
use Sudoku\DataStructure\Comparable;
use Sudoku\Grid\Cell;
use Sudoku\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

/**
 * @template T of GroupNumber
 * @template-implements Comparable<T>
 */
abstract readonly class GroupNumber implements Stringable, Comparable
{
    public const MIN = 1;
    public const MAX = 9;

    /**
     * @param int<self::MIN, self::MAX> $value
     */
    final protected function __construct(
        public int $value,
    ) {
        Assert::greaterThanEq($this->value, self::MIN);
        Assert::lessThanEq($this->value, self::MAX);
    }

    /**
     * @param int<self::MIN, self::MAX> $value
     */
    public static function from(int $value): static
    {
        return new static($value);
    }

    public static function fromCell(Cell $cell): static
    {
        return static::fromCoordinates($cell->coordinates);
    }

    abstract public static function fromCoordinates(Coordinates $coordinates): static;

    /**
     * @param T $other
     */
    public function equals(Comparable $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
