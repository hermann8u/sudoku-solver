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
use SudokuSolver\Solver\CandidatesProvider;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;
use SudokuSolver\Solver\Method\Behavior\GetCandidatesBehavior;
use SudokuSolver\Solver\Method\Behavior\GetMapForGroupBehavior;
use SudokuSolver\Solver\XWing;
use SudokuSolver\Solver\XWing\Direction;

final readonly class XWingMethod implements Method
{
    use GetCandidatesBehavior;
    use GetMapForGroupBehavior;

    public function __construct(
        private CandidatesProvider $candidatesProvider,
    ) {
    }

    public static function getName(): string
    {
        return 'x_wing';
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        /** @var XWing[] $xWings */
        $xWings = [];

        foreach (Direction::cases() as $direction) {
            [$map, $xWingsByDirection] = $this->buildXWings($direction, $map, $grid, $currentCell);
            $xWings = [...$xWings, ...$xWingsByDirection];
        }

        foreach ($xWings as $xWing) {
            foreach ($xWing->getGroupsToModify($grid) as $group) {
                foreach ($group->getEmptyCells() as $fillableCell) {
                    if ($xWing->contains($fillableCell)) {
                        continue;
                    }

                    [$map, $candidates] = $this->getCandidates($map, $grid, $fillableCell);
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
     * @return array{CellCandidatesMap, XWing[]}
     */
    private function buildXWings(Direction $direction, CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): array
    {
        $firstDirectionGroupCallable = match ($direction) {
            Direction::Horizontal => $grid->getRowByCell(...),
            Direction::Vertical => $grid->getColumnByCell(...),
        };

        $firstGroup = $firstDirectionGroupCallable($currentCell);

        [$map, $currentCellCandidates] = $this->getCandidates($map, $grid, $currentCell);
        [$map, $potentialSecondCells] = $this->getPotentialRelatedCellsInGroup($currentCellCandidates, $map, $grid, $firstGroup, $currentCell);

        if ($potentialSecondCells === []) {
            return [$map, []];
        }

        $otherDirectionGroup = match ($direction) {
            Direction::Horizontal => $grid->getColumnByCell($currentCell),
            Direction::Vertical => $grid->getRowByCell($currentCell),
        };

        foreach ($potentialSecondCells as $secondCellCoordinatesString => $secondCellCandidates) {
            [$map, $potentialThirdCells] = $this->getPotentialRelatedCellsInGroup($secondCellCandidates, $map, $grid, $otherDirectionGroup, $currentCell, false);

            if ($potentialThirdCells === []) {
                continue;
            }

            foreach ($potentialThirdCells as $thirdCellCoordinatesString => $thirdCellCandidates) {
                $thirdCellCoordinates = Coordinates::fromString($thirdCellCoordinatesString);
                /** @var FillableCell $thirdCell */
                $thirdCell = $grid->getCell($thirdCellCoordinates);

                /** @var Group $secondGroup */
                $secondGroup = $firstDirectionGroupCallable($thirdCell);

                [$map, $potentialFourthCells] = $this->getPotentialRelatedCellsInGroup($thirdCellCandidates, $map, $grid, $secondGroup, $thirdCell);

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

        return [$map, $xWings ?? []];
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

        $mapForGroup = $mapForGroup->filter(
            static fn (Candidates $candidates, FillableCell $c) => ! $c->is($currentCell),
        );

        if ($mapForGroup->isEmpty()) {
            return [$map, []];
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

        return [$map, $potentialRelatedCells];
    }

    private function filterDuplicateValues(CellCandidatesMap $mapForGroup, Candidates $carry, FillableCell $a, FillableCell $b): Candidates
    {
        $aCandidates = $mapForGroup->get($a);
        $bCandidates = $mapForGroup->get($b);

        return $carry->withRemovedValues(...$aCandidates->intersect($bCandidates)->values);
    }
}
