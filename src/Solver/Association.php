<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\FillableCell;
use Webmozart\Assert\Assert;

abstract readonly class Association
{
    /**
     * @param FillableCell[] $cells
     */
    public function __construct(
        public array $cells,
        public Candidates $candidates,
    ) {
        Assert::count($this->cells, $this->getAssociationCount());
        Assert::same($this->candidates->count(), $this->getAssociationCount());
    }

    public function toString(): string
    {
        return sprintf(
            '%s => %s',
            $this->candidates->toString(),
            implode(' ', array_map(static fn (FillableCell $c) => $c->coordinates->toString(), $this->cells)),
        );
    }

    public function contains(Cell $other): bool
    {
        foreach ($this->cells as $cell) {
            if ($cell->is($other)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return positive-int
     */
    abstract public static function getAssociationCount(): int;
}
