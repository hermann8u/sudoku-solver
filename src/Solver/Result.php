<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Grid;

final readonly class Result
{
    public string $realMemory;
    public bool $valid;
    public bool $filled;
    public bool $containsDuplicate;
    public int $cellToFill;
    public int $cellFilled;
    public int $remaining;

    /**
     * @param array<string, int> $methods
     */
    public function __construct(
        public int $iterationCount,
        public int $memory,
        public array $methods,
        private Grid $grid,
    ) {
        $this->realMemory = round($this->memory / 1024 / 1024, 5) . ' MiB';
        $this->valid = $this->grid->isValid();
        $this->filled = $this->grid->isFilled();
        $this->containsDuplicate = $this->grid->containsDuplicate();
        $this->cellToFill = count($this->grid->getFillableCells());
        $this->cellFilled = count(array_filter($grid->getFillableCells(), static fn (Cell $cell) => $cell->isEmpty() === false));
        $this->remaining = count(array_filter($grid->getFillableCells(), static fn (Cell $cell) => $cell->isEmpty()));
    }
}
