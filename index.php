<?php

use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\FixedValueCell;
use Sudoku\Grid\Factory\ArrayGridFactory;
use Sudoku\Grid\Factory\CsvGridFactory;
use Sudoku\Solver;
use Sudoku\Solver\Method\Association\AssociationApplier;
use Sudoku\Solver\Method\Association\Extractor\HiddenPairExtractor;
use Sudoku\Solver\Method\Association\Extractor\PairExtractor;
use Sudoku\Solver\Method\Association\Extractor\PointingPairExtractor;
use Sudoku\Solver\Method\Association\Extractor\TripletExtractor;
use Sudoku\Solver\Method\Association\Extractor\YWingExtractor;
use Sudoku\Solver\Method\ExclusiveMethod;
use Sudoku\Solver\Method\ExclusivePairMethod;
use Sudoku\Solver\Method\ExclusiveTripletMethod;
use Sudoku\Solver\Method\HiddenPairMethod;
use Sudoku\Solver\Method\InclusiveMethod;
use Sudoku\Solver\Method\PointingPairMethod;
use Sudoku\Solver\Method\XWingMethod;
use Sudoku\Solver\Method\YWingMethod;

require './vendor/autoload.php';

$csvStringGrid = file_get_contents('./data/grid/very_hard/1.csv');

$gridFactory = new CsvGridFactory(new ArrayGridFactory());
$grid = $gridFactory->create($csvStringGrid);

$exclusiveMethod = new ExclusiveMethod();
$associationApplier = new AssociationApplier();

$solver = new Solver(
    new InclusiveMethod(),
    [
        // Apply exclusive method early in order to find obvious solution
        $exclusiveMethod,
        new ExclusiveTripletMethod(
            new TripletExtractor(),
            $associationApplier,
        ),
        new ExclusivePairMethod(
            new PairExtractor(),
            $associationApplier,
        ),
        new HiddenPairMethod(
            new HiddenPairExtractor(),
            $associationApplier,
        ),
        new PointingPairMethod(
            new PointingPairExtractor(),
            $associationApplier,
        ),
        new YWingMethod(
            new YWingExtractor(),
            $associationApplier,
        ),
        new XWingMethod(
            new YWingExtractor(),
            $associationApplier,
        ),
        // Reapply exclusive method to make sure there is no new solution
        $exclusiveMethod,
    ],
);

$result = $solver->solve($grid);

dump($result);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
            font-family: sans-serif;
        }
        .container {
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        table {
            border-collapse: collapse;
            border: 3px solid #222;
        }
        td {
            width: 50px;
            height: 50px;
            border: 1px solid #444;
            text-align: center;
        }
        td:nth-of-type(3n) {
            border-right: 3px solid #222;
        }
        tr:nth-of-type(3n) td {
            border-bottom: 3px solid #222;
        }
        .fixed {
            background-color: #ddd;
            cursor: default;
        }
        .fillable {
            cursor: pointer;
            position: relative;
        }
        .fillable.solved {
            background-color: forestgreen;
            color: white;
        }
        .fillable .candidates {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap-reverse;
        }
        .fillable .candidates small {
            padding: 0 2px;
        }
        .fillable .step-number {
            position: absolute;
            padding: 0 2px;
            top: 0;
            right: 0;
            background: #444;
            color: white;
        }
        .steps li {
            padding: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php $candidatesByCell = $result->getCandidatesByCell(); ?>

        <p><?= $result->isSolved() ? 'Solved!' : 'No solution' ?></p>

        <table>
            <tbody>
            <?php foreach ($result->grid->rows as $row) : ?>
                <tr>
                    <?php foreach ($row->cells as $cell) : ?>
                        <?php if ($cell instanceof FixedValueCell): ?>
                            <td class="fixed">
                                <?= $cell->value ?>
                            </td>
                        <?php elseif ($cell instanceof FillableCell) : ?>
                            <td class="fillable <?= $cell->isEmpty() ? '' : 'solved' ?>">
                                <span><?= $cell->value ?></span>
                                <?php if ($cellStepNumber = $result->getStepNumberForCell($cell)) : ?>
                                    <small class="step-number"><?= $cellStepNumber ?></small>
                                <?php endif; ?>
                                <?php if ($candidatesByCell->has($cell)) : ?>
                                    <div class="candidates">
                                        <?php foreach ($candidatesByCell->get($cell)->values as $value) : ?>
                                            <small><?= $value ?></small>
                                        <?php endforeach;?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endif ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <hr>

        <ol class="steps">
            <?php foreach ($result->steps as $step) : ?>
                <?php if ($step->solution !== null) : ?>
                    <li><?= $step->solution ?></li>
                <?php endif ?>
            <?php endforeach; ?>
        </ol>
    </div>
</body>
</html>
