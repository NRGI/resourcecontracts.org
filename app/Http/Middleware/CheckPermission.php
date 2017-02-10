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
            'contract.edit.trans'     => ['edit-contract'],
            'contract.store'          => ['add-contract'],
            'contract.update'         => ['edit-contract'],
            'contract.delete'         => ['delete-contract'],
            'contract.status.comment' => ['unpublished-metadata'],
            'contract.show'           => ['add-contract'],

            'annotation.save'      => ['add-annotation'],
            'annotation.save.put'  => ['add-annotation'],
            'annotation.save.post' => ['add-annotation'],
            'annotation.delete'    => ['delete-annotation'],
            'annotation.update'    => ['edit-annotation'],

            'contract.import'              => ['add-contract'],
            'contract.import.post'         => ['add-contract'],
            'contract.import.confirm'      => ['add-contract'],
            'contract.import.confirm.post' => ['add-contract'],
            'contract.import.status'       => ['add-contract'],
            'contract.import.delete'       => ['add-contract'],
            'contract.import.notify'       => ['add-contract'],

            'utility.index'  => ['edit-contract'],
            'utility.submit' => ['edit-contract'],

            'mturk.index'           => ['mturk-view'],
            'mturk.allTasks'        => ['mturk-view'],
            'mturk.add'             => ['mturk-view', 'mturk-send-to-rc'],
            'mturk.activity'        => ['mturk-view'],
            'mturk.tasks'           => ['mturk-view'],
            'mturk.contract.copy'   => ['mturk-view', 'mturk-send-to-rc'],
            'mturk.task.approveAll' => ['mturk-view', 'mturk-review'],
            'mturk.task.detail'     => ['mturk-view', 'mturk-review'],
            'mturk.task.approve'    => ['mturk-view', 'mturk-review'],
            'mturk.task.reject'     => ['mturk-view', 'mturk-review'],
            'mturk.task.reset'      => ['mturk-view', 'mturk-review'],
        ];

//        add, edit, delete - contract
//
//        complete, publish, reject, unpublished - text   --- remove complete
//
//        edit, complete, publish, unpublished, reject - metadata --- remove complete/edit
//
//        (add, edit, delete,)/create complete, publish, reject, unpublished - annotation -- remove add, edit, delete,
//  add
// create
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
