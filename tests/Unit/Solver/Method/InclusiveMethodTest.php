<?php

use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Method\InclusiveMethod;

it('exclude candidates present in pair', function (string $file, string $cellToCheck, string $expectedValues) {
    // Arrange
    $candidatesByCell = Map::empty();
    $grid = buildGridFromFilePath($file);
    /** @var FillableCell $cell */
    $cell = $grid->getCell(Coordinates::fromString($cellToCheck));

    // Act
    $method = new InclusiveMethod();

    $updatedMap = $method->apply($candidatesByCell, $grid, $cell);

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
