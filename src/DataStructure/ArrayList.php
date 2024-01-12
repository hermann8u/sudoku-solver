<?php

declare(strict_types=1);

namespace SudokuSolver\DataStructure;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;
use function count;

/**
 * @template TItem of mixed
 *
 * @implements IteratorAggregate<int, TItem>
 */
final readonly class ArrayList implements Countable, IteratorAggregate
{
    /**
     * @param TItem[] $items
     */
    private function __construct(
        private array $items,
    ) {
        Assert::isList($this->items);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @template UItem of mixed
     *
     * @param UItem[] $list
     *
     * @return self<UItem>
     */
    public static function fromList(array $list): self
    {
        return new self($list);
    }

    /**
     * @template UItem
     *
     * @param callable(TItem): UItem $callable
     *
     * @return self<UItem>
     */
    public function map(callable $callable): self
    {
        return new self(array_map($callable, $this->items));
    }

    /**
     * @param callable(TItem): bool $callable
     *
     * @return self<TItem>
     */
    public function filter(callable $callable): self
    {
        return new self(array_values(array_filter($this->items, $callable)));
    }

    /**
     * @template U of mixed
     *
     * @param callable(U, TItem): U $callable
     * @param U $initial
     *
     * @return U
     */
    public function reduce(callable $callable, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callable, $initial);
    }

    /**
     * @param callable(TItem): bool $callable
     *
     * @return ?TItem
     */
    public function findFirst(callable $callable): mixed
    {
        foreach ($this->items as $item) {
            if ($callable($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Tests for the existence of an item that satisfies the given predicate.
     *
     * @param callable(TItem): bool $callable
     */
    public function exists(callable $callable): bool
    {
        foreach ($this->items as $item) {
            if ($callable($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable(TItem, TItem): int<-1, 1> $callable
     *
     * @return self<TItem>
     */
    public function sorted(callable $callable): self
    {
        $items = $this->items;
        usort($items, $callable);

        return new self($items);
    }

    /**
     * @param ?callable(TItem, TItem): bool $comparisonCallable
     *
     * @return self<TItem>
     */
    public function unique(callable $comparisonCallable = null): self
    {
        if ($comparisonCallable === null) {
            return new self(array_values(array_unique($this->items)));
        }

        $carry = $this;

        $newList = ArrayList::empty();

        foreach ($this->items as $item) {
            $previousCount = $carry->count();

            $carry = $carry->filter(static fn (mixed $other) => ! $comparisonCallable($item, $other));

            $filteredCount = $carry->count();

            if ($filteredCount !== $previousCount) {
                $newList = $newList->merge($item);
            }

            if ($filteredCount === 0) {
                return $newList;
            }
        }

        return $newList;
    }

    /**
     * @param TItem $item
     */
    public function contains(mixed $item): bool
    {
        return \in_array($item, $this->items, true);
    }

    /**
     * @param TItem ...$items
     *
     * @return self<TItem>
     */
    public function merge(mixed ...$items): self
    {
        return new self([...$this->items, ...$items]);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * @return TItem
     */
    public function first(): mixed
    {
        if ($this->isEmpty()) {
            throw new \OutOfBoundsException();
        }

        $items = $this->items;

        /** @var TItem $item */
        $item = reset($items);

        return $item;
    }

    /**
     * @return TItem
     */
    public function last(): mixed
    {
        if ($this->isEmpty()) {
            throw new \OutOfBoundsException();
        }

        $items = $this->items;

        /** @var TItem $item */
        $item = end($items);

        return $item;
    }

    /**
     * @return self<TItem>
     */
    public function takes(int $length): self
    {
        Assert::positiveInteger($length);

        return new self(array_slice($this->items, 0, $length));
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return TItem[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
