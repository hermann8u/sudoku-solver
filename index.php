<?php

use Florian\SudokuSolver\Grid\Cell\FixedValueCell;
use Florian\SudokuSolver\Grid\GridFactory;
use Florian\SudokuSolver\Grid\GridGenerator;
use Florian\SudokuSolver\Solver\Method\ExclusivePairMethod;
use Florian\SudokuSolver\Solver\Method\FilterPairMethod;
use Florian\SudokuSolver\Solver\Method\InclusiveMethod;
use Florian\SudokuSolver\Solver\Method\ExclusiveMethod;
use Florian\SudokuSolver\Solver\Method\Inspector;
use Florian\SudokuSolver\Solver\Solver;

require './vendor/autoload.php';

$stringGrid = file_get_contents('./data/grid/very_hard/1.csv');

$generator = new GridGenerator(new GridFactory());
$grid = $generator->generate($stringGrid);

$obviousCandidateMethod = new InclusiveMethod();

$solver = new Solver([
    $obviousCandidateMethod,
    new ExclusivePairMethod($obviousCandidateMethod),
    new Inspector(new FilterPairMethod($obviousCandidateMethod)),
    new ExclusiveMethod($obviousCandidateMethod),
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
