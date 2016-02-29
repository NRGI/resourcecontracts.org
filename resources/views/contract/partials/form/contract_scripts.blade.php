@section('script')
    <script src="{{asset('js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/jquery.datetimepicker.js')}}"></script>
    <script src="{{asset('js/mustache.min.js')}}"></script>
    <script src="{{asset('js/lib/underscore.js')}}"></script>
    <script src="{{asset('js/lib/backbone.js')}}"></script>
    @include('contract.company_template')
    <script>
        var i = {{$i or 0}};
        var j = {{$j or 0}};
        var g = {{$g or 0}};
        var country_list = {!!json_encode($country_list)!!};
        var contracts = {!!json_encode($contracts)!!};
        var docId = {!!json_encode($docId)!!};
        var govEntity = {!!json_encode($govEntity)!!};
        var country_code = {!!json_encode($country_code)!!};
        var selectedGovEntity = {!!json_encode($selectedGovEntity)!!};
    </script>
    <script src="{{asset('js/contract.js')}}"></script>
@stop
