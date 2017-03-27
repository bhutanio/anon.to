@extends('layouts.app')

@section('content')
    <h1 class="page-title">{{ meta()->pageTitle() }}</h1>

    <div class="block block-data">
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered">
                <thead>
                <tr>
                    <th>Link</th>
                    <th>Hash</th>
                    <th>Comment</th>
                    <th>On</th>
                    <th>By</th>
                </tr>
                </thead>
                <tbody>
                @foreach($reports as $report)
                    <tr>
                        <td><a href="{{ $report->url }}" target="_blank">{{ $report->url }}</a></td>
                        @if(!empty($report->link->hash))
                            <td><a href="{{ url('admin/links?hash='.$report->link->hash) }}" target="_blank">{{ $report->link->hash }}</a></td>
                        @else
                            <td><span class="text-danger"><strong>DELETED</strong></span></td>
                        @endif
                        <td>{{ $report->comment }}</td>
                        <td>{{ carbon($report->created_at)->diffForHumans() }}</td>
                        <td>{{ $report->email }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="pull-left" style="">{!! $reports->render() !!}</div>
@endsection

@section('footer_js')
    <script>
        deleteLink();
    </script>
@endsection