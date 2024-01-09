<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;


abstract readonly class GroupNumber implements \Stringable
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

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
