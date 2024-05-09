<?php

namespace App\Nrgi\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Class CountryScope
 * @package app\Nrgi\Scope
 */
class CountryScope implements Scope
{
    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model   $model
     *
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        // Implementation for removing scope if needed
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        if (!Auth::guest() && Auth::user()->hasCountryRole()) {
            $countryCode = Auth::user()->country; // Get the country code

            // Check if countryCode is accidentally an array or object
            if (is_array($countryCode) || is_object($countryCode)) {
                $countryCode = is_array($countryCode) ? $countryCode[0] : (string) $countryCode; // Simple fallback
            }

            // Apply the scope depending on the model
            if ($builder->getModel()->getTable() == "activity_logs" || $builder->getModel()->getTable() == "contract_annotations") {
                $builder->whereHas('contract', function ($q) use ($countryCode) {
                    $q->whereRaw("
                        EXISTS (
                            SELECT 1 
                            FROM json_array_elements(metadata->'countries') AS country 
                            WHERE country->>'code' = ?
                        )
                    ", [$countryCode]);
                });
            } else {
                $builder->whereRaw("
                    EXISTS (
                        SELECT 1 
                        FROM json_array_elements(metadata->'countries') AS country 
                        WHERE country->>'code' = ?
                    )
                ", [$countryCode]);
            }
        }
    }
}

