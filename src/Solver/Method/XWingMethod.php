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

final class XWingMethod implements Method
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
        [$map, $potentialSecondCells] = $this->getPotentialRelatedCellsInGroup($map, $grid, $firstRow, $currentCell);

        if ($potentialSecondCells === []) {
            return $map;
        }

        $column = $grid->getColumnByCell($currentCell);
        [$map, $potentialThirdCells] = $this->getPotentialRelatedCellsInGroup($map, $grid, $column, $currentCell, false);

        if ($potentialThirdCells === []) {
            return $map;
        }

        foreach ($potentialThirdCells as $thirdCellCoordinatesString => $thirdCellCandidates) {
            $thirdCellCoordinates = Coordinates::fromString($thirdCellCoordinatesString);
            /** @var FillableCell $thirdCell */
            $thirdCell = $grid->getCell($thirdCellCoordinates);

            $secondRow = $grid->getRowByCell($thirdCell);

            [$map, $potentialFourthCells] = $this->getPotentialRelatedCellsInGroup($map, $grid, $secondRow, $thirdCell);

            if ($potentialFourthCells === []) {
                continue;
            }

            foreach ($potentialSecondCells as $secondCellCoordinatesString => $secondCellCandidates) {
                $secondCellCoordinates = Coordinates::fromString($secondCellCoordinatesString);
                $fourthCellCoordinates = new Coordinates($secondCellCoordinates->x, $secondRow->y);

                $fourthCellCandidates = $potentialFourthCells[$fourthCellCoordinates->toString()] ?? null;
                if ($fourthCellCandidates === null) {
                    continue;
                }

                $allCandidatesIntersect = $currentCellCandidates->intersect($secondCellCandidates, $thirdCellCandidates, $fourthCellCandidates);

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


        /*foreach ($grid->rows as $secondRow) {
            $secondRowCell = $grid->getCell(new Coordinates($currentCell->coordinates->x, $secondRow->y));

            if (! $secondRowCell->isEmpty() || ! $secondRowCell instanceof FillableCell || $currentCell->regionNumber->is($secondRowCell->regionNumber)) {
                continue;
            }

            [$map, $secondRowCellCandidates] = $this->getCandidates($map, $grid, $secondRowCell);

            $intersectCandidates = $currentCellCandidates->intersect($secondRowCellCandidates);
            if ($intersectCandidates->count() === 0) {
                continue;
            }

            [$map, $secondRowRelatedCells] = $this->getPotentialRelatedCellsInGroup($map, $grid, $secondRow, $secondRowCell);

            foreach ($potentialSecondCells as $firstRowRelatedCellCoordinatesString => $firstRowRelatedCellCandidates) {
                $firstRowRelatedCellCoordinates = Coordinates::fromString($firstRowRelatedCellCoordinatesString);
                $secondRowRelatedCellCoordinates = new Coordinates($firstRowRelatedCellCoordinates->x, $secondRow->y);

                $secondRowRelatedCellCandidates = $secondRowRelatedCells[$secondRowRelatedCellCoordinates->toString()] ?? null;
                if ($secondRowRelatedCellCandidates === null) {
                    continue;
                }

                $allCandidatesIntersect = $intersectCandidates->intersect($firstRowRelatedCellCandidates, $secondRowRelatedCellCandidates);

                if ($allCandidatesIntersect->count() !== 1) {
                    continue;
                }

                $xWings[] = new XWing(
                    [
                        $currentCell->coordinates,
                        $firstRowRelatedCellCoordinates,
                        $secondRowCell->coordinates,
                        $secondRowRelatedCellCoordinates,
                    ],
                    $allCandidatesIntersect->first(),
                );
            }
        }*/

        // dump($xWings);

        foreach ($xWings as $xWing) {
            foreach ($xWing->coordinatesList as $coordinates) {
                $cell = $grid->getCell($coordinates);

                $column = $grid->getColumnByCell($cell);

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
    private function getPotentialRelatedCellsInGroup(CellCandidatesMap $map, Grid $grid, Group $currentGroup, FillableCell $currentCell, bool $withFilter = true): array
    {
        [$map, $currentCandidates] = $this->getCandidates($map, $grid, $currentCell);
        [$map, $mapForRow] = $this->getMapForGroup($map, $grid, $currentGroup, withoutCell: $currentCell);

        if ($mapForRow->isEmpty()) {
            return [$map, []];
        }

        $acceptedValues = $withFilter
            ? Candidates::fromValuesOnlyPresentOnceIn(...$mapForRow->getAllCandidates())
            : Candidates::empty();

        if ($withFilter && $acceptedValues->count() === 0) {
            return [$map, []];
        }

        $potentialRelatedCells = [];

        foreach ($currentGroup->getEmptyCells() as $relatedCell) {
            if ($currentCell->regionNumber->is($relatedCell->regionNumber)) {
                continue;
            }

            $relatedCellCandidates = $mapForRow->get($relatedCell);

            $intersectCandidates = $currentCandidates->intersect($relatedCellCandidates);
            if ($intersectCandidates->count() === 0) {
                continue;
            }

            if ($withFilter && ! $intersectCandidates->hasOneOf($acceptedValues->values)) {
                continue;
            }

            $potentialRelatedCells[$relatedCell->coordinates->toString()] = $intersectCandidates;
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
    private function getMapForGroup(CellCandidatesMap $map, Grid $grid, Group $group, ?FillableCell $withoutCell = null): array
    {
        $partialMap = CellCandidatesMap::empty();

        foreach ($group->getEmptyCells() as $cell) {
            if ($withoutCell instanceof FillableCell && $cell->is($withoutCell)) {
                continue;
            }

            if (! $map->has($cell)) {
                $map = $this->inclusiveMethod->apply($map, $grid, $cell);
            }

            $partialMap = $partialMap->merge($cell, $map->get($cell));
        }

        return [$map, $partialMap];
    }
}
