<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Association\HiddenPair;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method;
use Sudoku\Solver\Method\Association\AssociationApplier;
use Sudoku\Solver\Method\Association\AssociationExtractor;

final readonly class HiddenPairMethod implements Method
{
    /**
     * @param AssociationExtractor<HiddenPair> $extractor
     */
    public function __construct(
        private AssociationExtractor $extractor,
        private AssociationApplier $applier,
    ) {
    }

    public static function getName(): string
    {
        return 'hidden_pair_method';
    }

    /**
     * @inheritdoc
     */
    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map
    {
        $associations = $this->extractor->getAssociationWithCell($candidatesByCell, $grid, $currentCell);

        foreach ($associations as $hiddenPair) {
            foreach ($hiddenPair->cells as $cell) {
                $candidatesByCell = $candidatesByCell->with($cell, Candidates::fromValues(...$hiddenPair->values));
            }

            [$candidatesByCell, $hasSolution] = $this->applier->apply($candidatesByCell, $grid, $hiddenPair);

            if ($hasSolution) {
                return $candidatesByCell;
            }
        }

        return $candidatesByCell;
    }
}
