<?php

declare(strict_types=1);

namespace Sudoku\Tests\Double\Solver\Association\Extractor;

use Sudoku\Solver\Method\Association\AssociationExtractor;
use Sudoku\Solver\Association\Naked\Pair;

/**
 * @implements AssociationExtractor<Pair>
 */
final readonly class PredictablePairExtractor implements AssociationExtractor
{
    use PredictableExtractorBehavior;

    public static function getAssociationType(): string
    {
        return Pair::class;
    }
}
