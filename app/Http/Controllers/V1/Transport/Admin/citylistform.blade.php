{{ App::setLocale(  isset($_COOKIE['admin_language']) ? $_COOKIE['admin_language'] : 'en'  ) }}
<div class="modal-header">
    <h4 class="modal-title">{{ __('Menu City') }} {{ __('List') }}</h4>
    <button type="button" data-dismiss="modal" class="close">&times;</button>
</div>
<div class="row full-section-ser" style="border-top: none;">
    <div class="col-md-12 box-card price_lists_sty priceBody">
        <div class="form_pad">

            @if(!empty($id))
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" id="city_id" name="id" value="{{$id}}">
            @endif
            <form class="validateForm">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="country_list">Country</label>
                        <select class="form-control select2" name="country_name" id="country_list">

                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="state_list">State</label>
                        <select class="form-control select2" name="state_name" id="state_list">

                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="city_list">City</label>
                        <select class="form-control select2" name="city_name" id="city_list">

                        </select>
                    </div>
                </div>
                <input type="hidden" id="country_id" name="country_id" value="">
                <input type="hidden" id="service_id" name="service_id" value="">
                <table id="data-tables" class="table table-hover table_width display">
                    <thead>
                        <tr>
                            <th></th>
                            <th width="10px;">{{ __('admin.id') }}</th>
                            <th>{{ __('admin.menu.city') }}</th>
                            <!-- <th><input type="checkbox" id="checkAll" />{{ __('admin.action') }}</th> -->
                        </tr>
                    </thead>
                    <tbody id="menucityList">

                    </tbody>
                </table>
                <button type="reset" class="btn btn-danger cancel">{{ __('Cancel') }}</button>
                @if(Helper::getDemomode() != 1)
                <button type="submit" class="btn btn-accent float-right menucity">{{ __('Add') }}</button>
                @endif
            </form>
        </div>
    </div>
</div>
<style>
    .priceBody .pagination {
        display: none !important;
    }
</style>
<script type="text/javascript">
    var tableName = '#data-tables';
    var table1 = $(tableName);
    // console.log(table1)
    $(document).ready(function() {
        $('.select2').select2();
        basicFunctions();


            $('.modal').on('show.bs.modal', function() {
            $('#country_list').val(null);
        });
        var id = "";
        /*
         if($("input[name=id]").length){
            id = $("input[name=id]").val();
            var url = getBaseUrl() + "/admin/menucity/"+id;
            setData( url );
         }*/
        var ignored_cities = [];
        var ignored_cityall = '';
        $('body').on('change', '[name="city_id[]"]', function() {

            if (!$(this).is(":checked")) {
                ignored_cities.push($(this).val());
            }
        });
        $('body').on('change', '.countrylistdeleted', function() {
            if (!$(this).is(":checked")) {
                ignored_cityall = "ALL";
            } else {
                ignored_cityall = '';
            }
        });

        $('body').on('click', '.countrylist', function() {

            ignored_cities = [];

        });


        $('.validateForm').on('submit', function(e) {
            e.preventDefault();
            var data = new FormData();
            var formGroup = $(this).serialize().split("&");
            // console.log(formGroup);
            for (var i in formGroup) {
                var params = formGroup[i].split("=");
                data.append(decodeURIComponent(params[0]), decodeURIComponent(params[1]));
            }
            data.append('_method', 'PATCH');
            data.append('ignored_cities', ignored_cities);
            data.append('ignored_cityall', ignored_cityall);
            var url = getBaseUrl() + "/admin/menucity/" + id;
            if ($('[name="city_id[]"]:checked').length > 0) {
                saveRow(url, null, data);
            } else {
                alertMessage('Error', "You have to check atleast one City.", "danger");
            }
        });

        var id = $('#city_id').val();
        var country_id = $('#country_id').val();
        // console.log('{{$service}}')
        if ("{{$service}}" == "TRANSPORT") {
            var url = getBaseUrl() + "/admin/gettransportcity?menu_id=" + id;
        } else if ("{{$service}}" == "SERVICE") {
            var url = getBaseUrl() + "/admin/getservicecity?menu_id=" + id;
        } else {
            var url = getBaseUrl() + "/admin/getordercity?menu_id=" + id;
        }
        // console.log(getBaseUrl())
       table = table1.DataTable({
    "processing": true,
    "serverSide": true,
    "ordering": false,
    "ajax": {
        "url": url,
        "type": "GET",
        "headers": {
            "Authorization": "Bearer " + getToken("admin")
        },
        beforeSend: function() {
            showLoader();
        },
        data: function(data) {
            var info = $(tableName).DataTable().page.info();
            delete data.columns;

            data.page = info.page + 1;

            var selectedCountry = $('#country_id').val();
            if (!selectedCountry) {
                // Clear search parameters if no country is selected
                table.search('').draw();
                data.country_id = null;
                data.state_id = null;
                data.search_text = '';
            } else {
                data.country_id = selectedCountry;
                data.state_id = $('#state_list').val();
                data.search_text = data.search['value'] || ''; // Ensure search_text is an empty string if not provided
            }
            
        },
        dataFilter: function(data) {
            var json = parseData(data);
            json.recordsTotal = json.responseData.total;
            json.recordsFiltered = json.responseData.total;
            json.data = json.responseData.data;
            return JSON.stringify(json); // return JSON string
        }
    },
    "columns": [{
            "data": "id"
        },
        {
            "data": "id",
            render: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            },
        },
        {
            "data": "city_id",
            render: function(data, type, row, meta) {
                if (row.state) {
                    if (row.state_price == 1) {
                        return row.city.city_name;
                    } else {
                        return row.city.city_name + " - <span style='color:red'>  (Pricing Logic Is Required)</span>";
                    }
                } else {
                    return row.city.city_name;
                }
            }
        }
    ],
    "columnDefs": [{
        "targets": 0,
        "checkboxes": {
            'selectRow': true,
            'selectAllRender': function() {
                return '<input type="checkbox" class="dt-checked-main countrylistdeleted">';
            }
        },
        'render': function(data, type, row, meta) {
            var selected = '';
            if (row.menu_city) {
                selected = 'checked';
            }
            if (row.state) {
                if (row.state_price == 1) {
                    return '<input type="checkbox" name="city_id[]" class="dt-checkboxes dt-checked-main" ' + selected + ' value="' + row.city.id + '">';
                } else {
                    return '<input type="checkbox" name="city_id[]" class="dt-checkboxes dt-checked-main" ' + selected + ' value="' + row.city.id + '">';
                }
            } else {
                if (row.city_price == 1) {
                    return '<input type="checkbox" name="city_id[]" class="dt-checkboxes dt-checked-main" ' + selected + ' value="' + row.city.id + '">';
                } else {
                    return '<input type="checkbox" name="city_id[]" class="dt-checkboxes dt-checked-main" ' + selected + ' value="' + row.city.id + '">';
                }
            }
        }
    }],
    'select': {
        'style': 'multi'
    },
    'order': [
        [1, 'asc']
    ],
    responsive: true,
    paging: true,
    "scrollY": "350px",
    "scrollX": "450px",
    "scrollCollapse": true,
    searching: true,
    info: false,
    lengthChange: false,
    dom: 'Bfrtip',
    pageLength: 20,
    buttons: [

    ],
    "drawCallback": function() {

        var info = $(this).DataTable().page.info();
        if (info.pages <= 1) {
            $('#data-tables .dataTables_paginate').hide();
            $('#data-tables .dataTables_info').hide();
        } else {
            $('#data-tables .dataTables_paginate').show();
            $('#data-tables .dataTables_info').show();
        }
        hideLoader();
    }
});

        $(document).on('click', '.cancel', function() {
            $(".crud-modal").modal("hide");
        });

        menuSelectCities(id);

    });

    $('body').on('change', '#country_list', function() {
        var value = $(this).val();
        $('#state_list').html(``);
        $('#city_list').html(``);


        

        $.each(state[value], function(key, stateData) {
            if(key==0){
            var newOption = new Option("Select state", null, false, false);
            $('#state_list').append(newOption).trigger('change');
            }
            var newOption = new Option(stateData.state_name, stateData.id, false, false);
            $('#state_list').append(newOption).trigger('change');
        });

        $.each(city[value], function(key, cityData) {
             if(key==0){
            var newOption = new Option("Select City", null, false, false);
            $('#city_list').append(newOption).trigger('change');
            }
            var newOption = new Option(cityData.city_name, cityData.id, false, false);
            $('#city_list').append(newOption).trigger('change');
        });

    });

    $('#state_list').change(function() {
        table.draw();
    });

    function getPrice(id) {
        var url = getBaseUrl() + "/admin/transport/price/get/" + id;
        price_id = id;
        $.ajax({
            url: url,
            type: "GET",
            async: false,
            headers: {
                Authorization: "Bearer " + getToken("admin")
            },
            beforeSend: function(request) {
                showInlineLoader();
            },
            success: function(data) {
                console.log(data);

            var countryCityList = '';

            state = [];
            city = [];
            var data = parseData(data);
            var country_list = $('#country_list');

            country_list.empty();


            $.each(data.responseData, function(key, country) {
            
                selected = false;
                if (key == 0)
                {
                    var newOption = new Option("All Countries", null, true, true);
                    country_list.append(newOption);
                    selected = false;
                }

                var newOption = new Option(country.country_name, country.id, selected, selected);
                country_list.append(newOption);

                if (country.state) {
                    state[country.id] = country.state;
                }
                if (country.city) {
                    city[country.id] = country.city;
                }
            });
        }
        });
    }
</script>