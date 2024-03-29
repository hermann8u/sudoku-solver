<?php

declare(strict_types=1);

namespace Sudoku\Grid\Cell;

use Stringable;
use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Equable;
use Webmozart\Assert\Assert;

/**
 * @implements Equable<Coordinates>
 */
final readonly class Coordinates implements Equable, Stringable
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
     * @return ArrayList<Coordinates>
     */
    public static function all(): ArrayList
    {
        return ArrayList::fromList(range(0, 80))
            ->map(static function (int $number) {
                /** @var int<1, 9> $x */
                $x = $number % self::MAX + 1;

                /** @var int<1,9> $y */
                $y = (int) floor($number / self::MAX) + 1;

                return new self($x, $y);
            });
    }

    /**
     * @return int<-1, 1>
     */
    public function compare(Coordinates $coordinates): int
    {
        return $this->y . $this->x <=> $coordinates->y . $coordinates->x;
    }

    public function equals(Equable $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }

    public function toString(): string
    {
        return sprintf('(%d,%d)', $this->x, $this->y);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
