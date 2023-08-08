<?php

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\GridFactory;
use SudokuSolver\Grid\GridGenerator;
use SudokuSolver\Solver\Association;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;

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

function buildGridFromFilePath(string $path): Grid
{
    $realpath = sprintf('%s/data/grid/%s', __DIR__, $path);
    $stringGrid = file_get_contents($realpath);

    if ($stringGrid === false) {
        throw new \LogicException(sprintf('Unable to load file %s', $realpath));
    }

    $generator = new GridGenerator(new GridFactory());

    return $generator->generate($stringGrid);
}

function buildMapFrom(array $mapData): CellCandidatesMap
{
    $map = CellCandidatesMap::empty();
    foreach ($mapData as $coordinatesString => $candidates) {
        $map = $map->merge(
            buildFillableCellFromCoordinatesString($coordinatesString),
            Candidates::fromString($candidates),
        );
    }

    return $map;
}

function buildFillableCellFromCoordinatesString(string $coordinatesString): FillableCell
{
    return new FillableCell(Coordinates::fromString($coordinatesString));
}
