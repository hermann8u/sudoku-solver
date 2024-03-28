<?php

declare(strict_types=1);

namespace Sudoku;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\BackTrackingSolver;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method;
use Sudoku\Solver\Method\InclusiveMethod;
use Sudoku\Solver\NoSolutionException;
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
        private ?BackTrackingSolver $backTrackingSolver = null,
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

            try {
                [$candidatesByCell, $solution] = $this->getNextSolution($grid, $candidatesByCell ?? null);
            } catch (NoSolutionException $e) {
                return yield Step::fromNoSolution($e->candidatesByCell);
            }

            if ($solution === null) {
                return $this->backTrackingSolver
                    ? yield from $this->backTrackingSolver->getResolutionSteps($grid, $candidatesByCell)
                    : yield Step::fromNoSolution($candidatesByCell);
            }

            $grid = $grid->withUpdatedCell($solution->updatedCell);
            $candidatesByCell = $this->updateCandidatesFromSolution($candidatesByCell, $solution);
            $shouldStop = $this->shouldStop($stopAtStepNumber, $i, $grid);

            yield Step::fromSolution($solution, $shouldStop ? $candidatesByCell : null);

        } while (! $shouldStop);
    }

    /**
     * @return array{Map<FillableCell, Candidates>, ?Solution}
     *
     * @throws NoSolutionException
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

                    if ($candidatesCount === 0) {
                        throw new NoSolutionException($candidatesByCell);
                    }

                    if ($candidatesCount > 1) {
                        continue;
                    }

                    return [$candidatesByCell, new Solution($method::getName(), $cell, $candidates->first())];
                }
            }
        }

        return [$candidatesByCell, null];
    }

    public function withBackTracking(BackTrackingSolver $backTracking): self
    {
        return new self($this->initialMethod, $this->methods, $backTracking);
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
    private function updateCandidatesFromSolution(Map $candidatesByCell, Solution $solution): Map
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
