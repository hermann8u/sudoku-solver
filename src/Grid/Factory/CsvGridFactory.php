<?php

declare(strict_types=1);

namespace Sudoku\Grid\Factory;

use Sudoku\Grid;
use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\GridFactory;
use Webmozart\Assert\Assert;

/**
 * @implements GridFactory<string>
 */
final readonly class CsvGridFactory implements GridFactory
{
    /**
     * @param non-empty-string $separator
     */
    public function __construct(
        private ArrayGridFactory $arrayGridFactory,
        private string $separator = ';',
    ) {
    }

    public function create(mixed $data): Grid
    {
        $rows = array_filter(explode(PHP_EOL, $data), static fn (string $row) => $row !== '');

        Assert::count($rows, Coordinates::MAX);

        $gridAsArray = [];

        foreach ($rows as $row) {
            $rawCellsValues = explode($this->separator, $row);

            Assert::count($rawCellsValues, Coordinates::MAX);

            $values = [];
            foreach ($rawCellsValues as $value) {
                if ($value === '') {
                    $values[] = null;

                    continue;
                }

                $value = (int) $value;

                Assert::greaterThanEq($value, Value::MIN);
                Assert::lessThanEq($value, Value::MAX);

                $values[] = $value;
            }

            $gridAsArray[] = $values;
        }

        /** @var array<int<0,8>, array<int<0,8>, ?int<Value::MIN, Value::MAX>>> $gridAsArray */
        return $this->arrayGridFactory->create($gridAsArray);
    }
}
