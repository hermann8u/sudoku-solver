<?php

declare(strict_types=1);

use Florian\SudokuSolver\Grid\Set\Group;
use Florian\SudokuSolver\Grid\Set\GroupNumber;
use SudokuSolver\Tests\Double\Grid\NullCell;

it('has expected groups with direct impact', function (int $value, array $expectedGroupsNumber) {
    $group = new Group(
        NullCell::multiple(),
        new GroupNumber($value),
    );

    $groupsWithDirectImpact = array_map(
        static fn (GroupNumber $g) => $g->value,
        $group->getGroupNumbersWithDirectImpact()
    );

    sort($groupsWithDirectImpact);

    expect($groupsWithDirectImpact)->toBe($expectedGroupsNumber);
})->with([
    [1, [2, 3, 4, 7]],
    [5, [2, 4, 6, 8]],
    [6, [3, 4, 5, 9]],
    [7, [1, 4, 8, 9]],
    [9, [3, 6, 7, 8]],
])->only();
