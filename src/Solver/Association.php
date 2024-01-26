<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid\Cell;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Webmozart\Assert\Assert;

abstract readonly class Association
{
    /**
     * @param ArrayList<FillableCell> $cells
     */
    public function __construct(
        public Group $group,
        public Candidates $candidates,
        public ArrayList $cells,
    ) {
        Assert::count($this->cells, $this->getAssociationCount());
        Assert::same($this->candidates->count(), $this->getAssociationCount());
    }

    public function toString(): string
    {
        return sprintf(
            '%s => %s',
            $this->candidates->toString(),
            $this->cells
                ->map(static fn (FillableCell $c) => $c->coordinates->toString())
                ->implode(' '),
        );
    }

    public function contains(Cell $other): bool
    {
        return $this->cells->exists(static fn (FillableCell $cell) => $cell->is($other));
    }

    /**
     * @return positive-int
     */
    abstract public static function getAssociationCount(): int;
}
