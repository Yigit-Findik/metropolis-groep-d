<?php

namespace App\Services;

use App\Models\CityFunction;

class CityFunctionEffectValueService
{
    public function effectColumns(): array
    {
        return CityFunction::EFFECT_COLUMNS;
    }

    public function missingEffectColumns(CityFunction $function): array
    {
        // Refresh the model from the database so the completeness check uses what is actually stored, not unsaved mutations.
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

            // A field counts as complete only when it contains a numeric value inside the allowed score window.
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
        // Compare the dirty state column-by-column so the observer can react only when an effect field changed.
        foreach ($this->effectColumns() as $column) {
            if ($function->wasChanged($column)) {
                return true;
            }
        }

        return false;
    }
}