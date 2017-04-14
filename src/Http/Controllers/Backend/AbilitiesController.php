<?php

/*
 * NOTICE OF LICENSE
 *
 * Part of the Cortex Fort Module.
 *
 * This source file is subject to The MIT License (MIT)
 * that is bundled with this package in the LICENSE file.
 *
 * Package: Cortex Fort Module
 * License: The MIT License (MIT)
 * Link:    https://rinvex.com
 */

declare(strict_types=1);

namespace Cortex\Fort\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Rinvex\Fort\Models\Ability;
use Cortex\Foundation\Http\Controllers\AuthorizedController;

class AbilitiesController extends AuthorizedController
{
    /**
     * {@inheritdoc}
     */
    protected $resource = 'abilities';

    /**
     * {@inheritdoc}
     */
    protected $resourceActionWhitelist = ['grant'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $abilities = Ability::paginate(config('rinvex.fort.backend.items_per_page'));

        return view('cortex/fort::backend.abilities.index', compact('abilities'));
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
        return $this->process($request, new Ability());
    }

    /**
     * Update the given resource in storage.
     *
     * @param \Illuminate\Http\Request    $request
     * @param \Rinvex\Fort\Models\Ability $ability
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ability $ability)
    {
        return $this->process($request, $ability);
    }

    /**
     * Delete the given resource from storage.
     *
     * @param \Rinvex\Fort\Models\Ability $ability
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Ability $ability)
    {
        $ability->delete();

        return intend([
            'url' => route('backend.abilities.index'),
            'with' => ['warning' => trans('cortex/fort::messages.ability.deleted', ['abilityId' => $ability->id])],
        ]);
    }

    /**
     * Show the form for create/update of the given resource.
     *
     * @param \Rinvex\Fort\Models\Ability $ability
     *
     * @return \Illuminate\Http\Response
     */
    public function form(Ability $ability)
    {
        return view('cortex/fort::backend.abilities.form', compact('ability', 'resources'));
    }

    /**
     * Process the form for store/update of the given resource.
     *
     * @param \Illuminate\Http\Request    $request
     * @param \Rinvex\Fort\Models\Ability $ability
     *
     * @return \Illuminate\Http\Response
     */
    protected function process(Request $request, Ability $ability)
    {
        // Prepare required input fields
        $input = $request->all();

        // Verify valid policy
        if (! empty($input['policy']) && (($class = mb_strstr($input['policy'], '@', true)) === false || ! method_exists($class, str_replace('@', '', mb_strstr($input['policy'], '@'))))) {
            return intend([
                'back' => true,
                'withInput' => $request->all(),
                'withErrors' => ['policy' => trans('cortex/fort::messages.ability.invalid_policy')],
            ]);
        }

        // Save ability
        $ability->fill($input)->save();

        return intend([
            'url' => route('backend.abilities.index'),
            'with' => ['success' => trans('cortex/fort::messages.ability.saved', ['abilityId' => $ability->id])],
        ]);
    }
}
