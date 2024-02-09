<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association;
use Sudoku\Solver\Candidates;

/**
 * @template T of Association
 */
interface AssociationExtractor
{
    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return iterable<T>
     */
    public function getAssociationsInGroup(Map $candidatesByCell, Group $group): iterable;

    /**
     * @return class-string<T>
     */
    public static function getAssociationType(): string;
}
