<?php

use SudokuSolver\Solver\Association\Extractor\TripletExtractor;
use SudokuSolver\Solver\Association\Triplet;

it('is able to find triplets', function (array $mapForGroupData, array $expectedAssociationStrings) {
    $mapForGroup = buildMapFrom($mapForGroupData);

    $extractor = new TripletExtractor();
    $associations = $extractor->getAssociationsForGroup($mapForGroup);

    expect($associations)->toHaveCount(1);

    $association = reset($associations);

    expect($association)->toBeAssociation(Triplet::fromStrings(...$expectedAssociationStrings));
})->with([
    'Perfect triplet' => [
        [
            '(7,1)' => '2,6,8',
            '(7,3)' => '2,6,8',
            '(7,5)' => '6,8,9',
            '(7,6)' => '2,6,8',
            '(7,8)' => '1,5,6,8,9',
            '(7,9)' => '1,5,6,8,9',
        ],
        [
            ['(7,1)', '(7,3)', '(7,6)'],
            '2,6,8',
        ],
    ],
    'Triplet 3-3-2' => [
        [
            '(9,1)' => '2,3,5,9',
            '(9,2)' => '1,2,3,5,9',
            '(9,4)' => '1,3,8',
            '(9,5)' => '1,3,8',
            '(9,6)' => '1,3',
            '(9,9)' => '1,2',
        ],
        [
            ['(9,4)', '(9,5)', '(9,6)'],
            '1,3,8',
        ],
    ],
    'Triplet 3-2-2' => [
        [
            '(4,4)' => '4,8',
            '(4,5)' => '4,8,9',
            '(4,6)' => '4,9',
            '(5,4)' => '3,4,6,8',
            '(6,5)' => '4,6,9',
        ],
        [
            ['(4,4)', '(4,5)', '(4,6)'],
            '4,8,9',
        ],
    ],
]);
