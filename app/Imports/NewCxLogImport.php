<?php

namespace App\Imports;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NewCxLogImport implements ToCollection, WithHeadingRow
{

    public function __construct(
        public readonly Connection $connection
    ) {}

    /**
     * @param  Collection  $rows
     * @return void
     */
    public function collection(Collection $rows): void
    {
        $rows->chunk(1000)->each(function ($chunk) {
            $values = $chunk->map(function ($row) {
                return '('. implode(', ', array_map(fn ($row) => isset($row) ? "'" . addslashes($row). "'" :
                        'null', $row->toArray())). ')';
            })->implode(', ');

            $columns = implode(', ', $chunk->first()->keys()->toArray());

            $query = "INSERT INTO new_cx_log ({$columns}) VALUES {$values} ON CONFLICT(id) DO NOTHING";

            $this->connection->statement($query);
        });
    }

}
