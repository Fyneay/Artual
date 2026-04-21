<?php

namespace Database\Migrations\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait DropsForeignKeys
{
    protected function dropForeignKeys(string $table, array $columns): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        $database = $connection->getDatabaseName();

        foreach ($columns as $column) {
            if ($driver === 'pgsql') {
                $constraint = $connection->selectOne(
                    "SELECT tc.constraint_name
                     FROM information_schema.table_constraints AS tc
                     JOIN information_schema.key_column_usage AS kcu
                       ON tc.constraint_name = kcu.constraint_name
                       AND tc.table_schema = kcu.table_schema
                     WHERE tc.constraint_type = 'FOREIGN KEY'
                       AND tc.table_schema = ?
                       AND tc.table_name = ?
                       AND kcu.column_name = ?
                     LIMIT 1",
                    [$connection->getConfig('schema') ?? 'public', $table, $column]
                );

                if ($constraint) {
                    DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint->constraint_name}");
                }
            } else {
                $foreignKey = $connection->selectOne(
                    "SELECT CONSTRAINT_NAME
                     FROM information_schema.KEY_COLUMN_USAGE
                     WHERE TABLE_SCHEMA = ?
                     AND TABLE_NAME = ?
                     AND COLUMN_NAME = ?
                     AND REFERENCED_TABLE_NAME IS NOT NULL
                     LIMIT 1",
                    [$database, $table, $column]
                );

                if ($foreignKey) {
                    DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
                }
            }
        }
    }
}

