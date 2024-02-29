<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Result\Step;

final readonly class Result
{
    public Grid $grid;
    public int $memory;
    public string $realMemory;
    public bool $solved;
    public bool $containsDuplicate;
    public int $cellToFill;
    public int $filledCells;
    public int $remainingCells;

    /**
     * @param ArrayList<Step> $steps
     */
    public function __construct(
        Grid $grid,
        public ArrayList $steps,
    ) {
        foreach ($this->steps as $step) {
            if ($step->solution === null) {
                break;
            }

            $grid = $grid->withUpdatedCell($step->solution->cell->coordinates, $step->solution->value);
        }

        $this->grid = $grid;
        $this->memory = memory_get_peak_usage();
        $this->realMemory = round($this->memory / 1024 / 1024, 5) . ' MiB';
        $this->solved = $this->grid->isSolved();
        $this->containsDuplicate = $this->grid->containsDuplicate();
        $this->cellToFill = $this->grid
            ->cells
            ->filter(static fn (Cell $cell) => $cell instanceof FillableCell)
            ->count();
        $this->remainingCells = $this->grid->getEmptyCells()->count();
        $this->filledCells = $this->cellToFill - $this->remainingCells;
    }

    public function getStepNumberForCell(Cell $cell): ?int
    {
        return $this->steps
            ->findFirst(static fn (Step $step) => $step->solution !== null
                && $cell->coordinates->equals($step->solution->cell->coordinates)
            )
            ?->number;
    }

    public function isSolved(): bool
    {
        return $this->steps->last()->solution !== null;
    }

    /**
     * @return Map<FillableCell, Candidates>
     */
    public function getCandidatesByCell(): Map
    {
        return $this->steps->last()->candidatesByCell ?? Map::empty();
    }

    public function hasNoSolution(): bool
    {
        return $this->getCandidatesByCell()->values()->exists(static fn (Candidates $c) => $c->count() === 0);
    }
}
