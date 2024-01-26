<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\DataStructure\Comparable;
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

    public function intersect(Candidates $other): self
    {
        $intersect = $this->toIntegers()->intersect($other->toIntegers());

        return self::fromIntegers($intersect);
    }

    public function merge(Candidates $other): self
    {
        return new self($this->values
            ->merge($other->values)
            ->unique(static fn (Value $a, Value $b) => $a->equals($b)),
        );
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

        $valuesToRemove = ArrayList::fromList($values);

        return new self($this->values->filter(static fn (Value $v) => ! $valuesToRemove->exists($v->equals(...))));
    }

    public function equals(Comparable $other): bool
    {
        return $this->toIntegers()->toArray() === $other->toIntegers()->toArray();
    }

    public function toString(): string
    {
        return $this->toIntegers()->implode(',');
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
