<?php namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Nrgi\Services\Contract\CountryService;
use App\Nrgi\Services\User\UserService;
use Illuminate\Auth\Guard;
use Psr\Log\LoggerInterface;

/**
 * Class RoleController
 * @property LoggerInterface logger
 * @package App\Http\Controllers\Contract
 */
class RoleController extends Controller
{
    /**
     * @var UserService
     */
    protected $user;
    /**
     * @var Guard
     */
    protected $auth;
    /**
     * @var CountryService
     */
    protected $countries;

    /**
     * @param UserService     $user
     * @param CountryService  $countries
     * @param Guard           $auth
     *
     * @param LoggerInterface $logger
     *
     * @internal param CountryService $country
     */
    public function __construct(UserService $user, CountryService $countries, Guard $auth, LoggerInterface $logger)
    {
        $this->middleware('auth');
        $this->user   = $user;
        $this->auth   = $auth;
        $this->logger = $logger;

        if ($this->auth->user() && !$this->auth->user()->hasRole(['superadmin', 'admin', 'country-admin'])) {
            return redirect('/home')->withError(trans('contract.permission_denied'))->send();
        }

        $this->countries = $countries;
    }

    /**
     * Lists Roles
     */
    public function index()
    {
        $roles       = $this->user->getAllRolesWithPermissions();
        $permissions = $this->user->getPermissionsList();

        return view('user.role.index', compact('roles', 'permissions'));
    }

    /**
     * Stores Role added by user
     *
     * @param RoleRequest $request
     *
     * @return JSON
     */
    public function store(RoleRequest $request)
    {
        $role_detail = [
            'name'         => str_slug($request->input('name'), '-'),
            'display_name' => $request->input('name'),
            'description'  => $request->input('description'),
        ];
        $permissions = $request->input('permissions');

        try {
            $this->user->createRole($role_detail, $permissions);
            $this->logger->info("Role successfully created. ".json_encode($role_detail));

            return json_encode(
                [
                    'result'       => 'success',
                    "message"      => "Role successfully created.",
                    'name'         => $role_detail['name'],
                    'display_name' => $role_detail['display_name'],
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return json_encode(['result' => 'failed', "message" => "There was some error."]);
        }
    }

    /**
     * Updates Role
     *
     * @param RoleRequest $request
     *
     * @return string
     */
    public function update(RoleRequest $request)
    {
        $id = $request->input('role-id');
        $role_detail = [
            'name'         => str_slug($request->input('name'), '-'),
            'display_name' => $request->input('name'),
            'description'  => $request->input('description'),
        ];
        $permissions = $request->input('permissions');

        try {
            $this->user->updateRole($id, $role_detail, $permissions);
            $this->logger->info("Role successfully updated. ".json_encode($role_detail));

            return json_encode(
                [
                    'result'       => 'success',
                    "message"      => "Role successfully updated.",
                    'name'         => $role_detail['name'],
                    'display_name' => $role_detail['display_name'],
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return json_encode(['result' => 'failed', "message" => "There was some error."]);
        }
    }

    /**
     * Deletes User Defined Role
     *
     * @param $id
     *
     * @return \Illuminate\View\View
     */
    public function delete($id)
    {
        if (!$this->user->doesRoleBelongToAnyUser($id)) {

            if ($this->user->deleteRole($id)) {
                return redirect()->route('role')->withSuccess(trans('user.role_delete_success'));
            }

            return redirect()->route('role')->withError(trans('user.role_delete_fail'));
        }

        return redirect()->route('role')->withError(trans('user.role_belongs_to_user'));
    }
}
