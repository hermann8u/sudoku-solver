<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Cell;

use SudokuSolver\DataStructure\Comparable;
use Webmozart\Assert\Assert;

/**
 * @implements Comparable<Coordinates>
 */
final readonly class Coordinates implements Comparable, \Stringable
{
    public const MIN = 1;
    public const MAX = 9;

    /**
     * @param int<self::MIN, self::MAX> $x
     * @param int<self::MIN, self::MAX> $y
     */
    private function __construct(
        public int $x,
        public int $y,
    ) {
        Assert::greaterThanEq($this->x, self::MIN);
        Assert::lessThanEq($this->x, self::MAX);
        Assert::greaterThanEq($this->y, self::MIN);
        Assert::lessThanEq($this->y, self::MAX);
    }

    /**
     * @param int<self::MIN, self::MAX> $x
     * @param int<self::MIN, self::MAX> $y
     */
    public static function from(int $x, int $y): self
    {
        return new self($x, $y);
    }

    public static function fromString(string $coordinates): self
    {
        /**
         * @var int<self::MIN, self::MAX> $x
         * @var int<self::MIN, self::MAX> $y
         */
        [$x, $y] = explode(',', trim($coordinates, '()'));

        return self::from((int) $x, (int) $y);
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

    public function equals(Comparable $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }
}
