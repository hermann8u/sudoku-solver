<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group\Column;
use Sudoku\Grid\Group\GroupNumber;
use Sudoku\Grid\Group\Number\ColumnNumber;
use Sudoku\Grid\Group\Number\RowNumber;
use Sudoku\Grid\Group\Row;
use Sudoku\Solver\XWing\Direction;
use Webmozart\Assert\Assert;

final readonly class XWing
{
    /** @var ArrayList<ColumnNumber> */
    public ArrayList $columnNumbers;
    /** @var ArrayList<RowNumber> */
    public ArrayList $rowNumbers;

    /**
     * @param ArrayList<FillableCell> $cells
     */
    public function __construct(
        public Direction $direction,
        public ArrayList $cells,
        public Value $value,
    ) {
        Assert::count($this->cells, 4);

        $uniqueCallable = static fn (GroupNumber $a, GroupNumber $b) => $a->value === $b->value;

        $this->columnNumbers = $this->cells
            ->map(ColumnNumber::fromCell(...))
            ->unique($uniqueCallable);

        $this->rowNumbers = $this->cells
            ->map(RowNumber::fromCell(...))
            ->unique($uniqueCallable);

        Assert::count($this->columnNumbers, 2);
        Assert::count($this->rowNumbers, 2);
    }

    public function contains(FillableCell $cell): bool
    {
        return $this->cells->exists($cell->is(...));
    }

    /**
     * @return ArrayList<Column>|ArrayList<Row>
     */
    public function getGroupsToModify(Grid $grid): ArrayList
    {
        return match ($this->direction) {
            Direction::Horizontal => $this->columnNumbers->map(
                static fn (ColumnNumber $n) => $grid->columns->get($n),
            ),
            Direction::Vertical => $this->rowNumbers->map(
                static fn (RowNumber $n) => $grid->rows->get($n),
            ),
        };
    }

    public function toString(): string
    {
        return sprintf(
            '%s => %d => %s',
            $this->direction->name,
            $this->value->value,
            $this->cells
                ->map(static fn (FillableCell $c) => $c->coordinates->toString())
                ->implode(' '),
        );
    }
}
