<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Sudoku\Grid;
use Sudoku\Grid\Cell;
use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Result\Step;

final readonly class Result
{
    public Grid $grid;
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
        Grid $grid,
    ) {
        foreach ($this->steps as $step) {
            $grid = $grid->withUpdatedCell($step->coordinates, $step->value);
        }

        $this->grid = $grid;
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

    public function getCellStepNumber(Coordinates $coordinates): ?int
    {
        foreach ($this->steps as $step) {
            if ($step->coordinates->equals($coordinates)) {
                return $step->number;
            }
        }

        return null;
    }

    public function getLastStep(): ?Step
    {
        $steps = $this->steps;

        return end($steps) ?: null;
    }
}
