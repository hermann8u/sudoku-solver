<?php

use SudokuSolver\Grid\Cell\FixedValueCell;
use SudokuSolver\Grid\GridFactory;
use SudokuSolver\Grid\GridGenerator;
use SudokuSolver\Solver\Association\Extractor\HiddenTripletExtractor;
use SudokuSolver\Solver\Association\Extractor\PairExtractor;
use SudokuSolver\Solver\Association\Extractor\TripletExtractor;
use SudokuSolver\Solver\Method\ExclusiveAssociationMethod;
use SudokuSolver\Solver\Method\ExclusiveMethod;
use SudokuSolver\Solver\Method\InclusiveMethod;
use SudokuSolver\Solver\Method\XWingMethod;
use SudokuSolver\Solver\Solver;

require './vendor/autoload.php';

$stringGrid = file_get_contents('./data/grid/very_hard/2.csv');
//$stringGrid = file_get_contents('./tests/data/grid/x_wing/horizontal.csv');

$generator = new GridGenerator(new GridFactory());
$grid = $generator->generate($stringGrid);

$inclusiveMethod = new InclusiveMethod();

$solver = new Solver([
    $inclusiveMethod,
    //new XWingMethod($inclusiveMethod),
    new ExclusiveAssociationMethod(
        $inclusiveMethod,
        [
            new HiddenTripletExtractor(),
            new TripletExtractor(),
            new PairExtractor(),
        ]
    ),
    new ExclusiveMethod($inclusiveMethod),
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
        .container {
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            display: flex;
            justify-content: center;
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
        }
        .fillable.solved {
            background-color: forestgreen;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <table>
            <tbody>
            <?php for ($i = 1; $i < 10; $i++): ?>
                <tr>
                    <?php foreach ($grid->getCellsByRow($i) as $cell) : ?>
                        <?php if ($cell instanceof FixedValueCell): ?>
                            <td class="fixed">
                                <?= $cell->getCellValue() ?>
                            </td>
                        <?php else: ?>
                            <td class="fillable <?= $cell->isEmpty() ? '' : 'solved' ?>">
                                <?= $cell->getCellValue() ?>
                            </td>
                        <?php endif ?>
                    <?php endforeach;?>
                </tr>
            <?php endfor;?>
            </tbody>
        </table>
    </div>
</body>
</html>
