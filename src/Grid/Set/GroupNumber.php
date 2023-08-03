<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Set;

use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

final readonly class GroupNumber
{
    public const MIN = 1;
    public const MAX = 9;

    public function __construct(
        public int $value,
    ) {
        Assert::greaterThanEq($this->value, self::MIN);
        Assert::lessThanEq($this->value, self::MAX);
    }

    public static function fromCoordinates(Coordinates $coordinates): self
    {
        $number = ceil($coordinates->x / Group::WIDTH) + (ceil($coordinates->y / Group::HEIGHT) - 1) * Group::HEIGHT;

        return new self((int) $number);
    }
}
