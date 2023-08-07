<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Association\Extractor;

use Florian\SudokuSolver\Solver\Association\Triplet;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Association\AssociationExtractor;

/**
 * @implements AssociationExtractor<Triplet>
 */
final class TripletExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        $mapForGroup = $mapForGroup->filtered(static fn (Candidates $c) => $c->count() < 4);

        foreach ($mapForGroup as $coordinates => $candidates) {
            foreach ($mapForGroup as $otherCoordinates => $otherCandidates) {
                if ($coordinates === $otherCoordinates) {
                    continue;
                }

                [$candidatesWithBiggerCount, $candidatesWithSmallerCount] = $this->order($candidates, $otherCandidates);

                if ($candidatesWithBiggerCount->count() < 3) {
                    continue;
                }

                if (! $candidatesWithBiggerCount->contains($candidatesWithSmallerCount)) {
                    continue;
                }

                $key = $candidatesWithBiggerCount->toString();

                $coordinatesByCandidates[$key][] = $coordinates;
                $coordinatesByCandidates[$key][] = $otherCoordinates;

                $coordinatesByCandidates[$key] = array_unique($coordinatesByCandidates[$key]);
            }
        }

        foreach ($coordinatesByCandidates ?? [] as $valuesString => $coordinatesTriplet) {
            if (count($coordinatesTriplet) !== 3) {
                continue;
            }

            $triplets[] = Triplet::fromStrings($coordinatesTriplet, $valuesString);
        }

        return $triplets ?? [];
    }

    public static function getAssociationType(): string
    {
        return Triplet::class;
    }

    /**
     * @param Candidates $candidates
     * @param Candidates $otherCandidates
     *
     * @return array{Candidates, Candidates}
     */
    private function order(Candidates $candidates, Candidates $otherCandidates): array
    {
        $v = [$candidates, $otherCandidates];
        usort($v, static fn (Candidates $a, Candidates $b) => -1 * ($a->count() <=> $b->count()));

        return $v;
    }
}
