<?php

declare(strict_types=1);

use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Set\GroupNumber;

it('has the expected number when built from coordinates', function (int $x, int $y, int $expectedNumber) {
    $coordinates = new Coordinates($x, $y);

    $group = GroupNumber::fromCoordinates($coordinates);

    expect($group->value)->toBe($expectedNumber);
})->with([
    '(1,1)' => [1, 1, 1],
    '(4,1)' => [4, 1, 2],
    '(1,4)' => [1, 4, 4],
    '(1,9)' => [1, 9, 7],
    '(4,4)' => [4, 4, 5],
    '(5,5)' => [5, 5, 5],
    '(6,6)' => [6, 6, 5],
    '(7,4)' => [7, 4, 6],
    '(9,9)' => [9, 9, 9],
]);
