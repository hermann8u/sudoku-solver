<?php

declare(strict_types=1);

namespace Sudoku\DataStructure;

use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;
use Webmozart\Assert\Assert;
use function count;

/**
 * A class that allows to map object to any value
 *
 * @template TKey of object
 * @template TValue of mixed
 *
 * @implements IteratorAggregate<TKey, TValue>
 */
final readonly class Map implements Countable, IteratorAggregate
{
    /**
     * @param TKey[] $keys
     * @param TValue[] $values
     */
    private function __construct(
        private array $keys,
        private array $values,
    ) {
        Assert::same(count($this->keys), count($this->values));
    }

    public static function empty(): self
    {
        return new self([], []);
    }

    /**
     * @template UKey of object
     * @template UValue of mixed
     *
     * @param list<array{UKey, UValue}> $tuples
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
     * @return ArrayList<TKey>
     */
    public function keys(): ArrayList
    {
        return ArrayList::fromList($this->keys);
    }

    /**
     * @return ArrayList<TValue>
     */
    public function values(): ArrayList
    {
        return ArrayList::fromList($this->values);
    }

    /**
     * @param TKey $key
     */
    public function has(object $key): bool
    {
        try {
            $this->search($key);

            return true;
        } catch (OutOfBoundsException) {
            return false;
        }
    }

    /**
     * @param TKey $key
     *
     * @return TValue
     *
     * @throws OutOfBoundsException
     */
    public function get(object $key): mixed
    {
        return $this->values[$this->search($key)];
    }

    /**
     * @param TKey $key
     * @param TValue $value
     *
     * @return Map<TKey, TValue>
     */
    public function with(object $key, mixed $value): self
    {
        $keys = $this->keys;
        $values = $this->values;

        try {
            $index = $this->search($key);

            $keys[$index] = $key;
            $values[$index] = $value;
        } catch (OutOfBoundsException){
            $keys[] = $key;
            $values[] = $value;
        }

        return new Map($keys, $values);
    }

    /**
     * @param TKey $key
     * @param TKey ...$keys
     *
     * @return Map<TKey, TValue>
     */
    public function without(object $key, object ...$keys): self
    {
        $keys = [$key, ...$keys];

        $k = $this->keys;
        $v = $this->values;

        foreach ($keys as $key) {
            try {
                $index = $this->search($key);

                unset($k[$index]);
                unset($v[$index]);
            } catch (OutOfBoundsException) {
            }
        }

        return new Map(array_values($k), array_values($v));
    }

    public function getIterator(): Traversable
    {
        return new MapIterator($this->keys, $this->values);
    }

    public function isEmpty(): bool
    {
        return $this->keys === [];
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->keys);
    }

    /**
     * @param TKey $offset
     *
     * @throws OutOfBoundsException
     */
    private function search(object $offset): int
    {
        /** @var callable(TKey): bool $comparisonCallable */
        $comparisonCallable = match (true) {
            $offset instanceof Equable => $offset->equals(...),
            default => static fn (object $key) => $offset === $key,
        };

        foreach ($this->keys as $index => $key) {
            if ($comparisonCallable($key)) {
                return $index;
            }
        }

        throw new OutOfBoundsException();
    }
}
