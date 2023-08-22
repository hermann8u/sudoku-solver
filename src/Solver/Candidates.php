<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell\Value;

/**
 * @implements \IteratorAggregate<int, Value>
 */
final readonly class Candidates implements \IteratorAggregate, \Stringable
{
    /** @var Value[] */
    public array $values;

    /**
     * @param Value[] $values
     */
    private function __construct(array $values)
    {
        $this->values = array_values($values);
    }

    public static function all(): self
    {
        return self::fromInt(...range(Value::MIN, Value::MAX));
    }

    public static function fromString(string $valuesString): self
    {
        $values = explode(',', $valuesString);
        /** @var array<int<Value::MIN, Value::MAX>> $values */
        $values = array_map(static fn (string $v) => (int) $v, $values);

        return self::fromInt(...$values);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function hasUniqueValue(): bool
    {
        return count($this->values) === 1;
    }

    public function first(): Value
    {
        $value = current($this->values);
        if ($value === false) {
            throw new \LogicException();
        }

        return $value;
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function intersect(Candidates ...$others): self
    {
        $otherValues = array_map(
            static fn (Candidates $c) => $c->toIntegers(),
            $others,
        );

        $intersect = array_intersect($this->toIntegers(), ...$otherValues);

        return self::fromInt(...$intersect);
    }

    public function merge(Candidates $other): self
    {
        $values = array_unique([...$this->toIntegers(), ...$other->toIntegers()]);

        return self::fromInt(...$values);
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

        $currentValues = $this->toIntegers();

        foreach ($values as $value) {
            $index = array_search($value->value, $currentValues, true);

            if (\is_int($index)) {
                unset($currentValues[$index]);
            }
        }

        return self::fromInt(...$currentValues);
    }

    public function toString(): string
    {
        $values = $this->toIntegers();
        sort($values);

        return implode(',', $values);
    }

    public function __toString(): string
    {
       return $this->toString();
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->values);
    }

    /**
     * @param int<Value::MIN, Value::MAX> ...$values
     */
    private static function fromInt(int ...$values): self
    {
        return new self(array_map(
            static fn (int $v) => Value::from($v),
            $values,
        ));
    }

    /**
     * @return array<int<Value::MIN, Value::MAX>>
     */
    private function toIntegers(): array
    {
        return array_filter(array_column($this->values, 'value'));
    }
}
