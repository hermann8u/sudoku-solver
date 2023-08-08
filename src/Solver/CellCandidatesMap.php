<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\CellValue;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;

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

    public function get(FillableCell|Coordinates|string $key): Candidates
    {
        $coordinatesString = $this->getCoordinatesString($key);

        if (! $this->has($coordinatesString)) {
            throw new \DomainException();
        }

        return $this->map[$coordinatesString];
    }

    public function has(FillableCell|Coordinates|string $key): bool
    {
        return isset($this->map[$this->getCoordinatesString($key)]);
    }

    public function merge(FillableCell $cell, Candidates $candidates): self
    {
        $map = $this->map;
        $map[$cell->coordinates->toString()] = $candidates;

        return new self($map);
    }

    public function filter(callable $filter): self
    {
        return new self(array_filter($this->map, $filter));
    }

    /**
     * @template T of mixed
     *
     * @param callable(self $map, T $carry, string $a, string $b): T $callable
     * @param T $carry
     *
     * @return T
     */
    public function multidimensionalKeyLoop(callable $callable, mixed $carry = []): mixed
    {
        $keys = array_keys($this->map);

        foreach ($keys as $a) {
            foreach ($keys as $b) {
                if ($a === $b) {
                    continue;
                }

                $carry = $callable($this, $carry, $a, $b);
            }
        }

        return $carry;
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

    private function getCoordinatesString(Cell|Coordinates|string $key): string
    {
        return match (true) {
            $key instanceof Cell => $key->coordinates->toString(),
            $key instanceof Coordinates => $key->toString(),
            default => $key,
        };
    }
}
