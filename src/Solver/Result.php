<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Result\Step;

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
     * @param Step[] $steps
     */
    public function __construct(
        public int $iterationCount,
        public int $memory,
        public array $steps,
        public CellCandidatesMap $map,
        public Grid $grid,
    ) {
        $this->realMemory = round($this->memory / 1024 / 1024, 5) . ' MiB';
        $this->valid = $this->grid->isValid();
        $this->filled = $this->grid->isFilled();
        $this->containsDuplicate = $this->grid->containsDuplicate();
        $this->cellToFill = count($this->grid->getFillableCells());
        $this->cellFilled = count(array_filter($grid->getFillableCells(), static fn (Cell $cell) => $cell->isEmpty() === false));
        $this->remaining = count(array_filter($grid->getFillableCells(), static fn (Cell $cell) => $cell->isEmpty()));
    }

    public function getStep(Coordinates $coordinates): ?Step
    {
        foreach ($this->steps as $step) {
            if ($step->coordinates->toString() === $coordinates->toString()) {
                return $step;
            }
        }

        return null;
    }
}
