@extends('layouts.app')

@section('content')
    <h1 class="page-title">{{ meta()->pageTitle() }}</h1>
    <div class="block">
        {!! Form::open(['files'=>false, 'url'=>url('report'), 'id'=>'form_report', 'class' => 'form-horizontal', 'role'=>'form']) !!}

        <div class="form-group{{ $errors->has('url') ? ' has-error' : '' }}">
            {!! Form::label('url', 'Link', ['class' => 'col-sm-2 control-label']) !!}
            <div class="col-sm-8">
                {!! Form::url('url', old('url'), ['class' => 'form-control', 'placeholder'=>'Paste a URL', 'required', 'autofocus']) !!}
                @if ($errors->has('url'))
                    <span class="help-block"><strong>{{ $errors->first('url') }}</strong></span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
            {!! Form::label('email', 'Your E-Mail', ['class' => 'col-sm-2 control-label']) !!}
            <div class="col-sm-8">
                {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder'=>'Your E-Mail Address', 'required']) !!}
                @if ($errors->has('email'))
                    <span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('comment') ? ' has-error' : '' }}">
            {!! Form::label('comment', 'Comment', ['class' => 'col-sm-2 control-label']) !!}
            <div class="col-sm-8">
                {!! Form::textarea('comment', old('comment'), ['class' => 'form-control', 'placeholder'=>'Reason for reporting the link', 'required']) !!}
                @if ($errors->has('comment'))
                    <span class="help-block"><strong>{{ $errors->first('comment') }}</strong></span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }}">
            {!! Form::label('g-recaptcha-response', 'Verify', ['class'=>'col-sm-2 control-label']) !!}
            <div class="col-sm-8">
                <div class="g-recaptcha" data-sitekey="{{ env('API_GOOGLE_RECAPTCHA_CLIENT') }}"></div>
                @if ($errors->has('g-recaptcha-response'))
                    <span class="help-block form-error">{{ $errors->first('g-recaptcha-response')  }}</span>
                @endif
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-8 col-sm-offset-2">
                {!! Form::submit('Report', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>

        {!! Form::close() !!}
    </div>
@endsection

@section('footer_js')
    <script src='https://www.google.com/recaptcha/api.js'></script>
@endsection