@extends('errors.error')

@section('page_title')
@endsection

@section('error_title')
    Error 404: {{ $exception->getMessage() }}
@endsection
