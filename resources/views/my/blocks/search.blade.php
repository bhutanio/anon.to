<div class="block block-search">
    {!! Form::open(['files'=>false, 'method'=>'get', 'url'=>url($search_url), 'id'=>'form_search_links', 'class' => 'form-inline', 'role'=>'form']) !!}
    <div class="form-group">
        {!! Form::label('hash', 'Hash', ['class' => 'sr-only']) !!}
        {!! Form::text('hash', request()->get('hash'), ['class' => 'form-control', 'placeholder' => 'Hash']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('domain', 'Domain', ['class' => 'sr-only']) !!}
        {!! Form::text('domain', request()->get('domain'), ['class' => 'form-control', 'placeholder' => 'Domain']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('path', 'Path', ['class' => 'sr-only']) !!}
        {!! Form::text('path', request()->get('path'), ['class' => 'form-control', 'placeholder' => 'Path']) !!}
    </div>

    <button type="submit" class="btn btn-sm btn-primary" title="Search"><i class="glyphicon glyphicon-search"></i> Search</button>
    <a href="{{ url($search_url) }}" class="btn btn-sm btn-info" title="Reset"><i class="glyphicon glyphicon-remove-sign"></i> Reset</a>
    {!! Form::close() !!}
</div>