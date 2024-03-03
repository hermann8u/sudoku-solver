<?php

use Sudoku\Solver\Association\Extractor\PairExtractor;
use Sudoku\Solver\Association\Naked\Pair;

it('is able to find pairs', function (array $mapForGroupData, array $expectedAssociationStrings) {
    $mapForGroup = buildMapFrom($mapForGroupData);

    $extractor = new PairExtractor();
    $associations = $extractor->getAssociationsInGroup($mapForGroup);

    expect($associations)->toHaveCount(1);

    $association = $associations->first();

    expect($association)->toBeAssociation(Pair::fromStrings(...$expectedAssociationStrings));
})->with([
    'Column' => [
        [
            '(4,1)' => '7,6',
            '(4,3)' => '7,3',
            '(4,7)' => '1,3,7,8',
            '(4,8)' => '1,3,6,8',
            '(4,9)' => '3,7',
        ],
        [
            ['(4,3)', '(4,9)'],
            '3,7',
        ],
    ],
    'Row' => [
        [
            '(1,5)' => '4,8',
            '(2,5)' => '4,8',
            '(6,5)' => '2,4,7,9',
            '(7,5)' => '4,7,8',
            '(9,5)' => '2,4,7,9',
        ],
        [
            ['(1,5)', '(2,5)'],
            '4,8',
        ],
    ],
]);
