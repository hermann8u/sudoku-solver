<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\Group;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;
use SudokuSolver\Solver\XWing;

final readonly class XWingMethod implements Method
{
    public function __construct(
        private InclusiveMethod $inclusiveMethod,
    ) {
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        /** @var XWing[] $xWings */
        $xWings = [];

        $firstRow = $grid->getRowByCell($currentCell);

        [$map, $currentCellCandidates] = $this->getCandidates($map, $grid, $currentCell);
        [$map, $potentialSecondCells] = $this->getPotentialRelatedCellsInGroup($currentCellCandidates, $map, $grid, $firstRow, $currentCell);

        if ($potentialSecondCells === []) {
            return $map;
        }

        $column = $grid->getColumnByCell($currentCell);

        foreach ($potentialSecondCells as $secondCellCoordinatesString => $secondCellCandidates) {
            [$map, $potentialThirdCells] = $this->getPotentialRelatedCellsInGroup($secondCellCandidates, $map, $grid, $column, $currentCell, false);

            if ($potentialThirdCells === []) {
                continue;
            }

            foreach ($potentialThirdCells as $thirdCellCoordinatesString => $thirdCellCandidates) {
                $thirdCellCoordinates = Coordinates::fromString($thirdCellCoordinatesString);
                /** @var FillableCell $thirdCell */
                $thirdCell = $grid->getCell($thirdCellCoordinates);

                $secondRow = $grid->getRowByCell($thirdCell);

                [$map, $potentialFourthCells] = $this->getPotentialRelatedCellsInGroup($thirdCellCandidates, $map, $grid, $secondRow, $thirdCell);

                if ($potentialFourthCells === []) {
                    continue;
                }

                $secondCellCoordinates = Coordinates::fromString($secondCellCoordinatesString);
                $fourthCellCoordinates = new Coordinates($secondCellCoordinates->x, $secondRow->number);

                $fourthCellCandidates = $potentialFourthCells[$fourthCellCoordinates->toString()] ?? null;
                if ($fourthCellCandidates === null) {
                    continue;
                }

                $allCandidatesIntersect = $thirdCellCandidates->intersect($fourthCellCandidates);

                if ($allCandidatesIntersect->count() !== 1) {
                    continue;
                }

                $xWings[] = new XWing(
                    [
                        $currentCell->coordinates,
                        $secondCellCoordinates,
                        $thirdCellCoordinates,
                        $fourthCellCoordinates,
                    ],
                    $allCandidatesIntersect->first(),
                );
            }
        }

        foreach ($xWings as $xWing) {
            $already = [];

            foreach ($xWing->coordinatesList as $coordinates) {
                $cell = $grid->getCell($coordinates);

                $column = $grid->getColumnByCell($cell);

                if (\in_array($column->number, $already)) {
                    continue;
                }

                $already[] = $column->number;

                foreach ($column->getEmptyCells() as $fillableCell) {
                    if ($xWing->contains($fillableCell)) {
                        continue;
                    }

                    [$map, $candidates] = $this->getCandidates($map, $grid, $fillableCell);
                    $candidates = $candidates->withRemovedValues($xWing->value);

                    $map = $map->merge($fillableCell, $candidates);

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
    private function getPotentialRelatedCellsInGroup(
        Candidates $currentFilteredCandidates,
        CellCandidatesMap $map,
        Grid $grid,
        Group $currentGroup,
        FillableCell $currentCell,
        bool $withFilter = true,
    ): array {
        [$map, $mapForGroup] = $this->getMapForGroup($map, $grid, $currentGroup);

        $currentCoordinatesAsString = $currentCell->coordinates->toString();
        $mapForGroup = $mapForGroup->filter(
            static fn (Candidates $candidates, string $coordinatesAsString) => $coordinatesAsString !== $currentCoordinatesAsString,
        );

        if ($mapForGroup->isEmpty()) {
            return [$map, []];
        }

        if ($withFilter) {
            $expectedValues = $mapForGroup->multidimensionalKeyLoop($this->filterDuplicateValues(...), $currentFilteredCandidates);
        }

        $potentialRelatedCells = [];

        foreach ($currentGroup->getEmptyCells() as $relatedCell) {
            if ($currentCell->regionNumber->is($relatedCell->regionNumber)) {
                continue;
            }

            $relatedCellCandidates = $mapForGroup->get($relatedCell);

            $intersectCellCandidates = $currentFilteredCandidates->intersect($relatedCellCandidates);

            if ($intersectCellCandidates->count() === 0) {
                continue;
            }

            if ($withFilter && ! $intersectCellCandidates->hasOneOf($expectedValues->values)) {
                continue;
            }

            $potentialRelatedCells[$relatedCell->coordinates->toString()] = $intersectCellCandidates;
        }

        return [$map, $potentialRelatedCells];
    }

    /**
     * @return array{CellCandidatesMap, Candidates}
     */
    private function getCandidates(CellCandidatesMap $map, Grid $grid, FillableCell $cell): array
    {
        if (! $map->has($cell)) {
            $map = $this->inclusiveMethod->apply($map, $grid, $cell);
        }

        return [$map, $map->get($cell)];
    }

    /**
     * @return array{CellCandidatesMap, CellCandidatesMap}
     */
    private function getMapForGroup(CellCandidatesMap $map, Grid $grid, Group $group): array
    {
        $partialMap = CellCandidatesMap::empty();

        foreach ($group->getEmptyCells() as $cell) {
            if (! $map->has($cell)) {
                $map = $this->inclusiveMethod->apply($map, $grid, $cell);
            }

            $partialMap = $partialMap->merge($cell, $map->get($cell));
        }

        return [$map, $partialMap];
    }

    private function filterDuplicateValues(CellCandidatesMap $mapForRow, Candidates $carry, string $a, string $b): Candidates
    {
        $aCandidates = $mapForRow->get($a);
        $bCandidates = $mapForRow->get($b);

        $intersect = $aCandidates->intersect($bCandidates);

        if ($intersect->count() === 0) {
            return $carry;
        }

        return $carry->withRemovedValues(...$intersect);
    }
}
