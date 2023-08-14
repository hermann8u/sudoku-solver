<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\Grid\Cell\CellValue;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Cell\FixedValueCell;

final readonly class GridFactory
{
    /**
     * @param array<int<0, 8>, array<int<0, 8>, string>> $gridAsArray
     */
    public function create(array $gridAsArray): Grid
    {
        foreach ($gridAsArray as $y => $row) {
            foreach ($row as $x => $value) {
                $coordinates = new Coordinates($x + 1, $y + 1);

                if ($value === '') {
                    $cells[] = new FillableCell($coordinates);

                    continue;
                }

                $value = (int) $value;

                if ($value < CellValue::MIN || $value > CellValue::MAX) {
                    throw new \InvalidArgumentException();
                }

                $cells[] = new FixedValueCell($coordinates, CellValue::from($value));
            }
        }

        return new Grid($cells ?? []);
    }
}
