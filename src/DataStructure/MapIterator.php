<?php

declare(strict_types=1);

namespace Sudoku\DataStructure;

use Iterator;
use Webmozart\Assert\Assert;
use function count;

/**
 * @template TKey of object
 * @template TValue of mixed
 *
 * @implements Iterator<TKey, TValue>
 */
final class MapIterator implements Iterator
{
    private int $cursor;

    /**
     * @param TKey[] $keys
     * @param TValue[] $values
     */
    public function __construct(
        private readonly array $keys,
        private readonly array $values,
    ) {
        Assert::same(count($this->keys), count($this->values));

        $this->cursor = 0;
    }

    public function current(): mixed
    {
        return $this->values[$this->cursor];
    }

    public function next(): void
    {
        $this->cursor++;
    }

    public function key(): mixed
    {
        return $this->keys[$this->cursor];
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->cursor]);
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }
}
