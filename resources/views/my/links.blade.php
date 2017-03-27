@extends('layouts.app')

@section('content')
    <h1 class="page-title">{{ meta()->pageTitle() }}</h1>
    @include('my.blocks.search', ['search_url'=>(auth()->id()==2 ? 'admin/links' : 'my')])
    <div class="block block-data">
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered text-wrap">
                <thead>
                <tr>
                    <th style="width: 80px;">Hash</th>
                    <th>URL</th>
                    <th style="width: 120px;">Added</th>
                    @if(auth()->id()==2)
                        <th style="width: 120px;">By</th>
                        <th style="width: 48px;"></th>
                    @endif
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
                            <td><button type="button" data-id="{{ $link->id }}" class="btn btn-sm btn-danger btn_delete_link"><i class="glyphicon glyphicon-trash"></i></button></td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="pull-left" style="">{!! $links->render() !!}</div>
@endsection

@section('footer_js')
    <script>
        deleteLink();
    </script>
@endsection