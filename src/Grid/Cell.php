<?php

declare(strict_types=1);

namespace Sudoku\Grid;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group\GroupNumber;
use Sudoku\Grid\Group\Number\ColumnNumber;
use Sudoku\Grid\Group\Number\RegionNumber;
use Sudoku\Grid\Group\Number\RowNumber;

abstract readonly class Cell
{
    public function __construct(
        public Coordinates $coordinates,
        public ?Value $value = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->value === null;
    }

    public function is(Cell $cell): bool
    {
        return $this->coordinates->equals($cell->coordinates);
    }

    public function isNot(Cell $cell): bool
    {
        return ! $this->is($cell);
    }

    public function hasCommonGroupWith(Cell $other): bool
    {
        return $this->hasGroupNumberIn($other->getGroupNumbers());
    }

    /**
     * @param ArrayList<GroupNumber> $groupNumbers
     */
    public function hasGroupNumberIn(ArrayList $groupNumbers): bool
    {
        return $this->getGroupNumbers()->exists(
            static fn (GroupNumber $a) => $groupNumbers->exists(
                static fn (GroupNumber $b) => $a::class === $b::class && $a->equals($b),
            ),
        );
    }

    /**
     * @return ArrayList<GroupNumber>
     */
    public function getGroupNumbers(): ArrayList
    {
        /** @var ArrayList<GroupNumber> */
        return ArrayList::fromItems(
            $this->getColumnNumber(),
            $this->getRowNumber(),
            $this->getRegionNumber(),
        );
    }

    public function getColumnNumber(): ColumnNumber
    {
        return ColumnNumber::fromCell($this);
    }

    public function getRowNumber(): RowNumber
    {
        return RowNumber::fromCell($this);
    }

    public function getRegionNumber(): RegionNumber
    {
        return RegionNumber::fromCell($this);
    }
}
