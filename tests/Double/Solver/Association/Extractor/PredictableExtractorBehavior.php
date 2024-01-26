<?php

declare(strict_types=1);

namespace Sudoku\Tests\Double\Solver\Association\Extractor;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association;
use Sudoku\Solver\CellCandidatesMap;

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
