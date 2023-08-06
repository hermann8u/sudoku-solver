<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\CellValue;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
use Florian\SudokuSolver\Grid\Group;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method;
use Florian\SudokuSolver\Solver\Pair;

final readonly class FilterPairMethod implements Method
{
    public function __construct(
        private InclusiveMethod $inclusiveMethod,
    ) {
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        $groups = $grid->getGroupForCell($currentCell);

        foreach ($groups as $group) {
            [$map, $pairs] = $this->findPairs($map, $grid, $group);

            foreach ($pairs as $pair) {
                foreach ($group->getEmptyCells() as $cell) {
                    if ($pair->match($cell)) {
                        $map = $map->merge($cell, $pair->candidates);
                        continue;
                    }

                    [$map, $candidates] = $this->getCandidates($map, $grid, $cell);
                    $candidates = $candidates->withRemovedValues(...$pair->candidates);
                    $map = $map->merge($cell, $candidates);

                    if ($candidates->hasUniqueValue()) {
                        return $map;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * @return array{CellCandidatesMap, Pair[]}
     */
    private function findPairs(CellCandidatesMap $map, Grid $grid, Group $group): array
    {
        $candidateCoordinatesMap = array_fill(CellValue::MIN, CellValue::MAX, []);

        foreach ($group->getEmptyCells() as $cell) {
            [$map, $candidates] = $this->getCandidates($map, $grid, $cell);

            foreach ($candidates as $candidate) {
                $candidateCoordinatesMap[$candidate->value][] = $cell->coordinates->toString();
            }
        }

        // Filter candidates with more or less than 2 possible cells
        /** @var array<int<CellValue::MIN, CellValue::MAX>, string[]> $candidateCoordinatesMap */
        $candidateCoordinatesMap = array_filter($candidateCoordinatesMap, static fn (array $items) => count($items) === 1);

        return [$map, $this->associatePairs($candidateCoordinatesMap)];
    }

    /**
     * @return array{CellCandidatesMap, Candidates}
     */
    private function getCandidates(CellCandidatesMap $map, Grid $grid, FillableCell $cell): array
    {
        $map = $this->inclusiveMethod->apply($map, $grid, $cell);

        return [$map, $map->get($cell)];
    }

    /**
     * @param array<int<CellValue::MIN, CellValue::MAX>, string[]>  $candidateCoordinatesMap
     *
     * @return Pair[]
     */
    private function associatePairs(array $candidateCoordinatesMap): array
    {
        foreach ($candidateCoordinatesMap as $v1 => $coordinatesPair) {
            foreach ($candidateCoordinatesMap as $v2 => $otherCoordinatesPair) {
                if ($v1 === $v2) {
                    continue;
                }

                if ($otherCoordinatesPair === $coordinatesPair) {
                    $pairs[] = new Pair(
                        array_map(
                            static fn (string $coordinates) => Coordinates::fromString($coordinates),
                            $coordinatesPair,
                        ),
                        Candidates::fromInt($v1, $v2),
                    );

                    unset($candidateCoordinatesMap[$v1]);
                    unset($candidateCoordinatesMap[$v2]);

                    if (count($candidateCoordinatesMap) < 2) {
                        return $pairs;
                    }
                }
            }
        }

        return $pairs ?? [];
    }
}
