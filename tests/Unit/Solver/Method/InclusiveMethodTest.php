<?php

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method\InclusiveMethod;

it('exclude candidates present in pair', function (string $file, string $cellToCheck, string $expectedValues) {
    // Arrange
    $map = CellCandidatesMap::empty();
    $grid = buildGridFromFilePath($file);
    /** @var FillableCell $cell */
    $cell = $grid->getCell(Coordinates::fromString($cellToCheck));

    // Act
    $method = new InclusiveMethod();

    $updatedMap = $method->apply($map, $grid, $cell);

    // Assert
    $candidates = $updatedMap->get($cellToCheck);

    expect($candidates->toString())->toBe($expectedValues);
})->with([
    [
        'inclusive_method/column.csv',
        '(1,9)',
        '9',
    ],
    [
        'inclusive_method/row.csv',
        '(9,1)',
        '9',
    ],
    [
        'inclusive_method/region.csv',
        '(3,3)',
        '9',
    ],
    [
        'inclusive_method/multiple_group.csv',
        '(1,1)',
        '9',
    ],
    [
        'inclusive_method/all.csv',
        '(4,4)',
        '8',
    ],
]);
