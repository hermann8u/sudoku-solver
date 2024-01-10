<?php

declare(strict_types=1);

namespace SudokuSolver\DataStructure;

use SudokuSolver\Comparable;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * A class that allows to map comparable value object to any value
 *
 * @template TKey of Comparable
 * @template TValue
 * @implements \IteratorAggregate<TKey, TValue>
 */
final readonly class Map implements \Countable, \IteratorAggregate
{
    /**
     * @param TKey[] $keys
     * @param TValue[] $values
     */
    private function __construct(
        private array $keys,
        private array $values,
    ) {
        Assert::same(\count($this->keys), \count($this->values));
    }

    public static function empty(): self
    {
        return new self([], []);
    }

    /**
     * @template UKey of Comparable
     * @template UValue of mixed
     *
     * @param array<array{UKey, UValue}> $tuples
     *
     * @return self<UKey, UValue>
     */
    public static function fromTuples(array $tuples): self
    {
        $keys = [];
        $values = [];

        foreach ($tuples as $tuple) {
            [$key, $value] = $tuple;

            $keys[] = $key;
            $values[] = $value;
        }

        return new self($keys, $values);
    }

    /**
     * @param TKey $key
     */
    public function has(Comparable $key): bool
    {
        try {
            $this->search($key);

            return true;
        } catch (\OutOfBoundsException) {
            return false;
        }
    }

    /**
     * @param TKey $key
     *
     * @return TValue
     *
     * @throws \OutOfBoundsException
     */
    public function get(Comparable $key): mixed
    {
        return $this->values[$this->search($key)];
    }

    /**
     * @param TKey $key
     * @param TValue $value
     *
     * @return Map<TKey, TValue>
     */
    public function with(Comparable $key, mixed $value): self
    {
        $keys = $this->keys;
        $values = $this->values;

        try {
            $index = $this->search($key);

            $keys[$index] = $key;
            $values[$index] = $value;
        } catch (\OutOfBoundsException){
            $keys[] = $key;
            $values[] = $value;
        }

        return new Map($keys, $values);
    }

    public function getIterator(): Traversable
    {
        return new MapIterator($this->keys, $this->values);
    }

    public function count(): int
    {
        return count($this->keys);
    }

    /**
     * @param TKey $offset
     *
     * @throws \OutOfBoundsException
     */
    private function search(Comparable $offset): int
    {
        foreach ($this->keys as $index => $key) {
            if ($offset->equals($key)) {
                return $index;
            }
        }

        throw new \OutOfBoundsException();
    }
}
