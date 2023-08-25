<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
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
        public int $iterationCount,
        public array $steps,
        public CellCandidatesMap $map,
        public Grid $grid,
    ) {
        $this->memory = memory_get_peak_usage();
        $this->realMemory = round($this->memory / 1024 / 1024, 5) . ' MiB';
        $this->valid = $this->grid->isValid();
        $this->filled = $this->grid->isFilled();
        $this->containsDuplicate = $this->grid->containsDuplicate();
        $this->cellToFill = count($this->grid->getFillableCells());
        $this->remainingCells = count($this->grid->getEmptyCells());
        $this->filledCells = $this->cellToFill - $this->remainingCells;
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
