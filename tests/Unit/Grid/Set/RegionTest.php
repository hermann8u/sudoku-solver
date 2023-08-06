<?php

declare(strict_types=1);

use Florian\SudokuSolver\Grid\Group\Region;
use Florian\SudokuSolver\Grid\Group\RegionNumber;
use SudokuSolver\Tests\Double\Grid\NullCell;

it('has expected regions with direct impact', function (int $value, array $expectedRegionsNumber) {
    $region = Region::fromCells(
        NullCell::multiple(),
        new RegionNumber($value),
    );

    $regionsWithDirectImpact = array_map(
        static fn (RegionNumber $g) => $g->value,
        $region->getRegionNumbersWithDirectImpact()
    );

    sort($regionsWithDirectImpact);

    expect($regionsWithDirectImpact)->toBe($expectedRegionsNumber);
})->with([
    [1, [2, 3, 4, 7]],
    [5, [2, 4, 6, 8]],
    [6, [3, 4, 5, 9]],
    [7, [1, 4, 8, 9]],
    [9, [3, 6, 7, 8]],
])->only();
