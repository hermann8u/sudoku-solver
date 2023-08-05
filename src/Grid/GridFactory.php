<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid;

use Florian\SudokuSolver\Grid\Cell\CellValue;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Cell\FixedValueCell;

final class GridFactory
{
    /**
     * @param array<int<0, 8>, array<int<0, 8>, string>> $gridAsArray
     */
    public function create(array $gridAsArray): Grid
    {
        foreach ($gridAsArray as $y => $row) {
            foreach ($row as $x => $value) {
                $coordinates = new Coordinates($x + 1, $y + 1);

                $cells[] = $value === ''
                    ? new FillableCell($coordinates)
                    : new FixedValueCell($coordinates, CellValue::from((int) $value));
            }
        }

        return new Grid($cells ?? []);
    }
}
