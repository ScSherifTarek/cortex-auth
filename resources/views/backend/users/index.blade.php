{{-- Master Layout --}}
@extends('cortex/fort::backend.common.layout')

{{-- Page Title --}}
@section('title')
    » {{ trans('cortex/fort::common.users.label') }}
@stop

{{-- Main Content --}}
@section('content')

    <style>
        td {
            vertical-align: middle !important;
        }
    </style>

    <div class="container">

        @include('cortex/fort::frontend.alerts.success')
        @include('cortex/fort::frontend.alerts.warning')
        @include('cortex/fort::frontend.alerts.error')
        @include('cortex/fort::backend.common.confirm-modal', ['type' => 'user'])

        <section class="panel panel-default">

            {{-- Heading --}}
            <header class="panel-heading">
                <h4>
                    {{ trans('cortex/fort::common.users.label') }}
                    @can('create-users')
                        <span class="pull-right" style="margin-top: -7px">
                            <a href="{{ route('backend.users.create') }}" class="btn btn-default"><i class="fa fa-plus"></i></a>
                        </span>
                    @endcan
                </h4>
            </header>

            {{-- Data --}}
            <div class="panel-body">

                <div class="table-responsive">

                    <table class="table table-hover" style="margin-bottom: 0">

                        <thead>
                            <tr>
                                <th style="width: 20%">{{ trans('cortex/fort::common.name') }}</th>
                                <th style="width: 20%">{{ trans('cortex/fort::common.contact') }}</th>
                                <th style="width: 20%">{{ trans('cortex/fort::common.roles.label') }}</th>
                                <th style="width: 10%">{{ trans('cortex/fort::common.status') }}</th>
                                <th style="width: 15%">{{ trans('cortex/fort::common.created_at') }}</th>
                                <th style="width: 15%">{{ trans('cortex/fort::common.updated_at') }}</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($users as $user)

                                <tr>
                                    <td>
                                        @can('update-users', $user) <a href="{{ route('backend.users.edit', ['user' => $user]) }}"> @endcan
                                            <strong>
                                                @if($user->first_name)
                                                    {{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}
                                                @else
                                                    {{ $user->username }}
                                                @endif
                                            </strong>
                                            <div class="small ">{{ $user->job_title }}</div>
                                        @can('update-users', $user) </a> @endcan
                                    </td>

                                    <td>
                                        <div>
                                            {{ $user->email }} @if($user->email_verified) <span title="{{ $user->email_verified_at }}"><i class="fa text-success fa-check"></i></span> @endif
                                        </div>
                                        <div>
                                            {{ $user->phone }} @if($user->phone_verified) <span title="{{ $user->phone_verified_at }}"><i class="fa text-success fa-check"></i></span> @endif
                                        </div>
                                    </td>

                                    <td>
                                        @foreach($user->roles->pluck('title', 'id') as $roleId => $role)
                                            @can('update-roles', $role) <a href="{{ route('backend.roles.edit', ['role' => $roleId]) }}" class="label btn-xs label-info">{{ $role }}</a> @else {{ $role }} @endcan
                                        @endforeach
                                    </td>

                                    <td>
                                        @if($user->active)
                                            <span class="label label-success">{{ trans('cortex/fort::common.active') }}</span>
                                        @else
                                            <span class="label label-warning">{{ trans('cortex/fort::common.inactive') }}</span>
                                        @endif
                                    </td>

                                    <td class="small">
                                        @if($user->created_at)
                                            <div>
                                                {{ trans('cortex/fort::common.created_at') }}: <time datetime="{{ $user->created_at }}">{{ $user->created_at->format('Y-m-d') }}</time>
                                            </div>
                                        @endif
                                        @if($user->updated_at)
                                            <div>
                                                {{ trans('cortex/fort::common.updated_at') }}: <time datetime="{{ $user->updated_at }}">{{ $user->updated_at->format('Y-m-d') }}</time>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        @can('update-users', $user) <a href="{{ route('backend.users.edit', ['user' => $user]) }}" class="btn btn-xs btn-default"><i class="fa fa-pencil text-primary"></i></a> @endcan
                                        @can('delete-users', $user) <a href="#" class="btn btn-xs btn-default" data-toggle="modal" data-target="#delete-confirmation" data-item-href="{{ route('backend.users.delete', ['user' => $user]) }}" data-item-name="{{ $user->username }}"><i class="fa fa-trash-o text-danger"></i></a> @endcan
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

            {{-- Pagination --}}
            @include('cortex/fort::backend.common.pagination', ['resource' => $users])

        </section>

    </div>

@endsection
