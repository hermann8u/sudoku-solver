<?php

declare(strict_types=1);

namespace Sudoku\DataStructure;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;
use function count;
use function in_array;

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
     * @template UItem of mixed
     *
     * @param UItem $item
     * @param UItem ...$items
     *
     * @return self<UItem>
     */
    public static function fromItems(mixed $item, mixed ...$items): self
    {
        return new self([$item, ...$items]);
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
    public function reduce(callable $callable, mixed $initial): mixed
    {
        return array_reduce($this->items, $callable, $initial);
    }

    /**
     * @template TCarry of mixed
     *
     * @param callable(TCarry $carry, TItem $a, TItem $b): TCarry $callable
     * @param TCarry $carry
     *
     * @return TCarry
     */
    public function multidimensionalLoop(callable $callable, mixed $carry = []): mixed
    {
        $bItems = $this->items;

        foreach ($this->items as $aKey => $a) {
            unset($bItems[$aKey]);

            foreach ($bItems as $b) {
                $carry = $callable($carry, $a, $b);
            }
        }

        return $carry;
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
     * @param ?callable(TItem, TItem): int<-1, 1> $callable
     *
     * @return self<TItem>
     */
    public function sorted(?callable $callable = null): self
    {
        $items = $this->items;

        if ($callable !== null) {
            usort($items, $callable);
        } else {
            sort($items);
        }

        return new self($items);
    }

    /**
     * @param ?callable(TItem, TItem): bool $comparisonCallable Return true when the items are considered equals
     *
     * @return self<TItem>
     */
    public function unique(?callable $comparisonCallable = null): self
    {
        if ($comparisonCallable === null) {
            return new self(array_values(array_unique($this->items, SORT_REGULAR)));
        }

        $carry = $this;

        $newList = ArrayList::empty();

        foreach ($this->items as $item) {
            $previousCount = $carry->count();

            $carry = $carry->filter(static fn (mixed $other) => ! $comparisonCallable($item, $other));

            $filteredCount = $carry->count();

            if ($filteredCount !== $previousCount) {
                $newList = $newList->with($item);
            }

            if ($filteredCount === 0) {
                return $newList;
            }
        }

        return $newList;
    }

    /**
     * @param ArrayList<TItem> ...$others
     *
     * @return self<TItem>
     */
    public function intersect(ArrayList ...$others): self
    {
        if ($others === []) {
            return $this;
        }

        $intersect = array_intersect($this->items, ...array_map(
            static fn (ArrayList $other) => $other->items,
            $others,
        ));

        return new self(array_values($intersect));
    }

    /**
     * @param TItem $item
     */
    public function contains(mixed $item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * @param TItem ...$items
     *
     * @return self<TItem>
     */
    public function with(mixed ...$items): self
    {
        return new self([...$this->items, ...$items]);
    }

    /**
     * @param ArrayList<TItem> $other
     *
     * @return self<TItem>
     */
    public function merge(ArrayList $other): self
    {
        return new self([...$this->items, ...$other->items]);
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
     * @param positive-int $length
     * @param int<0, max> $offset
     *
     * @return self<TItem>
     */
    public function takes(int $length, int $offset = 0): self
    {
        return new self(array_slice($this->items, $offset, $length));
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->items);
    }

    public function implode(string $separator = ''): string
    {
        return implode(
            $separator,
            $this->map(static fn (mixed $item) => (string) $item)->items,
        );
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
