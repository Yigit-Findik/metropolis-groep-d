<?php

namespace App\Services;

use App\Models\CityFunction;
use Illuminate\Support\Facades\Schema;

class CityFunctionEffectValueService
{
    public function effectColumns(): array
    {
        $excludedColumns = [
            'id',
            'name',
            'category',
            'qol_score',
            'image_path',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        return array_values(array_filter(
            Schema::getColumnListing((new CityFunction())->getTable()),
            fn (string $column) => ! in_array($column, $excludedColumns, true)
        ));
    }

    public function missingEffectColumns(CityFunction $function): array
    {
        $persistedFunction = $function->fresh() ?? $function;
        $missingColumns = [];

        foreach ($this->effectColumns() as $column) {
            $value = $persistedFunction->getAttribute($column);

            if ($value === null || $value === '') {
                $missingColumns[] = $column;

                continue;
            }

            if (! is_numeric($value)) {
                $missingColumns[] = $column;

                continue;
            }

            $numericValue = (int) $value;

            if ($numericValue < -10 || $numericValue > 10) {
                $missingColumns[] = $column;
            }
        }

        return $missingColumns;
    }

    public function allRequiredEffectValuesAreFilledAndSaved(CityFunction $function): bool
    {
        return $this->missingEffectColumns($function) === [];
    }

    public function hasCompleteValues(CityFunction $function): bool
    {
        return $this->allRequiredEffectValuesAreFilledAndSaved($function);
    }

    public function hasChangedEffectValues(CityFunction $function): bool
    {
        foreach ($this->effectColumns() as $column) {
            if ($function->wasChanged($column)) {
                return true;
            }
        }

        return false;
    }
}