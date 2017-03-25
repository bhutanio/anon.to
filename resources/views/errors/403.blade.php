@extends('errors.error')

@section('page_title')
@endsection

@section('error_title')
    <i class="glyphicon glyphicon-exclamation-sign text-danger"></i> Error 403: Access Denied!
@endsection

@section('error_message')
    <p class="text-center">You are not authorized to access this page.</p>
@endsection
