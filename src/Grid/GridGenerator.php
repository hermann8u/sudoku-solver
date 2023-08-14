<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

final readonly class GridGenerator
{
    public function __construct(
        private GridFactory $gridFactory,
    ) {
    }

    public function generate(string $gridAsString): Grid
    {
        $rows = preg_split("/\r\n|\n|\r/", trim($gridAsString));
        if ($rows === false) {
            $rows = [];
        }

        Assert::count($rows, Coordinates::MAX);

        foreach ($rows as $row) {
            $cellValues = explode(';', $row);

            Assert::count($cellValues, Coordinates::MAX);

            $gridAsArray[] = $cellValues;
        }

        /** @var array<int<0, 8>, array<int<0, 8>, string>> $gridAsArray */
        return $this->gridFactory->create($gridAsArray ?? []);
    }
}
