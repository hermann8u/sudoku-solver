<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association\Extractor;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association\AssociationExtractor;
use Sudoku\Solver\Association\Pair;
use Sudoku\Solver\Candidates;

final class HiddenPairExtractor implements AssociationExtractor
{
    /**
     * @inheritdoc
     */
    public function getAssociationsInGroup(Map $candidatesByCell, Group $group): iterable
    {
        $groupCells = $group->getEmptyCells();

        /** @var Map<Candidates, ArrayList<FillableCell>> $cellsByCandidatesPairs */
        $cellsByCandidatesPairs = $groupCells->multidimensionalLoop(
            fn (Map $carry, FillableCell $a, FillableCell $b) => $this->groupCellsByCandidatesPair(
                $candidatesByCell,
                $carry,
                $a,
                $b,
            ),
            Map::empty(),
        );

        foreach ($cellsByCandidatesPairs as $candidates => $cells) {
            if ($cells->count() !== Pair::COUNT) {
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

            yield new Pair($group, $candidates, $cells);
        }
    }

    public static function getAssociationType(): string
    {
        return Pair::class;
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
        FillableCell $a,
        FillableCell $b,
    ): Map {

        $candidates = $candidatesByCell->get($a)->intersect($candidatesByCell->get($b));

        if ($candidates->count() < Pair::COUNT) {
            return $carry;
        }

        /** @var ArrayList<Candidates> $candidatesPairs */
        $candidatesPairs = $candidates->values->multidimensionalLoop(
            static fn (ArrayList $candidatesPairs, Value $a, Value $b) => $candidatesPairs->with(Candidates::fromValues($a, $b)),
            ArrayList::empty(),
        );

        foreach ($candidatesPairs as $candidatesPair) {
            $carry = $this->fillCellsByCandidatesPair($carry, $candidatesPair, $a, $b);
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
