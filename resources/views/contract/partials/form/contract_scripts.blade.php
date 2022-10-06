@section('script')
    <script src="{{asset('js/bootstrap-filestyle.min.js')}}"></script>
    <script src="{{asset('js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('js/jquery-ui.js')}}"></script>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/jquery.datetimepicker.js')}}"></script>
    <script src="{{asset('js/mustache.min.js')}}"></script>
    @include('contract.company_template')
    <script>
        $('#file').filestyle({
            buttonText : '@lang('contract.choose_file')',
            buttonName : 'btn-primary',
            placeholder: '@lang('contract.pdf_only')'
        });
        var lang_select = '@lang('global.select')';
        var i = {{$i ?? 0}};
        var j = {{$j ?? 0}};
        var g = {{$g ?? 0}};
        var country_list = {!!json_encode($country_list)!!};
        var contracts = {!!json_encode($contracts)!!};
        var docId = {!!json_encode($docId)!!};
        var govEntity = {!!json_encode($govEntity)!!};
        var companyName= {!! json_encode($companyName) !!};

        @if(isset($edit_trans))
        var arr = ['#contract_identifier', '#signature_date', '#signature_year', '#source_url', '#date_retrieval',
                    '.el_government_identifier', '#deal_number', '#matrix_page', 'select', 'input:checkbox',
                    'input:radio', '.open_corporate_id', '.participation_share', '.company_founding_date',
                    '.company_number'];
        $('.col-sm-7').click(function () {
            var inp = $(this).find('input');
            if (inp.length > 0 && inp.prop('disabled') == true) {
                console.log('input is disabled');
            }
        });

        $('.add-new-btn').hide();
        $.each(arr, function (index, value) {
            $(value).parent().parent().addClass("form-group-disabled");
            $(value).prop('disabled', true);
        });
        @endif
    </script>
    <script src="{{asset('js/contract.js')}}"></script>
@stop