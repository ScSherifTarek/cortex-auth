{{-- Master Layout --}}
@extends('cortex/fort::backend.common.layout')

{{-- Page Title --}}
@section('title')
    {{ config('app.name') }} » {{ trans('cortex/fort::common.roles') }} » {{ trans('cortex/fort::common.'.$mode) }} @if($role->exists) {{ $role->slug }} @endif
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
        @if($role->exists)
            @include('cortex/fort::backend.common.confirm-modal', ['type' => 'role'])
        @endif

        @if ($action === 'update')
            {{ Form::model($role, ['url' => route('backend.roles.update', ['role' => $role]), 'method' => 'put']) }}
        @else
            {{ Form::model($role, ['url' => route('backend.roles.store')]) }}
        @endif

            <section class="panel panel-default">

                {{-- Heading --}}
                <header class="panel-heading">
                    <h4>
                        <a href="{{ route('backend.roles.index') }}">{{ trans('cortex/fort::common.roles') }}</a> » {{ trans('cortex/fort::common.'.$mode) }} @if($role->exists) <strong>{{ $role->slug }}</strong> @endif
                        @if($role->exists)
                            <span class="pull-right" style="margin-top: -7px">
                                @can('delete-roles', $role) <a href="#" class="btn btn-default" data-toggle="modal" data-target="#delete-confirmation" data-item-href="{{ route('backend.roles.delete', ['role' => $role]) }}" data-item-name="{{ $role->slug }}"><i class="fa fa-trash-o text-danger"></i></a> @endcan
                                @can('create-roles') <a href="{{ route('backend.roles.create') }}" class="btn btn-default"><i class="fa fa-plus"></i></a> @endcan
                            </span>
                        @endif
                    </h4>
                </header>

                {{-- Data --}}
                <div class="panel-body">

                    <div class="row">
                        <div class="col-md-8">

                            {{-- Name --}}
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                {{ Form::label('name', trans('cortex/fort::common.name'), ['class' => 'control-label']) }}
                                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => trans('cortex/fort::common.name'), 'required' => 'required', 'autofocus' => 'autofocus']) }}

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div>
                        <div class="col-md-4">

                            {{-- Slug --}}
                            <div class="form-group{{ $errors->has('slug') ? ' has-error' : '' }}">
                                {{ Form::label('slug', trans('cortex/fort::common.slug'), ['class' => 'control-label']) }}
                                {{ Form::text('slug', null, ['class' => 'form-control', 'placeholder' => trans('cortex/fort::common.slug'), 'required' => 'required']) }}

                                @if ($errors->has('slug'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('slug') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">

                            {{-- Description --}}
                            <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                                {{ Form::label('description', trans('cortex/fort::common.description'), ['class' => 'control-label']) }}
                                {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => trans('cortex/fort::common.description'), 'rows' => 3]) }}

                                @if ($errors->has('description'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('description') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div>
                        @can('grant-abilities')
                        <div class="col-md-4">

                            {{-- Abilities --}}
                            <div class="form-group{{ $errors->has('abilities') ? ' has-error' : '' }}">
                                {{ Form::label('abilityList[]', trans('cortex/fort::common.abilities'), ['class' => 'control-label']) }}
                                {{ Form::select('abilityList[]', $abilityList, null, ['class' => 'form-control', 'multiple' => 'multiple', 'size' => 4]) }}

                                @if ($errors->has('abilities'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('abilities') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div>
                        @endcan
                    </div>

                </div>

                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-12">

                            @if($role->exists)
                                @if($role->created_at)
                                    <small><strong>{{ trans('cortex/fort::common.created_at') }}:</strong>
                                        <time datetime="{{ $role->created_at }}">{{ $role->created_at->format('Y-m-d') }}</time>
                                    </small>
                                @endif

                                @if($role->created_at && $role->updated_at) | @endif

                                @if($role->updated_at)
                                    <small><strong>{{ trans('cortex/fort::common.updated_at') }}:</strong>
                                        <time datetime="{{ $role->updated_at }}">{{ $role->updated_at->format('Y-m-d') }}</time>
                                    </small>
                                @endif
                            @endif

                            <div class="pull-right">
                                {{ Form::button(trans('cortex/fort::common.reset'), ['class' => 'btn btn-default', 'type' => 'reset']) }}
                                {{ Form::button(trans('cortex/fort::common.submit'), ['class' => 'btn btn-primary', 'type' => 'submit']) }}
                            </div>

                        </div>
                    </div>
                </div>
            </section>

        {{ Form::close() }}

    </div>

@endsection
