<?php

declare(strict_types=1);

namespace Sudoku;

use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method;
use Sudoku\Solver\Result;
use Sudoku\Solver\Result\Solution;
use Sudoku\Solver\Result\Step;
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
        $candidatesByCell = Map::empty();

        foreach ($this->methods as $method) {
            foreach ($grid->getEmptyCells() as $currentCell) {
                $candidatesByCell = $method->apply($candidatesByCell, $grid, $currentCell);

                /**
                 * @var FillableCell $cell
                 * @var Candidates $candidates
                 */
                foreach ($candidatesByCell as $cell => $candidates) {
                    if ($candidates->count() === 1) {
                        return new Solution($method::getName(), $candidatesByCell, $cell, $candidates->first());
                    }
                }
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
