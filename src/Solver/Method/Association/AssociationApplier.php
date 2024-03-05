<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method\Association;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Association;
use Sudoku\Solver\Candidates;

final class AssociationApplier
{
    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return array{Map<FillableCell, Candidates>, bool} Tuple with updated $candidatesByCell map and a bool with true
     *     if the association led to a solution
     */
    public function apply(Map $candidatesByCell, Grid $grid, Association $association): array
    {
        $hasSolution = false;

        foreach ($association->getTargetedCells($grid) as $cell) {
            $previousCandidates = $candidatesByCell->get($cell);
            $candidates = $previousCandidates->withRemovedValues(...$association->getCandidatesToEliminate());
            $candidatesCount = $candidates->count();

            if ($candidatesCount === 1) {
                $hasSolution = true;
            }

            if ($previousCandidates->count() === $candidatesCount) {
                continue;
            }

            dump(sprintf('Update candidates %s => %s with association %s', $cell->coordinates->toString(), $candidates->toString(), $association->toString()));

            $candidatesByCell = $candidatesByCell->with($cell, $candidates);
        }

        return [$candidatesByCell, $hasSolution];
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     * @param iterable<Association> $associations
     *
     * @return Map<FillableCell, Candidates>
     */
    public function applyAll(Map $candidatesByCell, Grid $grid, iterable $associations): Map
    {
        foreach ($associations as $association) {
            [$candidatesByCell, $hasSolution] = $this->apply($candidatesByCell, $grid, $association);

            if ($hasSolution) {
                return $candidatesByCell;
            }
        }

        return $candidatesByCell;
    }
}
