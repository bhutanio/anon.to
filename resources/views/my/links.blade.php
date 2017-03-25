@extends('layouts.app')

@section('content')
    <h1 class="page-title">{{ meta()->pageTitle() }}</h1>
    @include('my.blocks.search', ['search_url'=>'my'])
    <div class="block block-data">
        <table class="table table-condensed table-striped table-bordered">
            <thead>
            <tr>
                <th>Hash</th>
                <th>URL</th>
                <th>Added</th>
                @if(auth()->id()==2)
                    <th>By</th>
                @endif
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($links as $link)
                <tr>
                    <td><a href="{{ url($link->hash) }}" target="_blank">{{ $link->hash }}</a></td>
                    <td><a href="{{ $link->full_url }}" target="_blank">{{ $link->full_url }}</a></td>
                    <td>{{ carbon($link->created_at)->diffForHumans() }}</td>
                    @if(auth()->id()==2)
                        <td>{{ $link->user->username }}</td>
                    @endif
                    <td>
                        <button type="button" data-id="{{ $link->id }}" class="btn btn-sm btn-danger btn_delete_link">
                            <i class="glyphicon glyphicon-trash"></i></button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="pull-left" style="">{!! $links->render() !!}</div>
@endsection