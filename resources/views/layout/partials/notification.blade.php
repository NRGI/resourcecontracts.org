@if(Session::has('error'))
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only">Close</span>
        </button>
        {{ Session::get('error') }}
    </div>
@endif

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only">Close</span>
        </button>
        {{ Session::get('success') }}
    </div>
@endif
