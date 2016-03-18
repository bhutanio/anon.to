@extends('errors.error')

@section('page_title')
@endsection

@section('error_title')
    Error 403: {{ $exception->getMessage() }}
@endsection

@section('message')
    <p><strong>You shall not pass!</strong> Unauthorized access to this page is prohibited.</p>
@endsection
