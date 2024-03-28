<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Throwable;

final class NoSolutionException extends \DomainException
{
    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     */
    public function __construct(
        public readonly Map $candidatesByCell,
        ?Throwable $previous = null,
    ) {
        $cell = $this->getCellWithoutSolution();

        $message = $cell
            ? sprintf('The cell %s has no solution.', $cell->coordinates->toString())
            : 'The grid has no solution';

        parent::__construct(
            $message,
            previous: $previous,
        );
    }

    private function getCellWithoutSolution(): ?FillableCell
    {
        foreach ($this->candidatesByCell as $fillableCell => $candidates) {
            if ($candidates->count() === 0) {
                return $fillableCell;
            }
        }

        return null;
    }
}
