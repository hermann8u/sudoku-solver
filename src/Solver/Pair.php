<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell;
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

    public function match(Cell $cell): bool
    {
        return in_array(
            $cell->coordinates->toString(),
            array_map(static fn (Coordinates $c) => (string) $c, $this->coordinatesPair),
            true,
        );
    }
}
