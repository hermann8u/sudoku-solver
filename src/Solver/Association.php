<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

abstract readonly class Association
{
    /**
     * @param Coordinates[] $coordinatesList
     */
    final protected function __construct(
        public array $coordinatesList,
        public Candidates $candidates,
    ) {
        Assert::count($this->coordinatesList, $this->getAssociationCount());
        Assert::same($this->candidates->count(), $this->getAssociationCount());
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
    abstract public static function getAssociationCount(): int;
}
