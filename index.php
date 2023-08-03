<?php

use Florian\SudokuSolver\Grid\Cell\FixedValueCell;
use Florian\SudokuSolver\Grid\GridFactory;
use Florian\SudokuSolver\Grid\GridGenerator;
use Florian\SudokuSolver\Solver\Solver;

require './vendor/autoload.php';

$easyGrid = <<<GRID
3;4;;;;;;7;1
;;2;;3;;5;;
;;;5;;6;;;
1;;;;;;;;2
;;;;9;;;;
8;;;2;6;1;;;9
;;4;;8;;3;;
;;;9;1;3;;;
9;;;;;;;;7
GRID;

$mediumGrid = <<<GRID
;;;;1;;;;
9;5;;8;;3;;7;1
;;;;7;;;;
;4;;1;;7;;5;
5;7;;2;;6;;8;3
8;;;;;;;;7
;6;9;;;;3;2;
;;;4;;2;;;
;;2;;3;;7;;
GRID;

$hardGrid = <<<GRID
4;;;3;;9;;;2
;;;;;;;;
;6;;;2;;;8;
;7;;;8;;;1;
;;;5;;4;;;
;3;5;;;;7;9;
;;7;;;;2;;
;;9;;4;;1;;
;;;2;3;5;;;
GRID;

/*$hardGrid = <<<GRID
4;;;3;;9;;;2
;;;;;;;;
;6;;;2;;;8;
;7;;;8;3;;1;
;;;5;;4;;;
;3;5;;;;7;9;
;;7;;;;2;;
;;9;;4;;1;;
;;;2;3;5;;;
GRID;

$hardGrid = <<<GRID
4;;;3;;9;;;2
;;;;;;;;
;6;;;2;;;8;
;7;;;8;3;;1;
;;;5;7;4;;;
;3;5;;;;7;9;
;;7;;;;2;;
;;9;;4;;1;;
;;;2;3;5;;;
GRID;

$hardGrid = <<<GRID
4;;;3;;9;;;2
;;;;;;;;
;6;;;2;;;8;
;7;4;9;8;3;;1;
;;;5;7;4;;2;
;3;5;;;2;7;9;
;;7;;9;;2;;
;;9;;4;;1;;
;;;2;3;5;;;
GRID;

$hardGrid = <<<GRID
4;;;3;;9;;;2
;;;;;;;;
;6;;;2;;;8;
;7;4;9;8;3;;1;
;;;5;7;4;;2;
;3;5;;6;2;7;9;
;;7;;9;;2;;
;;9;;4;;1;;
;;;2;3;5;;;
GRID;*/


$generator = new GridGenerator(new GridFactory());
$grid = $generator->generate($hardGrid);

$solver = new Solver();
$solver->solve($grid);

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
