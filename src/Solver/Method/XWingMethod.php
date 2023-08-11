<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\Group;
use SudokuSolver\Grid\Group\Row;
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
        if (false === $currentCell->coordinates->is(Coordinates::fromString('(2,3)'))) {
            return $map;
        }

        /** @var XWing[] $xWings */
        $xWings = [];

        $firstRow = $grid->getRowByCell($currentCell);

        [$map, $currentCellCandidates] = $this->getCandidates($map, $grid, $currentCell);

        [$map, $firstRowRelatedCells] = $this->getPotentialRelatedCellsInRow($map, $grid, $firstRow, $currentCell);

        if ($firstRowRelatedCells === []) {
            return $map;
        }

        foreach ($grid->rows as $secondRow) {
            $secondRowCell = $grid->getCell(new Coordinates($currentCell->coordinates->x, $secondRow->y));

            if (! $secondRowCell->isEmpty() || ! $secondRowCell instanceof FillableCell || $currentCell->regionNumber->is($secondRowCell->regionNumber)) {
                continue;
            }

            [$map, $secondRowCellCandidates] = $this->getCandidates($map, $grid, $secondRowCell);

            $intersectCandidates = $currentCellCandidates->intersect($secondRowCellCandidates);
            if ($intersectCandidates->count() === 0) {
                continue;
            }

            [$map, $secondRowRelatedCells] = $this->getPotentialRelatedCellsInRow($map, $grid, $secondRow, $secondRowCell);

            foreach ($firstRowRelatedCells as $firstRowRelatedCellCoordinatesString => $firstRowRelatedCellCandidates) {
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

                foreach ($allCandidatesIntersect as $value) {
                    $xWings[] = new XWing(
                        [
                            $currentCell->coordinates,
                            $firstRowRelatedCellCoordinates,
                            $secondRowCell->coordinates,
                            $secondRowRelatedCellCoordinates,
                        ],
                        $value,
                    );
                }
            }
        }

        foreach ($xWings as $xWing) {
            foreach ($xWing->coordinatesList as $coordinates) {
                $cell = $grid->getCell($coordinates);

                $column = $grid->getColumnByCell($cell);

                foreach ($column->getEmptyCells() as $fillableCell) {
                    if ($xWing->contains($fillableCell)) {
                        continue;
                    }

                    [$map, $candidates] = $this->getCandidates($map, $grid, $fillableCell);

                    $filteredCandidates = $candidates->withRemovedValues($xWing->value);

                    $map = $map->merge($fillableCell, $filteredCandidates);

                    if ($filteredCandidates->hasUniqueValue()) {
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
        if (! $map->has($cell)) {
            $map = $this->inclusiveMethod->apply($map, $grid, $cell);
        }

        return [$map, $map->get($cell)];
    }

    /**
     * @return array{CellCandidatesMap, array<string, Candidates>}
     */
    private function getPotentialRelatedCellsInRow(CellCandidatesMap $map, Grid $grid, Row $currentRow, FillableCell $currentCell): array
    {
        $potentialRelatedCells = [];

        [$map, $currentCandidates] = $this->getCandidates($map, $grid, $currentCell);

        [$map, $mapForRow] = $this->getMapForGroup($map, $grid, $currentRow);

        $mapForRow = $mapForRow->filter(static fn (Candidates $c, string $coordinatesString) => $coordinatesString !== $currentCell->coordinates->toString());

        foreach ($currentRow->getEmptyCells() as $relatedCell) {
            if ($currentCell->regionNumber->is($relatedCell->regionNumber)) {
                continue;
            }

            $relatedCellCandidates = $mapForRow->get($relatedCell);

            $intersectCandidates = $currentCandidates->intersect($relatedCellCandidates);
            if ($intersectCandidates->count() === 0) {
                continue;
            }

            foreach ($mapForRow as $rowCellCoordinatesString => $rowCellCandidates) {
                if ($rowCellCoordinatesString === $relatedCell->coordinates->toString()) {
                    continue;
                }

                if ($intersectCandidates->intersect($rowCellCandidates)->count() > 0) {
                    continue 2;
                }
            }

            $potentialRelatedCells[$relatedCell->coordinates->toString()] = $intersectCandidates;
        }

        return [$map, $potentialRelatedCells];
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
}
