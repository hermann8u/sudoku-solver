<?php

declare(strict_types=1);

namespace Sudoku\Grid;

use Sudoku\Grid;

/**
 * @template TData of mixed
 */
interface GridFactory
{
    /**
     * @param TData $data
     */
    public function create(mixed $data): Grid;
}
