{{-- Master Layout --}}
@extends('cortex/foundation::backend.layouts.default')

{{-- Page Title --}}
@section('title')
    {{ config('app.name') }} » {{ trans('cortex/foundation::common.backend') }}
@stop

{{-- Main Content --}}
@section('content')

    <div class="content-wrapper">

        <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-md-9">

                    <div class="row">

                        <div class="col-md-6">
                            <h1><i class="fa fa-dashboard"></i> {{ trans('cortex/foundation::common.backend') }}</h1>
                        </div>

                    </div>
                </div>

                <div class="col-md-3">

                    <div class="row">
                        <div class="col-md-12">

                            <!-- Widget: user widget style 1 -->
                            <div class="box box-widget widget-user-2">
                                <!-- Add the bg color to the header using any of the bg-* classes -->
                                <div class="box-footer no-padding">
                                    <ul class="nav nav-stacked">
                                        <li><a href="{{ route('backend.abilities.index') }}">{{ trans('cortex/fort::common.abilities') }} <span class="pull-right badge bg-red">{{ $stats['abilities']['count'] }}</span></a></li>
                                        <li><a href="{{ route('backend.roles.index') }}">{{ trans('cortex/fort::common.roles') }} <span class="pull-right badge bg-yellow">{{ $stats['roles']['count'] }}</span></a></li>
                                        <li><a href="{{ route('backend.users.index') }}">{{ trans('cortex/fort::common.users') }} <span class="pull-right badge bg-blue">{{ $stats['users']['count'] }}</span></a></li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /.widget-user -->

                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12">

                            <div class="box box-success">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        {{ trans('cortex/fort::common.online_users', ['mins' => config('rinvex.fort.online_interval')]) }}
                                    </h3>
                                    <div class="box-tools pull-right">
                                        <span class="pull-right badge bg-green">{{ $sessions->count() }}</span>
                                    </div>
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body">
                                    <ul class="nav nav-stacked">
                                        @foreach($sessions as $session)
                                            <li>
                                                <strong>
                                                    @if($session->user->first_name)
                                                        {{ $session->user->first_name }} {{ $session->user->middle_name }} {{ $session->user->last_name }}
                                                    @else
                                                        {{ $session->user->username }}
                                                    @endif
                                                </strong>
                                                <span class="small ">{{ $session->user->job_title }}</span>
                                                <span class="pull-right">
                                                    @if($session->user_id == $currentUser->id)<span class="label label-info">{{ trans('cortex/fort::common.you') }}</span>@endif
                                                    <span class="badge">{{ $session->last_activity->diffForHumans() }}</span>
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <!-- /.box-body -->
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->

        </section>
        <!-- /.content -->
    </div>

@endsection
