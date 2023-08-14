<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Result\Step;

final readonly class Solver
{
    private const MAX_ITERATION = 500;

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

        $steps = [];
        $map = CellCandidatesMap::empty();

        do {
            $i++;

            $previousMap = $map;

            foreach ($this->methods as $method) {
                foreach ($grid->getFillableCells() as $currentCell) {
                    if ($currentCell->isEmpty() === false) {
                        continue;
                    }

                    $map = $method->apply($map, $grid, $currentCell);

                    [$coordinates, $cellValue] = $map->findUniqueValue();

                    if ($coordinates === null || $cellValue === null) {
                        continue;
                    }

                    $cell = $grid->getCell($coordinates);
                    if (! $cell instanceof FillableCell) {
                        throw new \LogicException();
                    }

                    $grid = $grid->withUpdatedCell($coordinates, $cellValue);

                    if ($grid->containsDuplicate()) {
                        dump([$cell->coordinates->toString(), $method::getName(), $cellValue->value, $map->display()]);
                        break 3;
                    }

                    $map = CellCandidatesMap::empty();

                    $steps[] = new Step(
                        count($steps) + 1,
                        $method::getName(),
                        $coordinates,
                        $cellValue,
                    );

                    break;
                }
            }
        } while (false === $this->shouldStop($i, $grid, $previousMap, $map));

        return new Result(
            $i,
            memory_get_peak_usage(),
            $steps,
            $map,
            $grid,
        );
    }

    private function shouldStop(int $iteration, Grid $grid, CellCandidatesMap $previousMap, CellCandidatesMap $currentMap): bool
    {
        if ($grid->containsDuplicate()) {
            return true;
        }

        if ($grid->isValid()) {
            return true;
        }

        if (! $previousMap->isEmpty() && ! $currentMap->isEmpty() && $currentMap->isSame($previousMap)) {
            return true;
        }

        if ($iteration > self::MAX_ITERATION) {
            return true;
        }

        return false;
    }
}
