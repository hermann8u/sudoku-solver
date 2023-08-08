<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Solver\Association;
use SudokuSolver\Solver\Candidates;

expect()->extend('toBeAssociation', function (Association $other) {

    /** @var Association $association */
    $association = $this->value;

    expect($association::class)->toBe($other::class);
    expect($association)->toHaveSameCandidates($other);
    expect($association)->toHaveSameCoordinatesList($other);

    return $this;
});

expect()->extend('toHaveSameCandidates', function (Association $other) {
    /** @var Candidates $candidates */
    $candidates = $this->value->candidates;

    expect($candidates->intersect($other->candidates)->count())->toBe($candidates->count());

    return $this;
});

expect()->extend('toHaveSameCoordinatesList', function (Association $other) {
    /** @var Coordinates[] $coordinatesList */
    $coordinatesList = $this->value->coordinatesList;

    $coordinatesStrings = array_map(static fn (Coordinates $c) => $c->toString(), $coordinatesList);
    sort($coordinatesStrings);

    $otherCoordinatesStrings = array_map(static fn (Coordinates $c) => $c->toString(), $other->coordinatesList);
    sort($otherCoordinatesStrings);

    expect($coordinatesStrings)->toBe($otherCoordinatesStrings);

    return $this;
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function fillableCellFromCoordinatesString(string $coordinatesString): FillableCell
{
    return new FillableCell(Coordinates::fromString($coordinatesString));
}
