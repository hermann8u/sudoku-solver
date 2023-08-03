<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Cell;

use Webmozart\Assert\Assert;

final readonly class Coordinates implements \Stringable
{

    public const MIN = 1;
    public const MAX = 9;

    public function __construct(
        public int $x,
        public int $y,
    ) {
        Assert::greaterThanEq($this->x, self::MIN);
        Assert::lessThanEq($this->x, self::MAX);
        Assert::greaterThanEq($this->y, self::MIN);
        Assert::lessThanEq($this->y, self::MAX);
    }

    /**
     * @return int<-1, 1>
     */
    public function compare(Coordinates $coordinates): int
    {
        return $this->y . $this->x <=> $coordinates->y . $coordinates->x;
    }

    public function toString(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return sprintf('(%d,%d)', $this->x, $this->y);
    }
}
