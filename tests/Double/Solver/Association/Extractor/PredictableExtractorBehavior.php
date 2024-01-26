<?php

declare(strict_types=1);

namespace Sudoku\Tests\Double\Solver\Association\Extractor;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association;

trait PredictableExtractorBehavior
{
    /**
     * @param ArrayList<Association> $associations
     */
    public function __construct(
        private readonly ArrayList $associations,
    ) {
    }

    public function getAssociationsInGroup(Map $candidatesByCell, Group $group): ArrayList
    {
        return $this->associations;
    }
}
