<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group\Number;

use SudokuSolver\Comparable;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group\GroupNumber;
use SudokuSolver\Grid\Group\Region;

/**
 * @implements Comparable<RegionNumber>
 */
final readonly class RegionNumber extends GroupNumber implements Comparable
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

    public function equals(Comparable $other): bool
    {
        return $this->value === $other->value;
    }
}
