<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association;
use Sudoku\Solver\CellCandidatesMap;

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
