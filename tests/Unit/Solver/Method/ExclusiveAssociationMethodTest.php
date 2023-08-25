<?php

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Solver\Association\Pair;
use SudokuSolver\Solver\Method\ExclusiveAssociationMethod;
use SudokuSolver\Solver\Method\InclusiveMethod;
use SudokuSolver\Tests\Double\Solver\Association\Extractor\PredictablePairExtractor;

it('exclude candidates present in pair', function () {
    // Arrange
    $pair = Pair::fromStrings(['(4,3)', '(4,9)'], '3,7');
    $pairExtractor = new PredictablePairExtractor([$pair]);

    $map = buildMapFrom(['(4,1)' => '6,7']);
    $grid = buildGridFromFilePath('exclusive_association/exclusive_pair.csv');
    /** @var FillableCell $cell */
    $cell = $grid->getCell(Coordinates::fromString('(4,1)'));

    // Act
    $method = new ExclusiveAssociationMethod(
        new InclusiveMethod(),
        [$pairExtractor],
    );

    $updatedMap = $method->apply($map, $grid, $cell);

    // Assert
    $candidates = $updatedMap->get('(4,1)');

    expect($candidates->hasUniqueCandidate())->toBeTrue();
    expect($candidates->toString())->toBe('6');
});
