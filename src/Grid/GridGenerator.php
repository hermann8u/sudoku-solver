<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid;

final class GridGenerator
{
    public function __construct(
        private readonly GridFactory $gridFactory,
    ) {
    }

    public function generate(string $gridAsString): Grid
    {
        $rows = preg_split("/\r\n|\n|\r/", trim($gridAsString));

        $gridAsArray = [];

        /** @phpstan-ignore-next-line */
        foreach ($rows as $row) {
            $gridAsArray[] = explode(';', $row);
        }

        return $this->gridFactory->create($gridAsArray);
    }
}
