<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

abstract readonly class Association
{
    /**
     * @param Coordinates[] $coordinatesList
     */
    final public function __construct(
        public array $coordinatesList,
        public Candidates $candidates,
    ) {
        Assert::count($this->coordinatesList, $this->getAssociationCount());
        Assert::count($this->candidates->values, $this->getAssociationCount());
    }

    /**
     * @param string[] $coordinatesStrings
     */
    public static function fromStrings(array $coordinatesStrings, string $valuesString): static
    {
        return new static(
            array_map(
                static fn (string $coordinates) => Coordinates::fromString($coordinates),
                $coordinatesStrings,
            ),
            Candidates::fromString($valuesString),
        );
    }

    public function contains(Cell $cell): bool
    {
        return in_array(
            $cell->coordinates->toString(),
            array_map(static fn (Coordinates $c) => (string) $c, $this->coordinatesList),
            true,
        );
    }

    /**
     * @return positive-int
     */
    abstract protected function getAssociationCount(): int;
}
