<?php

declare(strict_types=1);

namespace Sudoku\Tests\Double\Solver\Association\Extractor;

use Sudoku\Solver\Association\AssociationExtractor;
use Sudoku\Solver\Association\Triplet;

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
