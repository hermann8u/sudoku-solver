<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\DataStructure\Map;
use SudokuSolver\Grid\Cell;
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

        /** @var XWing $xWing */
        foreach ($xWings as $xWing) {
            foreach ($xWing->getGroupsToModify($grid) as $group) {
                foreach ($group->getEmptyCells() as $fillableCell) {
                    if ($xWing->contains($fillableCell)) {
                        continue;
                    }

                    $candidates = $map->get($fillableCell)->withRemovedValues($xWing->value);
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
     * @return ArrayList<XWing>
     */
    private function buildXWings(Direction $direction, CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): ArrayList
    {
        $xWings = ArrayList::empty();

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

        $otherDirectionGroup = match ($direction) {
            Direction::Horizontal => $grid->getColumnByCell($currentCell),
            Direction::Vertical => $grid->getRowByCell($currentCell),
        };

        foreach ($potentialSecondCells as $secondCell => $secondCellCandidates) {
            $potentialThirdCells = $this->getPotentialRelatedCellsInGroup(
                $map,
                $otherDirectionGroup,
                $currentCell,
                $secondCellCandidates,
                false,
            );

            foreach ($potentialThirdCells as $thirdCell => $thirdCellCandidates) {
                /** @var Group $secondGroup */
                $secondGroup = $firstDirectionGroupCallable($thirdCell);

                $potentialFourthCells = $this->getPotentialRelatedCellsInGroup(
                    $map,
                    $secondGroup,
                    $thirdCell,
                    $thirdCellCandidates,
                );

                if ($potentialFourthCells->isEmpty()) {
                    continue;
                }

                $fourthCell = $grid->getCell(match ($direction) {
                    Direction::Horizontal => Coordinates::from($secondCell->coordinates->x, $secondGroup->number->value),
                    Direction::Vertical => Coordinates::from($secondGroup->number->value, $secondCell->coordinates->y),
                });

                if (! $fourthCell instanceof FillableCell || ! $potentialFourthCells->has($fourthCell)) {
                    continue;
                }

                $allCandidatesIntersect = $thirdCellCandidates->intersect($potentialFourthCells->get($fourthCell));

                if ($allCandidatesIntersect->count() !== 1) {
                    continue;
                }

                $xWings = $xWings->merge(new XWing(
                    $direction,
                    ArrayList::fromItems(
                        $currentCell,
                        $secondCell,
                        $thirdCell,
                        $fourthCell,
                    ),
                    $allCandidatesIntersect->first(),
                ));
            }
        }

        return $xWings;
    }

    /**
     * @return Map<FillableCell, Candidates>
     */
    private function getPotentialRelatedCellsInGroup(
        CellCandidatesMap $map,
        Group $currentGroup,
        FillableCell $currentCell,
        Candidates $currentFilteredCandidates,
        bool $withFilter = true,
    ): Map {
        /** @var ArrayList<FillableCell> $cells */
        $cells = $currentGroup->cells->filter(static fn (Cell $c) => $c->isEmpty() && ! $c->is($currentCell));

        if ($cells->isEmpty()) {
            return Map::empty();
        }

        $expectedValues = $currentFilteredCandidates;

        if ($withFilter) {
            $expectedValues = $cells
                ->map(static fn (FillableCell $c) => $map->get($c))
                ->multidimensionalLoop($this->filterDuplicateValues(...), $expectedValues);
        }

        $currentCellRegionNumber = RegionNumber::fromCell($currentCell);

        return $currentGroup->getEmptyCells()
            ->filter(static fn (FillableCell $c) => ! $currentCellRegionNumber->equals(RegionNumber::fromCell($c)))
            ->reduce(function (Map $carry, FillableCell $relatedCell) use ($expectedValues, $map, $currentFilteredCandidates) {
                $intersectCellCandidates = $currentFilteredCandidates->intersect($map->get($relatedCell));

                if (
                    $intersectCellCandidates->count() === 0
                    || $intersectCellCandidates->intersect($expectedValues)->count() === 0
                ) {
                    return $carry;
                }

                return $carry->with($relatedCell, $intersectCellCandidates);
            }, Map::empty());
    }

    private function filterDuplicateValues(Candidates $carry, Candidates $a, Candidates $b): Candidates
    {
        return $carry->withRemovedValues(...$a->intersect($b)->values);
    }
}
