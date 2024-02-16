<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association\HiddenPair;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method;

final readonly class HiddenPairMethod implements Method
{
    public static function getName(): string
    {
        return 'hidden_pair_method';
    }

    /**
     * @inheritdoc
     */
    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map
    {
        $hiddenPairs = $this->buildHiddenPairs($candidatesByCell, $grid, $currentCell);

        foreach ($hiddenPairs as $hiddenPair) {
            dump($hiddenPair->toString());
            foreach ($hiddenPair->cells as $cell) {
                $candidatesByCell = $candidatesByCell->with($cell, Candidates::fromValues(...$hiddenPair->values));
            }

            foreach ($hiddenPair->getTargetedCells($grid) as $cell) {
                $candidatesByCell = $candidatesByCell->with(
                    $cell,
                    $candidatesByCell->get($cell)->withRemovedValues(...$hiddenPair->getCandidatesToEliminate()),
                );
            }
        }

        return $candidatesByCell;
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return iterable<HiddenPair>
     */
    private function buildHiddenPairs(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): iterable
    {
        /** @var Group $group */
        foreach ($grid->getGroupsForCell($currentCell) as $group) {
            $groupCells = $group->getEmptyCells()->filter($currentCell->isNot(...));

            /** @var Map<Candidates, ArrayList<FillableCell>> $cellsByCandidatesPairs */
            $cellsByCandidatesPairs = $groupCells->reduce(
                fn (Map $carry, FillableCell $relatedCell) => $this->groupCellsByCandidatesPair(
                    $candidatesByCell,
                    $carry,
                    $currentCell,
                    $relatedCell,
                ),
                Map::empty(),
            );

            foreach ($cellsByCandidatesPairs as $candidates => $cells) {
                if ($cells->count() !== HiddenPair::COUNT) {
                    continue;
                }

                $coordinates = $cells->map(static fn (FillableCell $c) => $c->coordinates);

                $otherCellsCandidates = $groupCells
                    ->filter(static fn (FillableCell $c) => ! $coordinates->contains($c->coordinates))
                    ->map(static fn (FillableCell $c) => $candidatesByCell->get($c))
                ;

                foreach ($otherCellsCandidates as $otherCellCandidates) {
                    if ($candidates->intersect($otherCellCandidates)->count() > 0) {
                        continue 2;
                    }
                }

                yield new HiddenPair($group, $cells, $candidates->values);
            }
        }
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     * @param Map<Candidates, ArrayList<FillableCell>> $carry
     *
     * @return Map<Candidates, ArrayList<FillableCell>>
     */
    private function groupCellsByCandidatesPair(
        Map $candidatesByCell,
        Map $carry,
        FillableCell $currentCell,
        FillableCell $relatedCell,
    ): Map {
        $currentCellCandidates = $candidatesByCell->get($currentCell);
        $relatedCellCandidates = $candidatesByCell->get($relatedCell);

        if ($currentCellCandidates->count() === 2 && $relatedCellCandidates->count() === 2) {
            return $carry;
        }

        $candidates = $currentCellCandidates->intersect($relatedCellCandidates);

        if ($candidates->count() < HiddenPair::COUNT) {
            return $carry;
        }

        /** @var ArrayList<Candidates> $candidatesPairs */
        $candidatesPairs = $candidates->values->multidimensionalLoop(
            static fn (ArrayList $candidatesPairs, Value $a, Value $b) => $candidatesPairs->with(Candidates::fromValues($a, $b)),
            ArrayList::empty(),
        );

        foreach ($candidatesPairs as $candidatesPair) {
            $carry = $this->fillCellsByCandidatesPair($carry, $candidatesPair, $currentCell, $relatedCell);
        }

        return $carry;
    }

    /**
     * @param Map<Candidates, ArrayList<FillableCell>> $carry
     *
     * @return Map<Candidates, ArrayList<FillableCell>>
     */
    private function fillCellsByCandidatesPair(
        Map $carry,
        Candidates $candidatesPair,
        FillableCell $a,
        FillableCell $b,
    ): Map {
        $key = $carry->keys()->findFirst($candidatesPair->equals(...));

        if (! $key instanceof Candidates) {
            return $carry->with($candidatesPair, ArrayList::fromItems($a, $b));
        }

        $cells = $carry->get($key);

        if (! $cells->contains($a)) {
            $cells = $cells->with($a);
        }

        if (! $cells->contains($b)) {
            $cells = $cells->with($b);
        }

        return $carry->with($key, $cells);
    }
}
