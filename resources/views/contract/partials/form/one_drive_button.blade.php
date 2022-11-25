<div>
    @if($is_one_drive_authenticated)
    <p class="help-block font-weight-bold"">@lang('contract.one_drive_authenticated')
    <a href="{{$one_drive_auth_link}}">@lang('contract.authenticate_again')</a></p>
    @else
    <p class="help-block">@lang('contract.import.one_drive_help')</p>
    <a href="{{$one_drive_auth_link}}">@lang('contract.one_drive')</a>
    @endif
</div>