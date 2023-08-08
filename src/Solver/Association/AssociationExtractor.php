<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association;

use SudokuSolver\Solver\Association;
use SudokuSolver\Solver\CellCandidatesMap;

/**
 * @template T of Association
 */
interface AssociationExtractor
{
    /**
     * @return T[]
     */
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array;

    /**
     * @return class-string<T>
     */
    public static function getAssociationType(): string;
}
