<?php

declare(strict_types=1);

namespace Sudoku;

use Sudoku\DataStructure\ArrayList;
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

    public function solve(Grid $grid, int $stopAtStepNumber = self::MAX_ITERATION): Result
    {
        $steps = iterator_to_array($this->getResolutionSteps($grid, $stopAtStepNumber));

        return new Result(
            $grid,
            ArrayList::fromList($steps),
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

            [$candidatesByCell, $solution] = $this->getNextSolution($grid);

            if (! $solution instanceof Solution) {
                yield new Step($i, $candidatesByCell, null);

                return;
            }

            $grid = $grid->withUpdatedCell($solution->cell->coordinates, $solution->value);

            yield new Step($i, $candidatesByCell, $solution);

        } while (! $this->shouldStop($stopAtStepNumber, $i, $grid));
    }

    /**
     * @return array{Map<FillableCell, Candidates>, ?Solution}
     */
    public function getNextSolution(Grid $grid): array
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
}
