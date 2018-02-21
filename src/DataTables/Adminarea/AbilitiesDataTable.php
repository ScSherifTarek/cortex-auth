<?php

declare(strict_types=1);

namespace Cortex\Auth\DataTables\Adminarea;

use Cortex\Auth\Models\Ability;
use Cortex\Foundation\DataTables\AbstractDataTable;

class AbilitiesDataTable extends AbstractDataTable
{
    /**
     * {@inheritdoc}
     */
    protected $model = Ability::class;

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $currentUser = $this->request->user($this->request->route('guard'));

        $query = $currentUser->can('superadmin') ? app($this->model)->query() : app($this->model)->query()->whereIn('id', $currentUser->getAbilities()->pluck('id')->toArray());

        return $this->applyScopes($query);
    }

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return datatables($this->query())
            ->orderColumn('title', 'title->"$.'.app()->getLocale().'" $1')
            ->make(true);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        $link = config('cortex.foundation.route.locale_prefix')
            ? '"<a href=\""+routes.route(\'adminarea.abilities.edit\', {ability: full.id, locale: \''.$this->request->segment(1).'\'})+"\">"+data+"</a>"'
            : '"<a href=\""+routes.route(\'adminarea.abilities.edit\', {ability: full.id})+"\">"+data+"</a>"';

        return [
            'title' => ['title' => trans('cortex/auth::common.title'), 'render' => $link, 'responsivePriority' => 0],
            'name' => ['title' => trans('cortex/auth::common.name')],
            'created_at' => ['title' => trans('cortex/auth::common.created_at'), 'render' => "moment(data).format('MMM Do, YYYY')"],
            'updated_at' => ['title' => trans('cortex/auth::common.updated_at'), 'render' => "moment(data).format('MMM Do, YYYY')"],
        ];
    }
}
