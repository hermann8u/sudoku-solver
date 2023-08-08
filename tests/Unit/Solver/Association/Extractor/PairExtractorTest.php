<?php

use SudokuSolver\Solver\Association\Extractor\PairExtractor;
use SudokuSolver\Solver\Association\Pair;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;

it('is able to find right pairs', function (array $cellCandidatesData, array $expectedPairStrings) {
    $mapForGroup = CellCandidatesMap::empty();
    foreach ($cellCandidatesData as $coordinatesString => $candidates) {
        $mapForGroup = $mapForGroup->merge(
            fillableCellFromCoordinatesString($coordinatesString),
            Candidates::fromString($candidates),
        );
    }

    $pairExtractor = new PairExtractor();
    $pairs = $pairExtractor->getAssociationsForGroup($mapForGroup);

    expect($pairs)->toHaveCount(1);

    $pair = reset($pairs);

    expect($pair)->toBeAssociation(Pair::fromStrings(...$expectedPairStrings));
})->with([
    'column' => [
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
    'row' => [
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
