<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Cell\Value;

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

    public function isEmpty(): bool
    {
        return $this->map->count() === 0;
    }

    public function get(FillableCell $cell): Candidates
    {
        return $this->map[$cell] ?? throw new \DomainException();
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

    public function filter(callable $filter): self
    {
        /** @var \WeakMap<FillableCell, Candidates> $filtered */
        $filtered = new \WeakMap();

        foreach ($this->map as $cell => $candidates) {
            if ($filter($candidates, $cell)) {
                $filtered[$cell] = $candidates;
            }
        }

        return new self($filtered);
    }

    /**
     * @template T of mixed
     *
     * @param callable(self $map, T $carry, FillableCell $a, FillableCell $b): T $callable
     * @param T $carry
     *
     * @return T
     */
    public function multidimensionalLoop(callable $callable, mixed $carry = []): mixed
    {
        $bMap = clone $this->map;

        foreach ($this->map as $a => $candidatesA) {
            unset($bMap[$a]);

            foreach ($bMap as $b => $candidatesB) {
                $carry = $callable($this, $carry, $a, $b);
            }
        }

        return $carry;
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
