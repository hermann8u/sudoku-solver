<?php

declare(strict_types=1);

namespace Sudoku\Grid\Cell;

use Sudoku\Grid\Cell;

final readonly class FillableCell extends Cell
{
    public function withValue(?Value $value): self
    {
        return new self($this->coordinates, $value);
    }
}
