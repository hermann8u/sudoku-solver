<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\CellValue;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
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
            $coordinatesByCandidates = [];

            foreach ($group->getEmptyCells() as $cell) {
                [$map, $candidates] = $this->getCandidates($map, $grid, $cell);

                if ($candidates->count() === 2) {
                    $values = $candidates->toIntegers();
                    sort($values);

                    $coordinatesByCandidates[implode(',', $values)][] = $cell->coordinates->toString();
                }
            }

            $coordinatesByCandidates = array_filter($coordinatesByCandidates, static fn ($e) => count($e) === 2);

            $pairs = $this->associatePairs($coordinatesByCandidates);

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
     * @return array{CellCandidatesMap, Candidates}
     */
    private function getCandidates(CellCandidatesMap $map, Grid $grid, FillableCell $cell): array
    {
        $map = $this->inclusiveMethod->apply($map, $grid, $cell);

        return [$map, $map->get($cell)];
    }

    /**
     * @param array<string, string[]>  $candidatesCoordinatesMap
     *
     * @return Pair[]
     */
    private function associatePairs(array $candidatesCoordinatesMap): array
    {
        foreach ($candidatesCoordinatesMap as $valuesString => $coordinatesPair) {
            $values = explode(',', $valuesString);
            /** @var array<int<CellValue::MIN, CellValue::MAX>> $values */
            $values = array_map(static fn (string $v) => (int) $v, $values);

            $pairs[] = new Pair(
                array_map(
                    static fn (string $coordinates) => Coordinates::fromString($coordinates),
                    $coordinatesPair,
                ),
                Candidates::fromInt(...$values),
            );
        }

        return $pairs ?? [];
    }
}
