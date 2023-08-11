<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\CellValue;
use SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

final class XWing
{
    /**
     * @param Coordinates[] $coordinatesList
     */
    public function __construct(
        public array $coordinatesList,
        public CellValue $value,
    ) {
        Assert::count($this->coordinatesList, 4);

        // TODO: Check coordinates
        Assert::count(array_merge(...array_map(static fn (Coordinates $c) => ['x' . $c->x => 0, 'y' . $c->y => 0], $this->coordinatesList)), 4);
    }

    /**
     * @param string[] $coordinatesStrings
     */
    public static function fromStrings(array $coordinatesStrings, int $value): static
    {
        return new static(
            array_map(
                static fn (string $coordinates) => Coordinates::fromString($coordinates),
                $coordinatesStrings,
            ),
            CellValue::from($value),
        );
    }

    public function contains(Cell $cell): bool
    {
        return in_array(
            $cell->coordinates->toString(),
            array_map(static fn (Coordinates $c) => (string) $c, $this->coordinatesList),
            true,
        );
    }

    public function toString(): string
    {
        return sprintf(
            '%d => %s',
            $this->value->value,
            implode(' ', array_map(static fn (Coordinates $c) => $c->toString(), $this->coordinatesList)),
        );
    }
}
