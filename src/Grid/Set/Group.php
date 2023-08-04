<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Set;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Set;

final readonly class Group extends Set
{
    public const WIDTH = 3;
    public const HEIGHT = 3;

    private function __construct(
        array $cells,
        public GroupNumber $number,
    ) {
        parent::__construct($cells);
    }

    /**
     * @param Cell[] $cells
     */
    public static function fromCells(array $cells, GroupNumber $number): self
    {
        return new self(
            array_values(array_filter($cells, static fn (Cell $cell) => $cell->groupNumber->value === $number->value)),
            $number,
        );
    }

    /**
     * @return GroupNumber[]
     */
    public function getGroupNumbersWithDirectImpact(): array
    {
        $groupX = $this->number->value % 3 ?: 3;
        $groupY = (int) ceil($this->number->value / 3);

        for ($x = $groupY * 3; $x > ($groupY - 1) * 3; $x--) {
            $groupNumbers[] = $x;
        }

        for ($y = 0; $y < 3; $y++) {
            $groupNumbers[] = $groupX + 3 * $y;
        }

        $groupNumbers = array_filter($groupNumbers, fn (int $value) => $value !== $this->number->value);

        sort($groupNumbers);

        return array_map(
            static fn (int $value) => new GroupNumber($value),
            array_values($groupNumbers),
        );
    }
}
