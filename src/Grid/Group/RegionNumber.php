<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Group;

use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

final readonly class RegionNumber
{
    public const MIN = 1;
    public const MAX = 9;

    /**
     * @param int<self::MIN, self::MAX> $value
     */
    public function __construct(
        public int $value,
    ) {
        Assert::greaterThanEq($this->value, self::MIN);
        Assert::lessThanEq($this->value, self::MAX);
    }

    public static function fromCoordinates(Coordinates $coordinates): self
    {
        /** @var int<self::MIN, self::MAX> $number */
        $number = (int) (ceil($coordinates->x / Region::WIDTH) + (ceil($coordinates->y / Region::HEIGHT) - 1) * Region::HEIGHT);

        return new self($number);
    }
}
