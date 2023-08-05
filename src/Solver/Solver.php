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

    public function solve(Grid $grid): void
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

                    if ($coordinates !== null && $cellValue !== null) {
                        $cell = $grid->getCellByCoordinates($coordinates);
                        if (! $cell instanceof FillableCell) {
                            throw new \LogicException();
                        }

                        $cell->updateValue($cellValue);

                        $methodName = $method::class;
                        $methodNamesCount[$methodName] = ($methodNamesCount[$methodName] ?? 0) +1;

                        if ($grid->containsDuplicate()) {
                            dump([$cell->coordinates->toString(), $methodName, $cellValue->value]);
                            break 3;
                        }

                        $map = CellCandidatesMap::empty();

                        break;
                    }
                }
            }
            $i++;
        } while (($grid->isValid() === false && $i < 10));

        dump([
            'map' => $map->display(),
            'iteration' => $i,
            'valid' => $grid->isValid(),
            'filled' => $grid->isFilled(),
            'contains_duplicate' => $grid->containsDuplicate(),
            'cell_to_fill' => count($grid->getFillableCells()),
            'cell_filled' => count(array_filter($grid->getFillableCells(), static fn (Cell $cell) => $cell->isEmpty() === false)),
            'remaining' => count(array_filter($grid->getFillableCells(), static fn (Cell $cell) => $cell->isEmpty())),
            'methods' => $methodNamesCount,
            'memory' => memory_get_peak_usage(),
            'real_memory' => (memory_get_peak_usage(true) / 1024 / 1024) . ' MiB',
        ]);
    }
}
