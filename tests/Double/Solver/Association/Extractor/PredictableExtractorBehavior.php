<?php

declare(strict_types=1);

namespace SudokuSolver\Tests\Double\Solver\Association\Extractor;

use SudokuSolver\Solver\Association;
use SudokuSolver\Solver\CellCandidatesMap;

trait PredictableExtractorBehavior
{
    /**
     * @param Association[] $associations
     */
    public function __construct(
        private readonly array $associations,
    ) {
    }

    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        return $this->associations;
    }
}
