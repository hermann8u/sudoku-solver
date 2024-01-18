<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Result\Step;

final readonly class Result
{
    public int $memory;
    public string $realMemory;
    public bool $valid;
    public bool $filled;
    public bool $containsDuplicate;
    public int $cellToFill;
    public int $filledCells;
    public int $remainingCells;

    /**
     * @param Step[] $steps
     */
    public function __construct(
        public array $steps,
        public Grid $grid,
    ) {
        $this->memory = memory_get_peak_usage();
        $this->realMemory = round($this->memory / 1024 / 1024, 5) . ' MiB';
        $this->valid = $this->grid->isValid();
        $this->filled = $this->grid->isFilled();
        $this->containsDuplicate = $this->grid->containsDuplicate();
        $this->cellToFill = $this->grid
            ->cells
            ->filter(static fn (Cell $cell) => $cell instanceof FillableCell)
            ->count();
        $this->remainingCells = $this->grid->getEmptyCells()->count();
        $this->filledCells = $this->cellToFill - $this->remainingCells;
    }

    public function getCellStep(Coordinates $coordinates): ?Step
    {
        foreach ($this->steps as $step) {
            if ($step->coordinates->equals($coordinates)) {
                return $step;
            }
        }

        return null;
    }
}
