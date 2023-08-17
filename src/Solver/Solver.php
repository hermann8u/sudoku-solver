<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Result\Step;

final readonly class Solver
{
    private const MAX_ITERATION = 100;

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

            [$map, $solution] = $this->getNextSolution($map, $grid);

            if ($solution === null) {
                // Continue here to try to reapply some methods with the updated map
                continue;
            }

            [$method, $coordinates, $value] = $solution;

            $grid = $grid->withUpdatedCell($coordinates, $value);
            $map = CellCandidatesMap::empty();

            $steps[] = new Step(
                count($steps) + 1,
                $method,
                $coordinates,
                $value,
            );
        } while (false === $this->shouldStop($i, $grid, $previousMap, $map));

        return new Result(
            $i,
            memory_get_peak_usage(),
            $steps,
            $map,
            $grid,
        );
    }

    /**
     * @return array{CellCandidatesMap, ?array{string, Coordinates, Value}}
     */
    private function getNextSolution(CellCandidatesMap $map, Grid $grid): array
    {
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

                return [$map, [$method::getName(), $coordinates, $cellValue]];
            }
        }

        return [$map, null];
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

        if ($iteration >= self::MAX_ITERATION) {
            return true;
        }

        return false;
    }
}
