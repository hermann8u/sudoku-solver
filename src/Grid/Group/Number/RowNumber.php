<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group\Number;

use SudokuSolver\Comparable;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group\GroupNumber;

/**
 * @implements Comparable<RegionNumber>
 */
final readonly class RowNumber extends GroupNumber implements Comparable
{
    public static function fromCoordinates(Coordinates $coordinates): static
    {
        return new self($coordinates->y);
    }

    public function equals(Comparable $other): bool
    {
        return $this->value === $other->value;
    }
}
