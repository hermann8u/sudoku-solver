<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\Group;
use SudokuSolver\Grid\Group\Number\RegionNumber;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;
use SudokuSolver\Solver\XWing;
use SudokuSolver\Solver\XWing\Direction;

final readonly class XWingMethod implements Method
{
    public static function getName(): string
    {
        return 'x_wing';
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        /** @var ArrayList<XWing> $xWings */
        $xWings = ArrayList::empty();

        foreach (Direction::cases() as $direction) {
            $xWingsByDirection = $this->buildXWings($direction, $map, $grid, $currentCell);
            $xWings = $xWings->merge(...$xWingsByDirection);
        }

        foreach ($xWings as $xWing) {
            foreach ($xWing->getGroupsToModify($grid) as $group) {
                foreach ($group->getEmptyCells() as $fillableCell) {
                    if ($xWing->contains($fillableCell)) {
                        continue;
                    }

                    $candidates = $map->get($fillableCell);
                    $candidates = $candidates->withRemovedValues($xWing->value);

                    $map = $map->with($fillableCell, $candidates);

                    if ($candidates->hasUniqueCandidate()) {
                        return $map;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * @return XWing[]
     */
    private function buildXWings(Direction $direction, CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): array
    {
        $firstDirectionGroupCallable = match ($direction) {
            Direction::Horizontal => $grid->getRowByCell(...),
            Direction::Vertical => $grid->getColumnByCell(...),
        };

        $firstGroup = $firstDirectionGroupCallable($currentCell);

        $currentCellCandidates = $map->get($currentCell);
        $potentialSecondCells = $this->getPotentialRelatedCellsInGroup(
            $map,
            $firstGroup,
            $currentCell,
            $currentCellCandidates,
        );

        if ($potentialSecondCells === []) {
            return [];
        }

        $otherDirectionGroup = match ($direction) {
            Direction::Horizontal => $grid->getColumnByCell($currentCell),
            Direction::Vertical => $grid->getRowByCell($currentCell),
        };

        foreach ($potentialSecondCells as $secondCellCoordinatesString => $secondCellCandidates) {
            $potentialThirdCells = $this->getPotentialRelatedCellsInGroup(
                $map,
                $otherDirectionGroup,
                $currentCell,
                $secondCellCandidates,
                false,
            );

            if ($potentialThirdCells === []) {
                continue;
            }

            foreach ($potentialThirdCells as $thirdCellCoordinatesString => $thirdCellCandidates) {
                $thirdCellCoordinates = Coordinates::fromString($thirdCellCoordinatesString);
                /** @var FillableCell $thirdCell */
                $thirdCell = $grid->getCell($thirdCellCoordinates);

                /** @var Group $secondGroup */
                $secondGroup = $firstDirectionGroupCallable($thirdCell);

                $potentialFourthCells = $this->getPotentialRelatedCellsInGroup(
                    $map,
                    $secondGroup,
                    $thirdCell,
                    $thirdCellCandidates,
                );

                if ($potentialFourthCells === []) {
                    continue;
                }

                $secondCellCoordinates = Coordinates::fromString($secondCellCoordinatesString);

                $fourthCellCoordinates = match ($direction) {
                    Direction::Horizontal => Coordinates::from($secondCellCoordinates->x, $secondGroup->number->value),
                    Direction::Vertical => Coordinates::from($secondGroup->number->value, $secondCellCoordinates->y),
                };

                $fourthCellCandidates = $potentialFourthCells[$fourthCellCoordinates->toString()] ?? null;
                if ($fourthCellCandidates === null) {
                    continue;
                }

                $allCandidatesIntersect = $thirdCellCandidates->intersect($fourthCellCandidates);

                if ($allCandidatesIntersect->count() !== 1) {
                    continue;
                }

                $xWings[] = new XWing(
                    $direction,
                    ArrayList::fromList([
                        $currentCell->coordinates,
                        $secondCellCoordinates,
                        $thirdCellCoordinates,
                        $fourthCellCoordinates,
                    ]),
                    $allCandidatesIntersect->first(),
                );
            }
        }

        return $xWings ?? [];
    }

    /**
     * @return array<string, Candidates>
     */
    private function getPotentialRelatedCellsInGroup(
        CellCandidatesMap $map,
        Group $currentGroup,
        FillableCell $currentCell,
        Candidates $currentFilteredCandidates,
        bool $withFilter = true,
    ): array {
        $mapForGroup = $map->filter(static fn (Candidates $candidates, FillableCell $cell) => $currentGroup->cells->contains($cell) && ! $cell->is($currentCell));

        if ($mapForGroup->isEmpty()) {
            return [];
        }

        $expectedValues = $currentFilteredCandidates;

        if ($withFilter) {
            $expectedValues = $mapForGroup->multidimensionalLoop($this->filterDuplicateValues(...), $expectedValues);
        }

        $potentialRelatedCells = [];

        $currentCellRegionNumber = RegionNumber::fromCell($currentCell);

        foreach ($currentGroup->getEmptyCells() as $relatedCell) {
            if ($currentCellRegionNumber->equals(RegionNumber::fromCell($relatedCell))) {
                continue;
            }

            $intersectCellCandidates = $currentFilteredCandidates->intersect($mapForGroup->get($relatedCell));

            if ($intersectCellCandidates->count() === 0) {
                continue;
            }

            if ($intersectCellCandidates->intersect($expectedValues)->count() === 0) {
                continue;
            }

            $potentialRelatedCells[$relatedCell->coordinates->toString()] = $intersectCellCandidates;
        }

        return $potentialRelatedCells;
    }

    private function filterDuplicateValues(CellCandidatesMap $mapForGroup, Candidates $carry, FillableCell $a, FillableCell $b): Candidates
    {
        $aCandidates = $mapForGroup->get($a);
        $bCandidates = $mapForGroup->get($b);

        return $carry->withRemovedValues(...$aCandidates->intersect($bCandidates)->values);
    }
}
