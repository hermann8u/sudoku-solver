<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Association\XWing;
use Sudoku\Solver\Method;
use Sudoku\Solver\Method\Association\AssociationApplier;
use Sudoku\Solver\Method\Association\AssociationExtractor;

final readonly class XWingMethod implements Method
{
    /**
     * @param AssociationExtractor<XWing> $extractor
     */
    public function __construct(
        private AssociationExtractor $extractor,
        private AssociationApplier $applier,
    ) {
    }

    public static function getName(): string
    {
        return 'x_wing';
    }

    /**
     * @inheritdoc
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
