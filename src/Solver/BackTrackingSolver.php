<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver;
use Sudoku\Solver\Result\Solution;
use Sudoku\Solver\Result\Step;

final readonly class BackTrackingSolver
{
    private Solver $solver;

    public function __construct(Solver $solver)
    {
        $this->solver = $solver->withBackTracking($this);
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return iterable<Step>
     */
    public function getResolutionSteps(Grid $grid, Map $candidatesByCell): iterable
    {
        $currentCell = $this->getCurrentCell($candidatesByCell, $grid);

        $candidates = $candidatesByCell->get($currentCell);
        $value = $candidates->first();

        $result = $this->solver->solve($grid->withUpdatedCell($currentCell->withValue($value)));

        if ($result->hasNoSolution()) {
            $candidates = $candidates->withRemovedValues($value);
            $candidatesByCell = $candidatesByCell->with($currentCell, $candidates);

            if ($candidates->count() === 0) {
                return yield Step::fromNoSolution($candidatesByCell);
            }

            return yield from $this->getResolutionSteps($grid, $candidatesByCell);
        }

        yield Step::fromSolution(new Solution('back_tracking', $currentCell, $value));

        return yield from $result->steps;
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     */
    private function getCurrentCell(Map $candidatesByCell, Grid $grid): FillableCell
    {
        $lowerCandidatesCount = $candidatesByCell->values()->reduce(
            static fn (int $carry, Candidates $candidates) => min($carry, $candidates->count()),
            9,
        );

        return $grid->getEmptyCells()->findFirst(static fn (FillableCell $cell) => $candidatesByCell->get($cell)->count() === $lowerCandidatesCount)
            ?? throw new \LogicException();
    }
}
