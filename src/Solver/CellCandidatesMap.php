<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;

/**
 * @implements \IteratorAggregate<FillableCell, Candidates>
 */
final readonly class CellCandidatesMap implements \IteratorAggregate
{
    /**
     * @param \WeakMap<FillableCell, Candidates> $map
     */
    private function __construct(
        private \WeakMap $map,
    ) {
    }

    public static function empty(): self
    {
        /** @var \WeakMap<FillableCell, Candidates> $map */
        $map = new \WeakMap();

        return new self($map);
    }

    public function get(FillableCell $cell): Candidates
    {
        return $this->map[$cell] ?? throw new \OutOfBoundsException();
    }

    public function has(FillableCell $cell): bool
    {
        return $this->map->offsetExists($cell);
    }

    public function with(FillableCell $cell, Candidates $candidates): self
    {
        $map = clone $this->map;
        $map[$cell] = $candidates;

        return new self($map);
    }

    /**
     * @return ?array{FillableCell, Value}
     */
    public function findFirstUniqueCandidate(): ?array
    {
        foreach ($this->map as $cell => $candidates) {
            if ($candidates->hasUniqueCandidate()) {
                return [$cell, $candidates->first()];
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    public function display(): array
    {
        $data = [];

        foreach ($this->map as $cell => $candidates) {
            $data[$cell->coordinates->toString()] = $candidates->toString();
        }

        ksort($data);

        return $data;
    }

    public function getIterator(): \Traversable
    {
        return $this->map;
    }
}
