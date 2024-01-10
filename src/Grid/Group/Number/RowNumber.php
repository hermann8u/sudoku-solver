<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group\Number;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group\GroupNumber;

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
