<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Cell\FixedValueCell;
use SudokuSolver\Grid\Cell\Value;

final readonly class GridFactory
{
    /**
     * @param array<int<0, 8>, array<int<0, 8>, string>> $gridAsArray
     */
    public function create(array $gridAsArray): Grid
    {
        /** @var Cell[] $cells */
        $cells = [];

        foreach ($gridAsArray as $y => $row) {
            foreach ($row as $x => $value) {
                $coordinates = Coordinates::from($x + 1, $y + 1);

                if ($value === '') {
                    $cells[] = new FillableCell($coordinates);

                    continue;
                }

                $value = (int) $value;

                if ($value < Value::MIN || $value > Value::MAX) {
                    throw new \InvalidArgumentException();
                }

                $cells[] = new FixedValueCell($coordinates, Value::from($value));
            }
        }

        return new Grid(ArrayList::fromList($cells));
    }
}
