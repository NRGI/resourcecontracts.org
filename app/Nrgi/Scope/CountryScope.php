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
            $countryCode = Auth::user()->country; // Assuming 'country_code' holds a single country code string

            if ($builder->getModel()->getTable() == "activity_logs" || $builder->getModel()->getTable() == "contract_annotations") {
                $builder->whereHas('contract', function ($q) use ($countryCode) {
                    $q->whereRaw("metadata->'countries' @> '[{\"code\":\"$countryCode\"}]'::jsonb");
                });
            } else {
                $builder->whereRaw("metadata->'countries' @> '[{\"code\":\"$countryCode\"}]'::jsonb");
            }
        }
    }

}
