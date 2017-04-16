<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Rinvex\Fort\Models\Role;
use Rinvex\Fort\Models\User;
use Rinvex\Fort\Models\Ability;
use Cortex\Foundation\Http\Controllers\AuthorizedController;

class UsersController extends AuthorizedController
{
    /**
     * {@inheritdoc}
     */
    protected $resource = 'users';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::paginate(config('rinvex.fort.backend.items_per_page'));

        return view('cortex/fort::backend.users.index', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->process($request, new User());
    }

    /**
     * Update the given resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Rinvex\Fort\Models\User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        return $this->process($request, $user);
    }

    /**
     * Delete the given resource from storage.
     *
     * @param \Rinvex\Fort\Models\User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(User $user)
    {
        $user->delete();

        return intend([
            'url' => route('backend.users.index'),
            'with' => ['warning' => trans('cortex/fort::messages.user.deleted', ['userId' => $user->id])],
        ]);
    }

    /**
     * Show the form for create/update of the given resource.
     *
     * @param \Rinvex\Fort\Models\User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function form(User $user)
    {
        $countries = array_map(function ($country) {
            return $country['name'];
        }, countries());

        $languages = array_map(function ($language) {
            return $language['name'];
        }, languages());

        $abilityList = Ability::all()->groupBy('resource')->map(function ($item) {
            return $item->pluck('name', 'id');
        })->toArray();

        $roleList = Role::all()->pluck('name', 'id')->toArray();

        return view('cortex/fort::backend.users.form', compact('user', 'abilityList', 'roleList', 'countries', 'languages'));
    }

    /**
     * Process the form for store/update of the given resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Rinvex\Fort\Models\User $user
     *
     * @return \Illuminate\Http\Response
     */
    protected function process(Request $request, User $user)
    {
        // Prepare required input fields
        $input = $request->all();
        $input['email_verified'] = $request->get('email_verified', false);
        $input['phone_verified'] = $request->get('phone_verified', false);

        // Remove empty password fields
        if (! $input['password']) {
            unset($input['password']);
        }

        // Save user
        $user->fill($input)->save();

        // Sync abilities
        if ($request->user($this->getGuard())->can('grant-abilities')) {
            $user->abilities()->sync((array) array_pull($input, 'abilityList'));
        }

        // Sync roles
        if ($request->user($this->getGuard())->can('assign-roles')) {
            $user->roles()->sync((array) array_pull($input, 'roleList'));
        }

        return intend([
            'url' => route('backend.users.index'),
            'with' => ['success' => trans('cortex/fort::messages.user.saved', ['userId' => $user->id])],
        ]);
    }
}
