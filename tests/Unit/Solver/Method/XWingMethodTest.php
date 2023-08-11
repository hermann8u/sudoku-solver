<?php

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Solver\Method\InclusiveMethod;
use SudokuSolver\Solver\Method\XWingMethod;

it('exclude candidates thanks to X-Wing', function () {
    // Arrange
    $map = buildMapFrom([
        '(2,1)' => '2,4,6,9',
        '(2,3)' => '2,9',
        '(2,8)' => '3,6,8,9',
        '(2,9)' => '6,9',
    ]);

    $grid = buildGridFromFilePath('x_wing/horizontal.csv');
    /** @var FillableCell $cell */
    $cell = $grid->getCell(Coordinates::fromString('(2,3)'));

    // Act
    $method = new XWingMethod(
        new InclusiveMethod(),
    );

    $updatedMap = $method->apply($map, $grid, $cell);

    // Assert
    $candidates = $updatedMap->get('(2,1)');
    expect($candidates->toString())->toBe('2,4,6');

    $candidates = $updatedMap->get('(2,8)');
    expect($candidates->toString())->toBe('3,6,8');
});
