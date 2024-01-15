<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\Grid\Group;
use SudokuSolver\Solver\Association;
use SudokuSolver\Solver\CellCandidatesMap;

/**
 * @template T of Association
 */
interface AssociationExtractor
{
    /**
     * @return ArrayList<T>
     */
    public function getAssociationsInGroup(CellCandidatesMap $map, Group $group): ArrayList;

    /**
     * @return class-string<T>
     */
    public static function getAssociationType(): string;
}
