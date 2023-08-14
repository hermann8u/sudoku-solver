<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group\Number;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group\Number;
use SudokuSolver\Grid\Group\Region;

final readonly class RegionNumber extends Number
{
    public static function fromCell(Cell $cell): static
    {
        return self::fromCoordinates($cell->coordinates);
    }

    public static function fromCoordinates(Coordinates $coordinates): static
    {
        /** @var int<self::MIN, self::MAX> $number */
        $number = (int) (ceil($coordinates->x / Region::WIDTH) + (ceil($coordinates->y / Region::HEIGHT) - 1) * Region::HEIGHT);

        return new self($number);
    }

    public function is(RegionNumber $regionNumber): bool
    {
        return $this->value === $regionNumber->value;
    }
}
