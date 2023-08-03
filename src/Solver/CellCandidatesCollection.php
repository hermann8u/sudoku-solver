<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Cell\FillableCell;

final class CellCandidatesCollection
{
    /**
     * @var array<string, Candidates>
     */
    public array $values;

    public function __construct()
    {
        $this->values = [];
    }

    public function reset(): void
    {
        $this->values = [];
    }

    public function get(FillableCell $cell): Candidates
    {
        if (! $this->has($cell)) {
            throw new \DomainException();
        }

        return $this->values[$cell->coordinates->toString()];
    }

    public function has(Cell $cell): bool
    {
        return isset($this->values[$cell->coordinates->toString()]);
    }

    public function add(FillableCell $cell, Candidates $candidates): void
    {
        if ($this->has($cell)) {
            return;
        }

        $this->values[$cell->coordinates->toString()] = $candidates;
    }
}
