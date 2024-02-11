<?php

use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\FixedValueCell;
use Sudoku\Grid\Factory\ArrayGridFactory;
use Sudoku\Grid\Factory\CsvGridFactory;
use Sudoku\Solver;
use Sudoku\Solver\Association\Extractor\HiddenPairExtractor;
use Sudoku\Solver\Association\Extractor\PairExtractor;
use Sudoku\Solver\Association\Extractor\TripletExtractor;
use Sudoku\Solver\Method\ExclusiveAssociationMethod;
use Sudoku\Solver\Method\ExclusiveMethod;
use Sudoku\Solver\Method\InclusiveMethod;
use Sudoku\Solver\Method\XWingMethod;

require './vendor/autoload.php';

$csvStringGrid = file_get_contents('./data/grid/very_hard/1.csv');

$gridFactory = new CsvGridFactory(new ArrayGridFactory());
$grid = $gridFactory->create($csvStringGrid);

$inclusiveMethod = new InclusiveMethod();

$solver = new Solver([
    $inclusiveMethod,
    new ExclusiveMethod(),
    new ExclusiveAssociationMethod(
        [
            new TripletExtractor(),
            new PairExtractor(),
            new HiddenPairExtractor(),
        ]
    ),
    new XWingMethod(),
    // Reapply exclusive method to make sure there is no new solution
    new ExclusiveMethod(),
]);

$result = $solver->solve($grid);

dump($result);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
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
        <?php $candidatesByCell = $result->getLastCandidatesByCell(); ?>

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
                                <?php if ($cellStepNumber = $result->getCellStepNumber($cell)) : ?>
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
                    <li><?= $step->solution->method ?> : <?= $step->solution->cell->coordinates ?> => <?= $step->solution->value ?></li>
                <?php endif ?>
            <?php endforeach; ?>
        </ol>
    </div>
</body>
</html>
