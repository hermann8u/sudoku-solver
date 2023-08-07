<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Solver\Association;
use Florian\SudokuSolver\Solver\CellCandidatesMap;

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
