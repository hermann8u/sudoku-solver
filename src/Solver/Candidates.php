<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell\CellValue;

/**
 * @implements \IteratorAggregate<int, CellValue>
 */
final readonly class Candidates implements \IteratorAggregate, \Stringable
{
    /** @var CellValue[] */
    public array $values;

    /**
     * @param CellValue[] $values
     */
    private function __construct(array $values)
    {
        $this->values = array_values($values);
    }

    public static function all(): self
    {
        return self::fromInt(...range(CellValue::MIN, CellValue::MAX));
    }

    /**
     * @param int<CellValue::MIN, CellValue::MAX> ...$values
     */
    public static function fromInt(int ...$values): self
    {
        return new self(array_map(
            static fn (int $v) => CellValue::from($v),
            $values,
        ));
    }

    public static function fromString(string $valuesString): self
    {
        $values = explode(',', $valuesString);
        /** @var array<int<CellValue::MIN, CellValue::MAX>> $values */
        $values = array_map(static fn (string $v) => (int) $v, $values);

        return self::fromInt(...$values);
    }

    public static function intersect(Candidates $candidates, Candidates ...$otherCandidates): self
    {
        $values = $candidates->toIntegers();
        $otherValues = array_map(
            static fn (Candidates $c) => $c->toIntegers(),
            $otherCandidates,
        );

        return self::fromInt(...array_intersect($values, ...$otherValues));
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function hasUniqueValue(): bool
    {
        return count($this->values) === 1;
    }

    public function first(): CellValue
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

    public function withRemovedValues(CellValue ...$cellValues): self
    {
        $currentValues = $this->toIntegers();

        foreach ($cellValues as $cell) {
            $index = array_search($cell->value, $currentValues, true);

            if (\is_int($index)) {
                unset($currentValues[$index]);
            }
        }

        return self::fromInt(...$currentValues);
    }

    /**
     * @return array<int<CellValue::MIN, CellValue::MAX>>
     */
    public function toIntegers(): array
    {
        return array_filter(array_column($this->values, 'value'));
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
}
