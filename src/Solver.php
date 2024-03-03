<?php

declare(strict_types=1);

namespace Sudoku;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method;
use Sudoku\Solver\Method\InclusiveMethod;
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
        private InclusiveMethod $initialMethod,
        private iterable $methods,
    ) {
    }

    /**
     * @param positive-int $stopAtStepNumber
     */
    public function solve(Grid $grid, int $stopAtStepNumber = self::MAX_ITERATION): Result
    {
        return new Result(
            $grid,
            ArrayList::fromIterable($this->getResolutionSteps($grid, $stopAtStepNumber)),
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

            dump('Step n°' . $i);

            [$candidatesByCell, $solution] = $this->getNextSolution($grid, $candidatesByCell ?? null);

            if (! $solution instanceof Solution) {
                return yield new Step($i, $candidatesByCell, null);
            }

            $grid = $grid->withUpdatedCell($solution->cell->withValue($solution->value));
            $candidatesByCell = $this->clearCandidatesByCellMapWithSolution($candidatesByCell, $solution);
            $shouldStop = $this->shouldStop($stopAtStepNumber, $i, $grid);

            yield new Step($i, $shouldStop ? $candidatesByCell : null, $solution);

        } while (! $shouldStop);
    }

    /**
     * @return array{Map<FillableCell, Candidates>, ?Solution}
     */
    public function getNextSolution(Grid $grid, ?Map $candidatesByCell = null): array
    {
        $candidatesByCell ??= Map::empty();

        $emptyCells = $grid->getEmptyCells();

        foreach ([$this->initialMethod, ...$this->methods] as $method) {
            foreach ($emptyCells as $currentCell) {
                $candidatesByCell = $method->apply($candidatesByCell, $grid, $currentCell);

                /**
                 * @var FillableCell $cell
                 * @var Candidates $candidates
                 */
                foreach ($candidatesByCell as $cell => $candidates) {
                    $candidatesCount = $candidates->count();

                    if ($candidatesCount > 1) {
                        continue;
                    }

                    return match ($candidatesCount) {
                        0 => [$candidatesByCell, null],
                        1 => [$candidatesByCell, new Solution($method::getName(), $cell, $candidates->first())],
                    };
                }
            }
        }

        return [$candidatesByCell, null];
    }

    /**
     * @param positive-int $stopAtStepNumber
     */
    private function shouldStop(int $stopAtStepNumber, int $iteration, Grid $grid): bool
    {
        if ($grid->containsDuplicate()) {
            return true;
        }

        if ($grid->isFilled()) {
            return true;
        }

        if ($iteration >= min($stopAtStepNumber, self::MAX_ITERATION)) {
            return true;
        }

        return false;
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return Map<FillableCell, Candidates>
     */
    private function clearCandidatesByCellMapWithSolution(Map $candidatesByCell, Solution $solution): Map
    {
        $candidatesByCell = $candidatesByCell->without($solution->cell);

        /**
         * @var FillableCell $currentCell
         * @var Candidates $candidates
         */
        foreach ($candidatesByCell as $currentCell => $candidates) {
            if (! $currentCell->hasCommonGroupWith($solution->cell)) {
                continue;
            }

            $candidatesByCell = $candidatesByCell->with(
                $currentCell,
                $candidatesByCell->get($currentCell)->withRemovedValues($solution->value),
            );
        }

        return $candidatesByCell;
    }
}
