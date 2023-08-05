<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
use Florian\SudokuSolver\Grid\Set;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method;
use Florian\SudokuSolver\Solver\Pair;

final readonly class FilterPairMethod implements Method
{
    public function __construct(
        private ObviousCandidateMethod $obviousCandidateMethod,
    ) {
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        $sets = $grid->getSetsOfCell($currentCell);

        foreach ($sets as $set) {
            [$map, $pairs] = $this->findPairs($map, $grid, $set);

            foreach ($pairs as $pair) {
                foreach ($pair->coordinatesPair as $coordinates) {
                    $cell = $grid->getCellByCoordinates($coordinates);

                    if (! $cell->isEmpty() || ! $cell instanceof FillableCell) {
                        throw new \LogicException();
                    }

                    $map = $map->merge($cell, $pair->candidates);
                }
            }
        }

        // Force to recalculate candidates for current cell
        $map = $map->without($currentCell);

        return $this->obviousCandidateMethod->apply($map, $grid, $currentCell);
    }

    /**
     * @return array{CellCandidatesMap, Pair[]}
     */
    private function findPairs(CellCandidatesMap $map, Grid $grid, Set $set): array
    {
        $candidateCoordinatesMap = array_fill(1, 9, []);

        foreach ($set->getEmptyCells() as $cell) {
            [$map, $candidates] = $this->getCandidates($grid, $cell, $map);

            foreach ($candidates as $candidate) {
                $candidateCoordinatesMap[$candidate->value][] = $cell->coordinates->toString();
            }
        }

        // Filter candidates with more or less than 2 possible cells
        /** @var array<int, string[]> $candidateCoordinatesMap */
        $candidateCoordinatesMap = array_filter($candidateCoordinatesMap, static fn (array $items) => count($items) === 2);

        // No pairs identified
        if (count($candidateCoordinatesMap) < 2) {
            return [$map, []];
        }

        // Associate pairs
        foreach ($candidateCoordinatesMap as $v1 => $coordinatesSet) {
            foreach ($candidateCoordinatesMap as $v2 => $otherCoordinatesSet) {
                if ($v1 === $v2) {
                    continue;
                }

                if ($otherCoordinatesSet === $coordinatesSet) {
                    $pairCoordinates = implode(',', $coordinatesSet);

                    if (isset($pairs[$pairCoordinates])) {
                        continue;
                    }

                    $pairs[$pairCoordinates] = new Pair(
                        array_map(static fn (string $coordinates) => Coordinates::fromString($coordinates), $coordinatesSet),
                        Candidates::fromInt($v1, $v2),
                    );
                }
            }
        }

        return [$map, $pairs ?? []];
    }

    /**
     * @return array{CellCandidatesMap, Candidates}
     */
    private function getCandidates(Grid $grid, FillableCell $cell, CellCandidatesMap $map): array
    {
        $map = $this->obviousCandidateMethod->apply($map, $grid, $cell);

        return [$map, $map->get($cell)];
    }
}
