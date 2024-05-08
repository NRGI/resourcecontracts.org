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
        // TODO: Implement remove() method.
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        if (!Auth::guest() && Auth::user()->hasCountryRole()) {
            $countryCode = Auth::user()->country; // Get the country code, which should be a string
    
            // Check if countryCode accidentally is an array or object
            if (is_array($countryCode) || is_object($countryCode)) {
                $countryCode = is_array($countryCode) ? $countryCode[0] : (string) $countryCode;  // Simple fallback for example
            }
    
            // JSONB query with parameter binding
            $jsonQuery = '[{"code": ?}]';  // Corrected: removed "::jsonb" here
    
            if ($builder->getModel()->getTable() == "activity_logs" || $builder->getModel()->getTable() == "contract_annotations") {
                $builder->whereHas('contract', function ($q) use ($countryCode, $jsonQuery) {
                    $q->whereRaw("metadata->'countries' @> ?::jsonb", [$jsonQuery]); // Corrected: moved "::jsonb" to whereRaw parameter
                    $q->setBindings([$countryCode]);  // Ensure that the correct bindings are set
                });
            } else {
                $builder->whereRaw("metadata->'countries' @> ?::jsonb", [$jsonQuery]); // Corrected: moved "::jsonb" to whereRaw parameter
                $builder->setBindings([$countryCode]); // Ensure that the correct bindings are set
            }
        }
    }

    

}
