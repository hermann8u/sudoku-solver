<?php

declare(strict_types=1);

namespace Sudoku\Grid\Factory;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid;
use Sudoku\Grid\Cell;
use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\FixedValueCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\GridFactory;

/**
 * @implements GridFactory<array<int<0,8>, array<int<0,8>, ?int<Value::MIN, Value::MAX>>>>
 */
final readonly class ArrayGridFactory implements GridFactory
{
    public function create(mixed $data): Grid
    {
        /** @var Cell[] $cells */
        $cells = [];

        foreach ($data as $y => $row) {
            foreach ($row as $x => $value) {
                $coordinates = Coordinates::from($x + 1, $y + 1);

                $cells[] = \is_int($value)
                    ? new FixedValueCell($coordinates, Value::from($value))
                    : new FillableCell($coordinates);
            }
        }

        return new Grid(ArrayList::fromList($cells));
    }
}
