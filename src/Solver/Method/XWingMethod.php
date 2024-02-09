<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell;
use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Sudoku\Grid\Group\Number\RegionNumber;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method;
use Sudoku\Solver\XWing;
use Sudoku\Solver\XWing\Direction;

final readonly class XWingMethod implements Method
{
    public static function getName(): string
    {
        return 'x_wing';
    }

    /**
     * @inheritdoc
     */
    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map
    {
        $xWings = $this->buildXWings($candidatesByCell, $grid, $currentCell);

        /** @var XWing $xWing */
        foreach ($xWings as $xWing) {
            foreach ($xWing->getGroupsToModify($grid) as $group) {
                foreach ($group->getEmptyCells() as $fillableCell) {
                    if ($xWing->contains($fillableCell)) {
                        continue;
                    }

                    $candidates = $candidatesByCell->get($fillableCell)->withRemovedValues($xWing->value);
                    $candidatesByCell = $candidatesByCell->with($fillableCell, $candidates);

                    if ($candidates->hasUniqueCandidate()) {
                        return $candidatesByCell;
                    }
                }
            }
        }

        return $candidatesByCell;
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return iterable<XWing>
     */
    private function buildXWings(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): iterable
    {
        foreach (Direction::cases() as $direction) {
            yield from $this->buildXWingsForDirection($direction, $candidatesByCell, $grid, $currentCell);
        }
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return iterable<XWing>
     */
    private function buildXWingsForDirection(
        Direction $direction,
        Map $candidatesByCell,
        Grid $grid,
        FillableCell $currentCell,
    ): iterable {
        $firstDirectionGroupCallable = match ($direction) {
            Direction::Horizontal => $grid->getRowByCell(...),
            Direction::Vertical => $grid->getColumnByCell(...),
        };

        $firstGroup = $firstDirectionGroupCallable($currentCell);

        $currentCellCandidates = $candidatesByCell->get($currentCell);
        $potentialSecondCells = $this->getPotentialRelatedCellsInGroup(
            $candidatesByCell,
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
                $candidatesByCell,
                $otherDirectionGroup,
                $currentCell,
                $secondCellCandidates,
                false,
            );

            foreach ($potentialThirdCells as $thirdCell => $thirdCellCandidates) {
                /** @var Group $secondGroup */
                $secondGroup = $firstDirectionGroupCallable($thirdCell);

                $potentialFourthCells = $this->getPotentialRelatedCellsInGroup(
                    $candidatesByCell,
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

                yield new XWing(
                    $direction,
                    ArrayList::fromItems(
                        $currentCell,
                        $secondCell,
                        $thirdCell,
                        $fourthCell,
                    ),
                    $allCandidatesIntersect->first(),
                );
            }
        }
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return Map<FillableCell, Candidates>
     */
    private function getPotentialRelatedCellsInGroup(
        Map $candidatesByCell,
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
                ->map(static fn (FillableCell $c) => $candidatesByCell->get($c))
                ->multidimensionalLoop($this->filterDuplicateValues(...), $expectedValues);
        }

        $currentCellRegionNumber = RegionNumber::fromCell($currentCell);

        return $currentGroup->getEmptyCells()
            ->filter(static fn (FillableCell $c) => ! $currentCellRegionNumber->equals(RegionNumber::fromCell($c)))
            ->reduce(function (Map $carry, FillableCell $relatedCell) use ($expectedValues, $candidatesByCell, $currentFilteredCandidates) {
                $intersectCellCandidates = $currentFilteredCandidates->intersect($candidatesByCell->get($relatedCell));

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
