<?php
namespace App\Nrgi\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Class CountryScope
 * @package app\Nrgi\Scope
 */
class CountryScope implements ScopeInterface
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
            $country = Auth::user()->country;
            if ($builder->getModel()->getTable() == "activity_logs") {
                $builder->whereHas(
                    'contract',
                    function ($q) use ($country) {
                        $q->whereRaw("contracts.metadata->'country'->>'code' in (?)", $country);
                    }
                );
            } elseif ($builder->getModel()->getTable() == "contract_annotations") {
                $builder->whereHas(
                    'contract',
                    function ($q) use ($country) {
                        $q->whereRaw("contracts.metadata->'country'->>'code' in (?)", $country);
                    }
                );
            } else {
                $builder->whereRaw("contracts.metadata->'country'->>'code' in (?)", $country);
            }
        }
    }

}
