<?php

declare(strict_types=1);

namespace SudokuSolver\Tests\Double\Solver\Association\Extractor;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\Grid\Group;
use SudokuSolver\Solver\Association;
use SudokuSolver\Solver\CellCandidatesMap;

trait PredictableExtractorBehavior
{
    /**
     * @param ArrayList<Association> $associations
     */
    public function __construct(
        private readonly ArrayList $associations,
    ) {
    }

    public function getAssociationsInGroup(CellCandidatesMap $map, Group $group): ArrayList
    {
        return $this->associations;
    }
}
