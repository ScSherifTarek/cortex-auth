{{-- Master Layout --}}
@extends('cortex/foundation::guestarea.layouts.auth')

{{-- Page Title --}}
@section('title')
    {{ config('app.name') }} » {{ trans('cortex/fort::common.password_reset') }}
@stop

{{-- Scripts --}}
@push('scripts')
    {!! JsValidator::formRequest(Cortex\Fort\Http\Requests\Guestarea\PasswordResetPostProcessRequest::class)->selector('#guestarea-passwordreset-process') !!}
@endpush

{{-- Main Content --}}
@section('content')

    <div class="login-box">
        <div class="login-logo">
            <a href="{{ route('guestarea.home') }}"><b>{{ config('app.name') }}</b></a>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p class="login-box-msg">{{ trans('cortex/fort::common.password_reset') }}</p>

            {{ Form::open(['url' => route('guestarea.passwordreset.process'), 'id' => 'guestarea-passwordreset-process']) }}

                {{ Form::hidden('expiration', old('expiration', $expiration)) }}
                {{ Form::hidden('token', old('token', $token)) }}

                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    {{ Form::email('email', old('email', $email), ['class' => 'form-control', 'placeholder' => trans('cortex/fort::common.email'), 'required' => 'required', 'readonly' => 'readonly']) }}

                    @if ($errors->has('email'))
                        <span class="help-block">{{ $errors->first('email') }}</span>
                    @endif
                </div>

                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    {{ Form::password('password', ['class' => 'form-control', 'placeholder' => trans('cortex/fort::common.new_password'), 'required' => 'required', 'autofocus' => 'autofocus']) }}

                    @if ($errors->has('password'))
                        <span class="help-block">{{ $errors->first('password') }}</span>
                    @endif
                </div>

                <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                    {{ Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => trans('cortex/fort::common.new_password_confirmation'), 'required' => 'required']) }}

                    @if ($errors->has('password_confirmation'))
                        <span class="help-block">{{ $errors->first('password_confirmation') }}</span>
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-12">
                        {{ Form::button('<i class="fa fa-envelope"></i> '.trans('cortex/fort::common.password_reset'), ['class' => 'btn btn-primary btn-flat', 'type' => 'submit']) }}
                    </div>
                    <!-- /.col -->
                </div>

            {{ Form::close() }}

        </div>
        <!-- /.login-box-body -->
    </div>

@endsection