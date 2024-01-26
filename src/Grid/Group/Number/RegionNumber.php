<?php

declare(strict_types=1);

namespace Sudoku\Grid\Group\Number;

use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Group\GroupNumber;
use Sudoku\Grid\Group\Region;

/**
 * @extends GroupNumber<RegionNumber>
 */
final readonly class RegionNumber extends GroupNumber
{
    public static function fromCoordinates(Coordinates $coordinates): static
    {
        /** @var int<self::MIN, self::MAX> $number */
        $number = (int) (
            (ceil($coordinates->y / Region::HEIGHT) - 1) * Region::HEIGHT
            + ceil($coordinates->x / Region::WIDTH)
        );

        return new self($number);
    }
}
