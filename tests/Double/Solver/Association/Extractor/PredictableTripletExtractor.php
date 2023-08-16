<?php

declare(strict_types=1);

namespace SudokuSolver\Tests\Double\Solver\Association\Extractor;

use SudokuSolver\Solver\Association\AssociationExtractor;
use SudokuSolver\Solver\Association\Pair;
use SudokuSolver\Solver\Association\Triplet;

/**
 * @implements AssociationExtractor<Triplet>
 */
final readonly class PredictableTripletExtractor implements AssociationExtractor
{
    use PredictableExtractorBehavior;

    public static function getAssociationType(): string
    {
        return Triplet::class;
    }
}
