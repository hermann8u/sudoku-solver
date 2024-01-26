<?php

declare(strict_types=1);

namespace Sudoku\Grid\Group\Number;

use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Group\GroupNumber;

/**
 * @extends GroupNumber<RowNumber>
 */
final readonly class RowNumber extends GroupNumber
{
    public static function fromCoordinates(Coordinates $coordinates): static
    {
        return new self($coordinates->y);
    }
}
