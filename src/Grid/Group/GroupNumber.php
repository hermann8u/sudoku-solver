<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group;

use SudokuSolver\Comparable;
use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

/**
 * @template T of GroupNumber
 * @template-implements Comparable<T>
 */
abstract readonly class GroupNumber implements \Stringable, Comparable
{
    public const MIN = 1;
    public const MAX = 9;

    /**
     * @param int<self::MIN, self::MAX> $value
     */
    protected function __construct(
        public int $value,
    ) {
        Assert::greaterThanEq($this->value, self::MIN);
        Assert::lessThanEq($this->value, self::MAX);
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
