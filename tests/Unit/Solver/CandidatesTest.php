<?php

use SudokuSolver\Solver\Candidates;

it('create correct Candidates instance with method fromValuesOnlyPresentOnceIn', function (array $candidatesListValues, string $expectedValuesString) {
    $from = [];
    foreach ($candidatesListValues as $candidatesValues) {
        $from[] = Candidates::fromString($candidatesValues);
    }

    $candidates = Candidates::fromValuesOnlyPresentOnceIn(...$from);

    expect($candidates->toString())->toBe($expectedValuesString);
})->with([
    [
        ['1,2,3,4,5,6,7,8,9', '1,2,3,4,5,6,7,8'],
        '9',
    ],
    [
        ['1', '2'],
        '1,2',
    ],
    [
        ['1,2', '2'],
        '1',
    ],
    [
        ['1,2,3', '2,3,4'],
        '1,4',
    ],
]);
