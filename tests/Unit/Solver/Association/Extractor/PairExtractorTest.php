<?php

use SudokuSolver\Solver\Association\Extractor\PairExtractor;
use SudokuSolver\Solver\Association\Pair;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;

it('is able to find pairs', function (array $mapForGroupData, array $expectedAssociationStrings) {
    $mapForGroup = CellCandidatesMap::empty();
    foreach ($mapForGroupData as $coordinatesString => $candidates) {
        $mapForGroup = $mapForGroup->merge(
            fillableCellFromCoordinatesString($coordinatesString),
            Candidates::fromString($candidates),
        );
    }

    $extractor = new PairExtractor();
    $associations = $extractor->getAssociationsForGroup($mapForGroup);

    expect($associations)->toHaveCount(1);

    $association = reset($associations);

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
