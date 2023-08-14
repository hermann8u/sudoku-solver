<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Cell;

use Webmozart\Assert\Assert;

final readonly class Value implements \Stringable
{
    public const MIN = 1;
    public const MAX = 9;

    /**
     * @param int<self::MIN, self::MAX>|null $value
     */
    private function __construct(
        public ?int $value,
    ) {
        if ($this->value === null) {
            return;
        }

        Assert::greaterThanEq($this->value, self::MIN);
        Assert::lessThanEq($this->value, self::MAX);
    }

    public static function empty(): self
    {
        return new self(null);
    }

    /**
     * @param int<self::MIN, self::MAX> $value
     */
    public static function from(int $value): self
    {
        return new self($value);
    }

    public function isEmpty(): bool
    {
        return $this->value === null;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
