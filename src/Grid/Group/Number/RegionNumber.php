<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group\Number;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group\GroupNumber;
use SudokuSolver\Grid\Group\Region;

/**
 * @extends GroupNumber<RegionNumber>
 */
final readonly class RegionNumber extends GroupNumber
{
    public static function fromCoordinates(Coordinates $coordinates): static
    {
        /** @var int<self::MIN, self::MAX> $number */
        $number = (int) (
            ceil($coordinates->x / Region::WIDTH)
            + (ceil($coordinates->y / Region::HEIGHT) - 1)
            * Region::HEIGHT
        );

        return new self($number);
    }
}
