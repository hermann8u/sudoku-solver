<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method;

final readonly class Inspector implements Method
{
    public function __construct(
        private Method $inner,
    ) {
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        $previous = $map;

        $afterMap = $this->inner->apply($map, $grid, $currentCell);

        $afterDisplay = $afterMap->display();

        if ($previous->display() !== $afterDisplay) {
            $display = [];
            foreach ($previous->display() as $key => $previousRow) {
                if ($afterDisplay[$key] !== $previousRow) {
                    $display[$key] = [
                        'previous' => $previousRow,
                        'after' => $afterDisplay[$key],
                    ];
                }
            }

            dump($display);
        }

        return $afterMap;
    }
}
