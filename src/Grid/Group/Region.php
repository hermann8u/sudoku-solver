<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Group;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Group;

final readonly class Region extends Group
{
    public const WIDTH = 3;
    public const HEIGHT = 3;

    private function __construct(
        array $cells,
        public RegionNumber $number,
    ) {
        parent::__construct($cells);
    }

    /**
     * @param Cell[] $cells
     */
    public static function fromCells(array $cells, RegionNumber $number): self
    {
        return new self(
            array_values(array_filter($cells, static fn (Cell $cell) => $cell->regionNumber->value === $number->value)),
            $number,
        );
    }

    /**
     * @return RegionNumber[]
     */
    public function getRegionNumbersWithDirectImpact(): array
    {
        $regionX = $this->number->value % 3 ?: 3;
        $regionY = (int) ceil($this->number->value / 3);

        for ($x = $regionY * 3; $x > ($regionY - 1) * 3; $x--) {
            $regionNumbers[] = $x;
        }

        for ($y = 0; $y < 3; $y++) {
            $regionNumbers[] = $regionX + 3 * $y;
        }

        $regionNumbers = array_filter($regionNumbers, fn (int $value) => $value !== $this->number->value);

        sort($regionNumbers);

        return array_map(
            static fn (int $value) => new RegionNumber($value),
            array_values($regionNumbers),
        );
    }
}
