<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell\CellValue;
use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;

final readonly class Solver
{
    private CellCandidatesCollection $cellCandidatesCollection;

    public function __construct()
    {
        $this->cellCandidatesCollection = new CellCandidatesCollection();
    }

    public function solve(Grid $grid): void
    {
        $i = 0;

        $methods = [
            'obvious' => $this->getCandidates(...),
            'candidate_not_present_in_related_cell' => $this->getCandidatesNotPresentInOtherRelatedCells(...),
        ];

        $methodNamesCount = [];

        do {
            foreach ($grid->getFillableCells() as $currentCell) {
                if ($currentCell->isEmpty() === false) {
                    continue;
                }

                foreach ($methods as $methodName => $method) {
                    $candidates = $method($grid, $currentCell);

                    if ($this->updateCellWhenCandidateIsUnique($currentCell, $candidates)) {
                        $methodNamesCount[$methodName] = ($methodNamesCount[$methodName] ?? 0) +1;
                        break;
                    }
                }
            }
            /*if ($i === 8) {
                break;
            }*/

            $i++;
        } while ($grid->isValid() === false && $i < 50);

        dump([
            'iteration' => $i,
            'valid' => $grid->isValid(),
            'filled' => $grid->isFilled(),
            'contains_duplicate' => $grid->containsDuplicate(),
            'cell_to_fill' => count($grid->getFillableCells()),
            'methods' => $methodNamesCount,
            'memory' => memory_get_peak_usage(),
            'real_memory' => (memory_get_peak_usage(true) / 1024 / 1024) . ' MiB',
        ]);
    }

    private function updateCellWhenCandidateIsUnique(FillableCell $cell, Candidates $candidates): bool
    {
        if (! $candidates->isUnique()) {
            return false;
        }

        $this->updateCell($cell, $candidates->first());

        return true;
    }

    private function updateCell(FillableCell $cell, CellValue $value): void
    {
        $cell->updateValue($value);
        $this->cellCandidatesCollection->reset();
    }

    private function getCandidates(Grid $grid, FillableCell $cell): Candidates
    {
        if (! $this->cellCandidatesCollection->has($cell)) {
            $candidates = $this->doGetCandidates($grid, $cell);

            $this->cellCandidatesCollection->add($cell, $candidates);
        }

        return $this->cellCandidatesCollection->get($cell);
    }

    private function getCandidatesNotPresentInOtherRelatedCells(Grid $grid, FillableCell $currentCell): Candidates
    {
        $candidates = $this->getCandidates($grid, $currentCell);

        foreach ($grid->getSetsContainingCell($currentCell) as $set) {
            foreach ($set->getEmptyCells() as $relatedCell) {
                if ($relatedCell->is($currentCell)) {
                    continue;
                }

                $relatedCellCandidates = $this->getCandidates($grid, $relatedCell);

                // Short circuit :  When related cell has only one candidate
                if ($this->updateCellWhenCandidateIsUnique($relatedCell, $relatedCellCandidates)) {
                    return Candidates::empty();
                }

                $candidates = $candidates->withRemovedValues(...$relatedCellCandidates);
            }
        }

        return $candidates;
    }

    private function doGetCandidates(Grid $grid, FillableCell $cell): Candidates
    {
        $sets = $grid->getSetsContainingCell($cell);

        $candidatesBySet = [];

        foreach ($sets as $set) {
            $candidates = Candidates::all()->withRemovedValues(...$set->getPresentValues());

            if ($candidates->isUnique()) {
                return $candidates;
            }

            $candidatesBySet[] = $candidates;
        }

        return Candidates::intersect(...$candidatesBySet);
    }
}
