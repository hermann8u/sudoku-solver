<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method;

final readonly class DebugMethod implements Method
{
    public function __construct(
        private Method $inner,
    ) {
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        $previous = $map;

        $afterMap = $this->inner->apply($map, $grid, $currentCell);

        $previousDisplay = $previous->display();
        $afterDisplay = $afterMap->display();

        if ($previousDisplay !== $afterDisplay) {
            $display = [];
            foreach (array_keys($afterDisplay) as $key) {
                if (($afterDisplay[$key] ?? []) !== ($previousDisplay[$key] ?? [])) {
                    $display[$key] = [
                        'previous' => $previousDisplay[$key] ?? [],
                        'after' => $afterDisplay[$key] ?? [],
                    ];
                }
            }

            dump($display);
        }

        return $afterMap;
    }
}
