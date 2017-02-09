<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Guard;

class CheckPermission
{

    const DELIMITER = '|';
    protected $auth;

    /**
     * Creates a new instance of the middleware.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure                  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (!is_null($user)) {

            if ($user->hasRole(['superadmin', 'admin', 'country-admin'])) {
                return $next($request);
            }

            $permissions = $this->getPermissions($this->getRouteName($request));

            if (empty($permissions)) {
                return $next($request);
            }

            if ($this->auth->guest() || !$user->can($permissions)) {
                $referrer = $request->headers->get('referer');

                if ($referrer) {
                    return back()->withError("You don't have sufficient permissions.");
                }

                return view('errors.no-permission');
            }

            return $next($request);
        }

        return $next($request);
    }

    /**
     * write brief description
     *
     * @param $name
     *
     * @return array
     */
    public function getPermissions($name)
    {
        $permissions = $this->getPermissionsForRoute();

        try {
            return $permissions[$name];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get Permissions
     *
     * @return array
     */
    public function getPermissionsForRoute()
    {
        return [
            'contract.edit.trans'        => ['edit-contract'],
            'contract.store'             => ['add-contract'],
            'contract.update'            => ['edit-contract'],
            'contract.delete'            => ['delete-contract'],
            'contract.status.comment'    => ['unpublished-metadata'],
            'contract.show'              => ['add-contract'],
            'role'                       => ['unpublished-metadata'],
        ];

//        ['add-contract', 'edit-contract', 'delete-contract'];
//
//        complete, publish, reject, unpublished - text
//
//        (edit complete publish unpublished reject) - metadata
//
//        add, edit, delete, complete, publish, reject, unpublished - annotation
    }

    /**
     * Get Route Name
     *
     * @param $request
     *
     * @return string
     */
    protected function getRouteName($request)
    {
        $route = $request->route();
        $name  = '';

        if (isset($route->getAction()['as'])) {
            $name = $route->getAction()['as'] ?: '';
        }

        return $name;
    }
}
