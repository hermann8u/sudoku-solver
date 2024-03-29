<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Association\PointingPair;
use Sudoku\Solver\Method;
use Sudoku\Solver\Method\Association\AssociationApplier;
use Sudoku\Solver\Method\Association\AssociationExtractor;

final class PointingPairMethod implements Method
{
    /**
     * @param AssociationExtractor<PointingPair> $extractor
     */
    public function __construct(
        private AssociationExtractor $extractor,
        private AssociationApplier $applier,
    ) {
    }

    public static function getName(): string
    {
        return 'pointing_pair';
    }

    /**
     * @inheritDoc
     */
    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map
    {
        return $this->applier->applyAll(
            $candidatesByCell,
            $grid,
            $this->extractor->getAssociationWithCell($candidatesByCell, $grid, $currentCell),
        );
    }
}
