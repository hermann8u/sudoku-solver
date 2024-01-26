<?php

declare(strict_types=1);

namespace SudokuSolver\DataStructure;

/**
 * @template T of Comparable
 */
interface Comparable
{
    /**
     * @param T $other
     */
    public function equals(self $other): bool;
}