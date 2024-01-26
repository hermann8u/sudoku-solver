<?php

use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Method\XWingMethod;

it('exclude candidates thanks to horizontal X-Wing', function () {
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
    $method = new XWingMethod();

    $updatedMap = $method->apply($map, $grid, $cell);

    // Assert
    $candidates = $updatedMap->get('(2,1)');
    expect($candidates->toString())->toBe('2,4,6');

    $candidates = $updatedMap->get('(2,8)');
    expect($candidates->toString())->toBe('3,6,8');
});

it('exclude candidates thanks to vertical X-Wing', function () {
    // Arrange
    $map = buildMapFrom([
        '(1,2)' => '6,9',
        '(1,5)' => '6,9',
        '(7,2)' => '2,9',
        '(7,5)' => '6,9',
        '(2,2)' => '3,6,8,9',
        '(9,2)' => '2,4,8,9',
    ]);

    $grid = buildGridFromFilePath('x_wing/vertical.csv');
    /** @var FillableCell $cell */
    $cell = $grid->getCell(Coordinates::fromString('(1,2)'));

    // Act
    $method = new XWingMethod();

    $updatedMap = $method->apply($map, $grid, $cell);

    // Assert
    $candidates = $updatedMap->get('(2,2)');
    expect($candidates->toString())->toBe('3,6,8');

    $candidates = $updatedMap->get('(9,2)');
    expect($candidates->toString())->toBe('2,4,8');
});
