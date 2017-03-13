{{-- Master Layout --}}
@extends('cortex/fort::frontend.common.layout')

{{-- Page Title --}}
@section('title')
    {{ config('app.name') }} » {{ trans('common.password_reset') }}
@stop

{{-- Main Content --}}
@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <section class="panel panel-default">
                    <header class="panel-heading">{{ trans('common.welcome') }}</header>

                    <div class="panel-body">
                        {!! trans('common.welcome_body') !!}
                    </div>
                </section>
            </div>
        </div>
    </div>

@endsection
