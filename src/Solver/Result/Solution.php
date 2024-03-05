<?php

declare(strict_types=1);

namespace Sudoku\Solver\Result;

use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;

final readonly class Solution implements \Stringable
{
    public FillableCell $updatedCell;

    public function __construct(
        public string $method,
        public FillableCell $cell,
        public Value $value,
    ) {
        $this->updatedCell = $this->cell->withValue($this->value);
    }

    public function toString(): string
    {
        return sprintf('%s : %s', $this->method, $this->updatedCell);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
