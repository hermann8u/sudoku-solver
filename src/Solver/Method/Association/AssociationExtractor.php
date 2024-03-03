<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method\Association;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Association;
use Sudoku\Solver\Candidates;

/**
 * @template T of Association
 */
interface AssociationExtractor
{
    /**
     * @return class-string<T>
     */
    public static function getAssociationType(): string;

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return iterable<T>
     */
    public function getAssociationWithCell(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): iterable;
}
