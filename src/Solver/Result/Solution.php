<?php

declare(strict_types=1);

namespace Sudoku\Solver\Result;

use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;

final readonly class Solution implements \Stringable
{
    public function __construct(
        public string $method,
        public FillableCell $cell,
        public Value $value,
    ) {
    }

    public function toString(): string
    {
        return sprintf('%s : %s => %d', $this->method, $this->cell->coordinates->toString(), $this->value->value);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
