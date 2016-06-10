$(function () {
    $('select').select2({placeholder: lang_select, allowClear: true, theme: "classic"});
    $('.parent_company').select2({placeholder: lang_select, allowClear: true, tags: true, theme: "classic"});
    $('.resource-list').select2({placeholder: lang_select, allowClear: true, tags: true, theme: "classic"});
    $('.el_government_entity').select2({
        placeholder: lang_select, allowClear: true, tags: true, theme: "classic"
    });

    $('.contract-form').validate();

    $('.datepicker').datetimepicker({
        timepicker: false,
        format: 'Y-m-d',
        scrollInput: false
    });


    $('body').on('hidden.bs.modal', '.modal-comment', function (event) {
        var modal = $(this);
        modal.removeData('bs.modal');
    });

    $('body').on('show.bs.modal', '.modal-comment', function (event) {
        var modal = $(this);
        modal.find('.modal-content').html('<div style="padding: 40px;"> Loading...</div>');
    });

    var input = '<span class="red input-required">*</span><input class="form-control required other_toc" name="type_of_contract[]" type="text">';


    $(document).on('change', '#type_of_contract', function () {
        if (($(this).val() == 'Other')) {
            $(this).parent().append(input)
        } else {
            if ($('.other_toc').length) {
                input = $('.other_toc');
            }
            $('.other_toc').remove();
        }
    });

    var input_dt = '<span class="red input-required">*</span><input class="form-control required dt" name="document_type" type="text">';

    $(document).on('change', '#document_type', function () {

        if (($(this).val() == 'Other')) {
            var dt = $('.el_document_type .dt');
            if ($('.dt').length) {
                $('.dt').show();
            } else {
                $(this).parent().append(input_dt)
            }
        } else {
            $('.dt').remove();
        }

        if ($(this).val() == 'Contract') {
            $('#type_of_contract').addClass("required");
            $('label[for="type_of_contract"] span.red').removeClass("hidden");
        } else {
            $('#type_of_contract').removeClass("required");
            $('label[for="type_of_contract"] span.red').addClass("hidden");
        }
    });
    $('#document_type').trigger('change');

    var input_disclosure_mode = '<span class="red input-required">*</span><input class="form-control required disclosure_mode_other" name="disclosure_mode" type="text" />';
    $(document).on('change', '#disclosure_mode', function () {
        if (($(this).val() == 'Other')) {
            $(this).parent().append(input_disclosure_mode)
        } else {
            if ($('.disclosure_mode_other').length) {
                input_disclosure_mode = $('.disclosure_mode_other');
            }
            $('.disclosure_mode_other').remove();
        }
    });

    translation();

    function translation() {
        var div = $('.translation-parent');
        if ($('.translation:checked').val() == 1) {
            div.removeClass('hide');
        }
        else {
            div.addClass('hide');
        }
    }

    $('.translation').on('change', function () {
        translation();
    });

    $(document).on('click', '.company .item .delete', function (e) {
        $(this).parent().remove();
        var key = $(this).data('key');
        if (typeof key != 'undefined') {
            var input = $('.delete_company');
            key = input.val() == '' ? key : input.val() + ',' + key;
            input.val(key);
        }
    });
    $('.new-company').on('click', function (e) {
        e.preventDefault();
        i += 1;
        var template = $('#company-template').html();
        Mustache.parse(template);
        var rendered = Mustache.render(template, {item: i});
        $('.company .item:last-child').after(rendered);
        $('select').select2({placeholder: lang_select, allowClear: true, theme: "classic"});
        $('.parent_company').select2({placeholder: lang_select, allowClear: true, tags: true, theme: "classic"});

        $('.datepicker').datetimepicker({
            timepicker: false,
            format: 'Y-m-d',
            scrollInput: false
        });
        init_autocomplete();
    })

    $(document).on('change', '.corporate_grouping', function () {
        var corporate_grouping_mode = '<span class="red input-required">*</span><input class="form-control required corporate_grouping_other" name="company[' + i + '][parent_company]" type="text">';
        if (($(this).val() == 'Other')) {
            $(this).parent().append(corporate_grouping_mode)
        } else {
            if ($('.corporate_grouping_other').length) {
                corporate_grouping_mode = $('.corporate_grouping_other');
            }
            $('.corporate_grouping_other').remove();
        }
    });


    $(document).on('click', '.concession .con-item .delete', function (e) {
        $(this).parent().remove();
        var key = $(this).data('key');
        if (typeof key != 'undefined') {
            var input = $('.delete_concession');
            key = input.val() == '' ? key : input.val() + ',' + key;
            input.val(key);
        }
    });

    $('.new-concession').on('click', function (e) {
        e.preventDefault();
        j += 1;
        var template = $('#concession-template').html();
        Mustache.parse(template);
        var rendered = Mustache.render(template, {item: j});
        $('.concession .con-item:last-child').after(rendered);
    })

    $(document).on('click', '.government_entity .government-item .delete', function (e) {
        $(this).parent().remove();
        var key = $(this).data('key');
        if (typeof key != 'undefined') {
            var input = $('.delete_government_entity');
            key = input.val() == '' ? key : input.val() + ',' + key;
            input.val(key);
        }
    });

    $('.new-government-entity').on('click', function (e) {
        e.preventDefault();
        g += 1;
        var template = $('#government-entity').html();
        Mustache.parse(template);
        var rendered = Mustache.render(template, {item: g});
        $('.government_entity .government-item:last-child').after(rendered);
        var elementId = $("#government_" + g + "_entity");
        var options = getEntitiesOptions($('#country').val());
        elementId.empty();
        elementId.append(options).trigger('change');
        $('.el_government_entity').select2({
            placeholder: lang_select, allowClear: true, tags: true, theme: "classic"
        });

    });


    $(document).on('click', '.selected-document .document .delete', function (e) {
        $(this).parent().remove();
        var id = $(this).context.id;
        docId.pop(Number(id));
    });
    var eventSelect = $(".select-document");
    eventSelect.on("select2:select", function (e) {
        var args = JSON.stringify(e.params, function (key, value) {
            var data = value.data;


            var check = docId.indexOf(Number(data.id));
            docId.push(Number(data.id));
            var template = $('#document').html();
            Mustache.parse(template);
            var rendered = Mustache.render(template, {id: data.id, name: data.text});
            if (check < 0) {
                $("#selected-document").append(rendered);
            }
        });

    });


    $(document).on('change', '.signature_date', function (e) {
        var date = $(".signature_date").val();
        date = date.split("-");
        $(".signature_year").val(date[0]);
        if (date[0] != "") {
            $(".signature_year").attr("readonly", true);
        } else {
            $(".signature_year").removeAttr("readonly");
        }

    });

    $('.el_government_entity').select2({
        placeholder: lang_select, allowClear: true, tags: true, theme: "classic"
    });

    $(document).on('change', '#country', function (e) {
        var country = $(this).val();
        loadGovernmentEntities(country);
    });

    $(document).on('change','#country',function(){
       $('.el_government_identifier').val('');
    });


    function getEntitiesOptions(country) {
        var entities = govEntity[country];
       var options = '';
        options += "<option value=''>"+lang_select+"</option>";
        if (entities) {
            for (i = 0; i < entities.length; i++) {
                options += "<option value ='" + entities[i]['entity'] + "'>" + entities[i]['entity'] + "</option>";
            }
        }

        return options;
    }

    function loadGovernmentEntities(country) {
        var options = getEntitiesOptions(country);
        $('.el_government_entity').empty();
        $('.el_government_entity').append(options).trigger('change');
    }

    $(document).on('change', '.el_government_entity', function (e) {
        var country = $('#country').val();
        var entities = govEntity[country];
        var entity = $(this).val();
        if (entities) {
            identifier = "";
            for (i = 0; i < entities.length; i++) {
                if (entities[i]['entity'] == entity) {
                    identifier = entities[i]['identifier'];
                }
            }

            $(this).parent().parent().parent().find('.el_government_identifier').val(identifier)
        }
    });

    $("#show-new-document").click(function (e) {
        e.preventDefault();
        $("#new-document").toggle();
    });

    function init_autocomplete()
    {
        $( ".company_name" ).autocomplete({
            source: function( request, response){
                var results= $.ui.autocomplete.filter(companyName, request.term);
                response(results.slice(0,10));
            }
        });

    }
    init_autocomplete();


    $('input:radio[name="is_supporting_document"]').change(
        function(){
            if ($(this).is(':checked') && $(this).val() == 1) {
                $(".parent-document").css('display', 'block');
            }
            else{
                $(".parent-document").css('display', 'none');
            }
        });


    $('#generate-contract-name').on('click', function () {
        var companies = $('.company_name');
        var company = [];
        companies.map(function (index, item) {
            var c = $(item).val();
            company.push(c);
        });
        company = company.join(" - ");

        var license = [];
        var count = 0;
        var licenses =  $('.license-name');

        $(licenses).each(function(index,item){
            if($(item).val() === '') {
                count = count+1;
            }
        });

        if(count >0){
            licenses = $('.license_identifier');
            licenses.map(function (index, item) {
                var li = $(item).val();
                license.push(li);
            });
            license = license.join(" - ");
        }
        else
        {
            licenses.map(function (index, item) {
                var l = $(item).val();
                license.push(l);
            });
            license = license.join(" - ");
        }


        var type_of_contract = null;

        if ($('#type_of_contract').val() === null) {
            type_of_contract = $('#document_type').val();

            if (type_of_contract == 'Other') {
                type_of_contract = $('.dt').val();
            }
        }
        else {
            type_of_contract = $('#type_of_contract').val();

            if (type_of_contract == 'Other') {
                type_of_contract = $('.other_toc').val();
            }
            else
                type_of_contract = type_of_contract.join(" - ");
        }

        var signature_year = $('.signature_year').val();

        var contract_name = company +',' +license + ','+type_of_contract + ','+signature_year;
        if (contract_name !== ',,,') {
            $('.contract_name').val(contract_name);
        }

    });

});




