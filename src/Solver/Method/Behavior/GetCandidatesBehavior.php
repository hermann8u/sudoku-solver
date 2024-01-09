<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method\Behavior;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CandidatesProvider;
use SudokuSolver\Solver\CellCandidatesMap;

trait GetCandidatesBehavior
{
    private readonly CandidatesProvider $candidatesProvider;

    /**
     * @return array{CellCandidatesMap, Candidates}
     */
    private function getCandidates(CellCandidatesMap $map, Grid $grid, FillableCell $cell): array
    {
        if (! $map->has($cell)) {
            $map = $map->with($cell, $this->candidatesProvider->getCandidates($grid, $cell));
        }

        return [$map, $map->get($cell)];
    }
}
