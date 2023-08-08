<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;

final readonly class Solver
{
    private const MAX_ITERATION = 20;

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
            $previousMap = $map;

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
                    $map = CellCandidatesMap::empty();

                    $methodName = $method::class;
                    $methodNamesCount[$methodName] = ($methodNamesCount[$methodName] ?? 0) + 1;

                    if ($grid->containsDuplicate()) {
                        dump([$cell->coordinates->toString(), $methodName, $cellValue->value]);
                        break 3;
                    }

                    break;
                }
            }

            $i++;
        } while (false === $this->shouldStop($i, $grid, $previousMap, $map));

        //dump(array_filter($map->display(), static fn (string $v) => strlen($v) === 3/*in_array($v, ['4,6', '6,8', '4,8'])*/));

        return new Result(
            $i,
            memory_get_peak_usage(),
            $methodNamesCount,
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

        if ($iteration > (self::MAX_ITERATION - 1)) {
            return true;
        }

        return false;
    }
}
