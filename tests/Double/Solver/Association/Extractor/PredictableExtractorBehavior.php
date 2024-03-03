<?php

declare(strict_types=1);

namespace Sudoku\Tests\Double\Solver\Association\Extractor;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Association\NakedAssociation;

trait PredictableExtractorBehavior
{
    /**
     * @param ArrayList<NakedAssociation> $associations
     */
    public function __construct(
        private readonly ArrayList $associations,
    ) {
    }

    public function getAssociationWithCell(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): iterable
    {
        return $this->associations;
    }
}
