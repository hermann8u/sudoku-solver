<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Cell\CellValue;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Cell\FillableCell;

/**
 * @implements \IteratorAggregate<string, Candidates>
 */
final readonly class CellCandidatesMap implements \IteratorAggregate
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

    public function isEmpty(): bool
    {
        return $this->map === [];
    }

    public function isSame(CellCandidatesMap $otherMap): bool
    {
        return array_diff($this->display(), $otherMap->display()) === [];
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

    public function filtered(callable $filter): self
    {
        return new self(array_filter($this->map, $filter));
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

    /**
     * @return array<string, string>
     */
    public function display(): array
    {
        $map = $this->map;
        ksort($map);

        return array_map(
            static fn (Candidates $candidates) => $candidates->toString(),
            $map,
        );
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->map);
    }
}
