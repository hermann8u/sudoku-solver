<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group\Number;

use SudokuSolver\Comparable;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group\GroupNumber;

/**
 * @implements Comparable<ColumnNumber>
 */
final readonly class ColumnNumber extends GroupNumber implements Comparable
{
    public static function fromCoordinates(Coordinates $coordinates): static
    {
        return new self($coordinates->x);
    }

    public function equals(Comparable $other): bool
    {
        return $this->value === $other->value;
    }
}
