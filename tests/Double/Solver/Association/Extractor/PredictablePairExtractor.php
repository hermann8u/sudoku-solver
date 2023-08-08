<?php

declare(strict_types=1);

namespace SudokuSolver\Tests\Double\Solver\Association\Extractor;

use SudokuSolver\Solver\Association\AssociationExtractor;
use SudokuSolver\Solver\Association\Pair;

/**
 * @implements AssociationExtractor<Pair>
 */
final class PredictablePairExtractor implements AssociationExtractor
{
    use PredictableExtractorBehavior;

    public static function getAssociationType(): string
    {
        return Pair::class;
    }
}
