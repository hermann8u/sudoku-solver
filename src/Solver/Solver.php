<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;

final readonly class Solver
{
    /**
     * @param iterable<Method> $methods
     */
    public function __construct(
        private iterable $methods,
    ) {
    }

    public function solve(Grid $grid): Result
    {
        $i = 0;

        $methodNamesCount = [];
        $map = CellCandidatesMap::empty();

        do {
            foreach ($grid->getFillableCells() as $currentCell) {
                if ($currentCell->isEmpty() === false) {
                    continue;
                }

                foreach ($this->methods as $method) {
                    $map = $method->apply($map, $grid, $currentCell);

                    [$coordinates, $cellValue] = $map->findUniqueValue();

                    if ($coordinates === null || $cellValue === null) {
                        continue;
                    }

                    $cell = $grid->getCellByCoordinates($coordinates);
                    if (! $cell instanceof FillableCell) {
                        throw new \LogicException();
                    }

                    $cell->updateValue($cellValue);

                    $methodName = $method::class;
                    $methodNamesCount[$methodName] = ($methodNamesCount[$methodName] ?? 0) + 1;

                    if ($grid->containsDuplicate()) {
                        dump([$cell->coordinates->toString(), $methodName, $cellValue->value]);
                        break 3;
                    }

                    $map = CellCandidatesMap::empty();

                    break;
                }
            }
            $i++;
        } while (($grid->isValid() === false && $grid->containsDuplicate() === false && $i < 10));

        return new Result(
            $i,
            memory_get_peak_usage(true),
            $methodNamesCount,
            $grid,
        );
    }
}
