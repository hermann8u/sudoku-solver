<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Cell\CellValue;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Cell\FillableCell;

final readonly class CellCandidatesMap
{
    /**
     * @param array<string, Candidates> $map
     */
    public function __construct(
        private array $map,
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function get(FillableCell $cell): Candidates
    {
        if (! $this->has($cell)) {
            throw new \DomainException();
        }

        return $this->map[$cell->coordinates->toString()];
    }

    public function has(Cell $cell): bool
    {
        return isset($this->map[$cell->coordinates->toString()]);
    }

    public function merge(FillableCell $cell, Candidates $candidates): self
    {
        $map = $this->map;
        $map[$cell->coordinates->toString()] = $candidates;

        return new self($map);
    }

    /**
     * @return array{?Coordinates, ?CellValue}
     */
    public function findUniqueValue(): array
    {
        foreach ($this->map as $coordinateAsString => $candidates) {
            if ($candidates->hasUniqueValue()) {
                return [Coordinates::fromString($coordinateAsString), $candidates->first()];
            }
        }

        return [null, null];
    }

    public function display(): array
    {
        $map = $this->map;
        ksort($map);

        return array_map(
            static function (Candidates $candidates) {
                $integers = $candidates->toIntegers();
                sort($integers);

                return implode(',', $integers);
            },
            $map,
        );
    }
}
