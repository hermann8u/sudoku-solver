<?php

declare(strict_types=1);

namespace Sudoku\Grid;

enum Level
{
    case Unknown;
    case Easy;
    case Medium;
    case Hard;
    case VeryHard;
    case Extreme;
}
