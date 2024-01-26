<?php

declare(strict_types=1);

namespace Sudoku\Grid\Group\Number;

use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Group\GroupNumber;

/**
 * @extends GroupNumber<ColumnNumber>
 */
final readonly class ColumnNumber extends GroupNumber
{
    public static function fromCoordinates(Coordinates $coordinates): static
    {
        return new self($coordinates->x);
    }
}
