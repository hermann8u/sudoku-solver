<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
use Florian\SudokuSolver\Grid\Group;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method;
use Florian\SudokuSolver\Solver\Pair;

final readonly class ExclusivePairMethod implements Method
{
    public function __construct(
        private InclusiveMethod $inclusiveMethod,
    ) {
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        foreach ($grid->getGroupForCell($currentCell) as $group) {
            [$map, $candidatesByGroup] = $this->getCandidatesByGroup($map, $grid, $group);
            $coordinatesByCandidates = $this->prepareAssociations($candidatesByGroup);
            $pairs = $this->associatePairs($coordinatesByCandidates);

            foreach ($pairs as $pair) {
                foreach ($group->getEmptyCells() as $cell) {
                    if ($pair->contains($cell)) {
                        continue;
                    }

                    $candidates = $candidatesByGroup[$cell->coordinates->toString()];
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
     * @return array{CellCandidatesMap, array<string, Candidates>}
     */
    private function getCandidatesByGroup(CellCandidatesMap $map, Grid $grid, Group $group): array
    {
        foreach ($group->getEmptyCells() as $cell) {
            if (! $map->has($cell)) {
                $map = $this->inclusiveMethod->apply($map, $grid, $cell);
            }

            $candidates = $map->get($cell);

            $candidatesByGroup[$cell->coordinates->toString()] = $candidates;
        }

        return [$map, $candidatesByGroup ?? []];
    }

    /**
     * @param array<string, Candidates> $candidatesByGroup
     *
     * @return array<string, string[]>
     */
    private function prepareAssociations(array $candidatesByGroup): array
    {
        $candidatesByGroup = array_filter($candidatesByGroup, static fn (Candidates $c) => $c->count() === 2);

        foreach ($candidatesByGroup as $coordinates => $candidates) {
            $coordinatesByCandidates[$candidates->toString()][] = $coordinates;
        }

        return array_filter(
            $coordinatesByCandidates ?? [],
            static fn (array $coordinatesPair) => count($coordinatesPair) === 2,
        );
    }

    /**
     * @param array<string, string[]>  $candidatesCoordinatesMap
     *
     * @return Pair[]
     */
    private function associatePairs(array $candidatesCoordinatesMap): array
    {
        foreach ($candidatesCoordinatesMap as $valuesString => $coordinatesPair) {
            $pairs[] = new Pair(
                array_map(
                    static fn (string $coordinates) => Coordinates::fromString($coordinates),
                    $coordinatesPair,
                ),
                Candidates::fromString($valuesString),
            );
        }

        return $pairs ?? [];
    }
}
