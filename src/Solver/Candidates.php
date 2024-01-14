<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Comparable;
use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\Grid\Cell\Value;

/**
 * @implements Comparable<Candidates>
 */
final readonly class Candidates implements Comparable, \Stringable
{
    /** @var ArrayList<Value> */
    public ArrayList $values;

    /**
     * @param ArrayList<Value> $values
     */
    private function __construct(ArrayList $values)
    {
        $this->values = $values->sorted();
    }

    public static function all(): self
    {
        /** @var ArrayList<int<Value::MIN, Value::MAX>> $allIntegers */
        $allIntegers = ArrayList::fromList(range(Value::MIN, Value::MAX));

        return self::fromIntegers($allIntegers);
    }

    public static function fromString(string $valuesString): self
    {
        $valuesStrings = explode(',', $valuesString);

        /** @var ArrayList<int<Value::MIN, Value::MAX>> $values */
        $values = ArrayList::fromList($valuesStrings)->map(static fn (string $v) => (int) $v);

        return self::fromIntegers($values);
    }

    public static function empty(): self
    {
        return new self(ArrayList::empty());
    }

    public function hasUniqueCandidate(): bool
    {
        return $this->values->count() === 1;
    }

    public function first(): Value
    {
        return $this->values->first();
    }

    public function count(): int
    {
        return $this->values->count();
    }

    public function intersect(Candidates ...$others): self
    {
        /**
         * @var ArrayList<ArrayList<int<Value::MIN, Value::MAX>>> $otherValues
         * @phpstan-ignore-next-line
         */
        $otherValues = ArrayList::fromList($others)->map(static fn (Candidates $c) => $c->toIntegers());

        $intersect = $this->toIntegers()->intersect(...$otherValues);

        return self::fromIntegers($intersect);
    }

    public function merge(Candidates $other): self
    {
        $values = $this
            ->toIntegers()
            ->merge(...$other->toIntegers())
            ->unique();

        return self::fromIntegers($values);
    }

    public function contains(Candidates $other): bool
    {
        return $this->intersect($other)->count() === $other->count();
    }

    public function withRemovedValues(Value ...$values): self
    {
        if ($values === []) {
            return $this;
        }

        $integers = ArrayList::fromList($values)->map(static fn (Value $v) => $v->value);

        return self::fromIntegers($this->toIntegers()->filter(
            static fn (mixed $item) => ! $integers->contains($item))
        );
    }

    public function equals(Comparable $other): bool
    {
        return $this->toIntegers()->toArray() === $other->toIntegers()->toArray();
    }

    public function toString(): string
    {
        $values = $this->toIntegers()->toArray();

        return implode(',', $values);
    }

    public function __toString(): string
    {
       return $this->toString();
    }

    /**
     * @param ArrayList<int<Value::MIN, Value::MAX>> $values
     */
    private static function fromIntegers(ArrayList $values): self
    {
        return new self($values->map(static fn (int $v) => Value::from($v)));
    }

    /**
     * @return ArrayList<int<Value::MIN, Value::MAX>>
     */
    private function toIntegers(): ArrayList
    {
        /** @var ArrayList<int<Value::MIN, Value::MAX>> $values */
        $values = $this->values->map(static fn (Value $v) => $v->value);

        return $values;
    }
}
