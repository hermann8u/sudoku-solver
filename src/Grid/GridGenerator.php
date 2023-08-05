<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid;

use Webmozart\Assert\Assert;

final class GridGenerator
{
    public function __construct(
        private readonly GridFactory $gridFactory,
    ) {
    }

    public function generate(string $gridAsString): Grid
    {
        $rows = preg_split("/\r\n|\n|\r/", trim($gridAsString));
        if ($rows === false) {
            $rows = [];
        }

        Assert::count($rows, 9);

        foreach ($rows as $row) {
            $cellValues = explode(';', $row);

            Assert::count($cellValues, 9);

            $gridAsArray[] = $cellValues;
        }

        /** @var array<int<0, 8>, array<int<0, 8>, string>> $gridAsArray */
        return $this->gridFactory->create($gridAsArray ?? []);
    }
}
