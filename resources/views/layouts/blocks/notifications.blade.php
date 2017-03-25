@if (!empty($errors) && count($errors->all()) > 0)
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>Please check the form below for errors</p>
    </div>
@endif

@if($flash_message = session()->get('flash_message'))
    <div class="alert alert-{{ $flash_message['type'] }} alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>{{ $flash_message['message'] }}</p>
    </div>
@endif