<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Result\Solution;
use SudokuSolver\Solver\Result\Step;
use Traversable;

final readonly class Solver
{
    private const MAX_ITERATION = 80;

    /**
     * @param iterable<Method> $methods
     */
    public function __construct(
        private iterable $methods,
    ) {
    }

    public function solve(Grid $grid): Result
    {
        return new Result(
            iterator_to_array($this->getResolutionSteps($grid)),
            $grid,
        );
    }

    /**
     * @param positive-int $stopAtStepNumber
     *
     * @return Traversable<Step>
     */
    public function getResolutionSteps(Grid $grid, int $stopAtStepNumber = self::MAX_ITERATION): Traversable
    {
        $i = 0;

        do {
            $i++;
            $solution = $this->getNextSolution($grid);

            if (! $solution instanceof Solution) {
                break;
            }

            $grid = $grid->withUpdatedCell($solution->cell->coordinates, $solution->value);

            yield Step::fromSolution($i, $solution);

        } while (! $this->shouldStop($stopAtStepNumber, $i, $grid));
    }

    public function getNextSolution(Grid $grid): ?Solution
    {
        $map = CellCandidatesMap::empty();

        foreach ($this->methods as $method) {
            foreach ($grid->getEmptyCells() as $currentCell) {
                $map = $method->apply($map, $grid, $currentCell);

                $uniqueValue = $map->findFirstUniqueCandidate();

                if ($uniqueValue === null) {
                    continue;
                }

                return new Solution($method::getName(), $map, ...$uniqueValue);
            }
        }

        return null;
    }

    /**
     * @param positive-int $stopAtStepNumber
     */
    private function shouldStop(int $stopAtStepNumber, int $iteration, Grid $grid): bool
    {
        if ($grid->containsDuplicate()) {
            return true;
        }

        if ($grid->isValid()) {
            return true;
        }

        if ($iteration >= min($stopAtStepNumber, self::MAX_ITERATION)) {
            return true;
        }

        return false;
    }
}
