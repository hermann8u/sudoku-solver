<?php

declare(strict_types=1);

use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Group\Number\RegionNumber;

it('has the expected number when built from coordinates', function (string $coordinatesString, int $expectedNumber) {
    $regionNumber = RegionNumber::fromCoordinates(Coordinates::fromString($coordinatesString));

    expect($regionNumber->value)->toBe($expectedNumber);
})->with([
    '(1,1)' => ['(1,1)', 1],
    '(4,1)' => ['(4,1)', 2],
    '(1,4)' => ['(1,4)', 4],
    '(1,9)' => ['(1,9)', 7],
    '(4,4)' => ['(4,4)', 5],
    '(5,5)' => ['(5,5)', 5],
    '(6,6)' => ['(6,6)', 5],
    '(7,4)' => ['(7,4)', 6],
    '(9,9)' => ['(9,9)', 9],
]);
