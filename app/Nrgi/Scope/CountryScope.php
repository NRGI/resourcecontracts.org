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
            $countryCode = Auth::user()->country; // Ensure this is a string.
    
            // Check if countryCode accidentally is an array or object
            if (is_array($countryCode) || is_object($countryCode)) {
                $countryCode = is_array($countryCode) ? $countryCode[0] : (string) $countryCode;
            }
    
            // Correctly prepare JSON string for query
            $jsonQuery = json_encode([["code" => $countryCode]]); // Encode as JSON string
    
            if ($builder->getModel()->getTable() == "activity_logs" || $builder->getModel()->getTable() == "contract_annotations") {
                $builder->whereHas('contract', function ($q) use ($jsonQuery) {
                    $q->whereRaw("metadata->'countries'::jsonb @> ?", [$jsonQuery]); // Cast 'countries' to jsonb and bind JSON as parameter
                });
            } else {
                $builder->whereRaw("metadata->'countries'::jsonb @> ?", [$jsonQuery]); // Cast 'countries' to jsonb and bind JSON as parameter
            }
        }
    }
    

    

}
