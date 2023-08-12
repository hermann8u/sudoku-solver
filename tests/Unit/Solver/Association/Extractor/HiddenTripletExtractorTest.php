<?php

use SudokuSolver\Solver\Association\Extractor\HiddenTripletExtractor;
use SudokuSolver\Solver\Association\Triplet;

it('is able to find hidden triplets', function (array $mapForGroupData, array $expectedAssociationStrings) {
    $mapForGroup = buildMapFrom($mapForGroupData);

    $extractor = new HiddenTripletExtractor();
    $associations = $extractor->getAssociationsForGroup($mapForGroup);

    expect($associations)->toHaveCount(1);

    $association = reset($associations);

    expect($association)->toBeAssociation(Triplet::fromStrings(...$expectedAssociationStrings));
})->with([
    'Region' => [
        [
            '(5,7)' => '4,9',
            '(5,8)' => '6,9',
            '(5,9)' => '4,6',
            '(6,7)' => '2,4,5,9',
            '(6,8)' => '2,6,9',
        ],
        [
            ['(5,7)', '(5,8)', '(5,9)'],
            '4,6,9',
        ],
    ],
]);
