<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Stringable;
use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Equable;
use Sudoku\Grid\Cell\Value;

/**
 * @implements Equable<Candidates>
 */
final readonly class Candidates implements Equable, Stringable
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

    public static function fromAllValues(): self
    {
        return new self(Value::all());
    }

    public static function fromValues(Value $value, Value ...$values): self
    {
        return new self(ArrayList::fromItems($value, ...$values));
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

    public function first(): Value
    {
        return $this->values->first();
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return $this->values->count();
    }

    public function intersect(Candidates $other, Candidates ...$others): self
    {
        $othersToIntegers = array_map(
            static fn (Candidates $c) => $c->toIntegers(),
            [$other, ...$others],
        );

        return self::fromIntegers($this->toIntegers()->intersect(...$othersToIntegers));
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

    public function equals(Equable $other): bool
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
