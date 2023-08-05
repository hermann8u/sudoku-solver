<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

final readonly class Pair
{
    /**
     * @param Coordinates[] $coordinatesPair
     */
    public function __construct(
        public array $coordinatesPair,
        public Candidates $candidates,
    ) {
        Assert::count($this->coordinatesPair, 2);
        Assert::count($this->candidates->values, 2);
    }
}
