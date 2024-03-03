<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association\Naked;

use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Association\NakedAssociation;

final readonly class Pair extends NakedAssociation
{
    public const COUNT = 2;

    public static function getAssociationCount(): int
    {
        return self::COUNT;
    }

    public function toString(): string
    {
        return sprintf(
            'Pair : %s => %s',
            $this->cells
                ->map(static fn (FillableCell $c) => $c->coordinates->toString())
                ->implode(' '),
            $this->values->implode(','),
        );
    }
}
