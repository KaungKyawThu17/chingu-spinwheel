<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('surveys') || ! Schema::hasColumn('surveys', 'event_id')) {
            return;
        }

        $indexes = DB::select(
            "SELECT INDEX_NAME as index_name, NON_UNIQUE as non_unique, COLUMN_NAME as column_name, SEQ_IN_INDEX as seq_in_index
            FROM information_schema.statistics
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'surveys'"
        );

        $byIndex = [];
        foreach ($indexes as $index) {
            $name = $index->index_name;
            if (! isset($byIndex[$name])) {
                $byIndex[$name] = [
                    'non_unique' => (int) $index->non_unique,
                    'columns' => [],
                ];
            }

            $byIndex[$name]['columns'][(int) $index->seq_in_index] = $index->column_name;
        }

        $phoneUniqueIndex = null;
        $hasCompositeUnique = false;

        foreach ($byIndex as $name => $info) {
            if ($info['non_unique'] !== 0) {
                continue;
            }

            $columns = $info['columns'];
            ksort($columns);
            $columns = array_values($columns);

            if ($columns === ['phone']) {
                $phoneUniqueIndex = $name;
                continue;
            }

            if (count($columns) === 2) {
                $sorted = $columns;
                sort($sorted);
                if ($sorted === ['event_id', 'phone']) {
                    $hasCompositeUnique = true;
                }
            }
        }

        if ($phoneUniqueIndex) {
            Schema::table('surveys', function (Blueprint $table) use ($phoneUniqueIndex): void {
                $table->dropUnique($phoneUniqueIndex);
            });
        }

        if (! $hasCompositeUnique) {
            Schema::table('surveys', function (Blueprint $table): void {
                $table->unique(['event_id', 'phone'], 'surveys_event_id_phone_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('surveys')) {
            return;
        }

        $indexes = DB::select(
            "SELECT INDEX_NAME as index_name, NON_UNIQUE as non_unique, COLUMN_NAME as column_name, SEQ_IN_INDEX as seq_in_index
            FROM information_schema.statistics
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'surveys'"
        );

        $byIndex = [];
        foreach ($indexes as $index) {
            $name = $index->index_name;
            if (! isset($byIndex[$name])) {
                $byIndex[$name] = [
                    'non_unique' => (int) $index->non_unique,
                    'columns' => [],
                ];
            }

            $byIndex[$name]['columns'][(int) $index->seq_in_index] = $index->column_name;
        }

        $compositeIndex = null;

        foreach ($byIndex as $name => $info) {
            if ($info['non_unique'] !== 0) {
                continue;
            }

            $columns = $info['columns'];
            ksort($columns);
            $columns = array_values($columns);

            if (count($columns) !== 2) {
                continue;
            }

            $sorted = $columns;
            sort($sorted);
            if ($sorted === ['event_id', 'phone']) {
                $compositeIndex = $name;
                break;
            }
        }

        if ($compositeIndex) {
            Schema::table('surveys', function (Blueprint $table) use ($compositeIndex): void {
                $table->dropUnique($compositeIndex);
            });
        }
    }
};
