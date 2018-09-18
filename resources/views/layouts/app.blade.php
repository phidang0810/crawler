<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Zuri - Quoting</title>

    <!-- Styles -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<header>
    <div class="navbar navbar-dark bg-dark box-shadow">
        <div class="container d-flex justify-content-between">
            <a href="#" class="navbar-brand d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                    <circle cx="12" cy="13" r="4"></circle>
                </svg>
                <strong>Zuri</strong>
            </a>
        </div>
    </div>
</header>

<main role="main" style="margin-top: 20px">

    <div class="container">

        <div class="row">
            @yield('content')
        </div>
    </div>

</main>

<footer class="text-muted">
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="{{ asset('js/moment.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js"></script>
<script src="{{ asset('js/fontawesome-all.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>


<script>
    var url_quote_manna = "{{route('get-quote-manna')}}";
    var url_quote_fc = "{{route('get-quote-fc')}}";
    var url_quote_convey = "{{route('get-quote-convey')}}";
    var url_quote_priority = "{{route('get-quote-priority')}}";
    var totalRequest = 4;
    var totalManna = totalFC = totalConvey = 1;
    var quoteData;
    $.xhrPool = [];

    $(document).ready(function () {
        $('.bt-add').on('click', function () {
            var num = document.getElementById('num-product').value++;
            html = addItem(num);
            $('#product-lists').append(html);
            return false;
        });
        $('#product-lists').on('click', '.bt-remove', function () {
            var id = $(this).attr('data-id');
            $('#item' + id).remove();
            return false;
        });
        $('#pickup_date').datetimepicker({
            'format': 'L'
        });
        generatePallets(1);

        $('#product-lists').on('change', '.ip-freight', function () {
            var parent = $(this).parent().parent();
            var length = parent.find('#ip-length').val();
            var width = parent.find('#ip-width').val();
            var height = parent.find('#ip-height').val();
            var weight = parent.find('#ip-weight').val();
            if (length && width && height && weight) {
                var freight = getFreightClass(length, width, height, weight);
                if(freight) $(this).parent().parent().find('#ip-freight').val(freight);
            }
        });

        $.extend($.validator.messages, {
            required: "Required.",
            min: jQuery.validator.format("Invalid.")
        });

        $.fn.serializeObject = function(){

            var self = this,
                json = {},
                push_counters = {},
                patterns = {
                    "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                    "key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
                    "push":     /^$/,
                    "fixed":    /^\d+$/,
                    "named":    /^[a-zA-Z0-9_]+$/
                };


            this.build = function(base, key, value){
                base[key] = value;
                return base;
            };

            this.push_counter = function(key){
                if(push_counters[key] === undefined){
                    push_counters[key] = 0;
                }
                return push_counters[key]++;
            };

            $.each($(this).serializeArray(), function(){

                // skip invalid keys
                if(!patterns.validate.test(this.name)){
                    return;
                }

                var k,
                    keys = this.name.match(patterns.key),
                    merge = this.value,
                    reverse_key = this.name;

                while((k = keys.pop()) !== undefined){

                    // adjust reverse_key
                    reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

                    // push
                    if(k.match(patterns.push)){
                        merge = self.build([], self.push_counter(reverse_key), merge);
                    }

                    // fixed
                    else if(k.match(patterns.fixed)){
                        merge = self.build([], k, merge);
                    }

                    // named
                    else if(k.match(patterns.named)){
                        merge = self.build({}, k, merge);
                    }
                }

                json = $.extend(true, json, merge);
            });

            return json;
        };

        $("#quoting-form").validate({
            errorElement: 'div',
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                $("#quoting-form").addClass('was-validated');
                element.attr('required', true);
                if (element.next().is('label')) {
                    error.insertAfter(element.next());
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function (form) {
                $('#bt-submit').attr("disabled", true);
                $('#bt-submit').html('<i class="fas fa-spinner fa-spin"></i> Loading ...');
                quoteData = {
                    general: {},
                    fc:[],
                    manna:[],
                    convey:[],
                    priority:[]
                };
                var numOfService = $("#sl-service").val().length;
                totalRequest *= numOfService;
                totalManna *= numOfService;
                totalFC *= numOfService;
                totalConvey *= numOfService;
                var orderNum = $('#ip-order').val();
                $("#order-number").text(orderNum);
                data = $(form).serializeArray();
                loadModal(data);
                quoteData.general = $(form).serializeObject();

                $.each(data, function (k, v) {
                    if (v.name == 'service_level_array[]') {
                        data.push({
                            name: "shipping_method",
                            value: v.value
                        });
                        sendRequest($.param(data), url_quote_manna, '#result-content-manna', quoteData.manna);
                        sendRequest($.param(data), url_quote_fc, '#result-content-fc', quoteData.fc);
                        sendRequest($.param(data), url_quote_convey, '#result-content-convey', quoteData.convey);
                        sendRequest($.param(data), url_quote_priority, '#result-content-priority', quoteData.priority);
                    }
                });

                return false;
            }
        });
    });

    function loadModal(data)
    {
        $.ajax({
            url: "{{route('loadModal')}}",
            success: function (data) {
                $('#mModal').html(data);
                $('#result-modal').modal({backdrop: "static"}, 'show');
                $('#result-modal').on('hidden.bs.modal', function (e) {
                    $.xhrPool.abortAll = function() {
                        $(this).each(function (idx, jqXHR) {
                            console.log(jqXHR);
                            jqXHR.abort();
                        });
                    };
                    $.xhrPool.abortAll();
                })
            }
        });
    }

    function generatePallets(num) {
        html = '';
        for (var i = 0; i < num; i++) {
            html += addItem(i);
        }
        document.getElementById('product-lists').innerHTML += html;
    }

    function sendRequest(form, url, elementResult, commonResult) {
        $.ajax({
            type: 'post',
            url: url,
            data: data,
            dataType:'json',
            beforeSend: function (jqXHR) {
                $.xhrPool.push(jqXHR);
            },
            success: function (result) {
                if (result.quotes) {
                    $.each(result.quotes, function (k, v) {
                        var html = '<tr data-value="'+v.quote+'">\n' +
                            '          <td scope="col">' + v.carrier_name + '</td>' +
                            '          <td scope="col">' + v.shipping_method + '</td>' +
                            '          <td scope="col">' + v.service_level.join("<br/>") + '</td>' +
                            '          <td scope="col" class="text-right">' + v.transit_days + '</td>' +
                            '          <td scope="col" class="text-center">' + v.est_delivery + '</td>' +
                            '          <td scope="col" class="text-right">$' + v.quote + '</td>' +
                            '      </tr>';
                        $(elementResult).find('table tbody').append(html);
                        commonResult.push(v);
                    });
                } else {
                    console.log(result);
                }
            },
            error: function (xhr) {
                if (xhr.status == 401) alert("Login failed. Please check your account");
                if (xhr.status == 422) alert(xhr.responseJSON.message);
            },
            complete: function (jqXHR) {
                var i = $.xhrPool.indexOf(jqXHR);
                if (i > -1) $.xhrPool.splice(i, 1);

                totalRequest--;
                if (totalRequest === 0) {
                    $('#bt-submit').removeAttr("disabled");
                    $('#bt-submit').html('<i class="fas fa-search"></i> Get quote');
                    $('#bt-export').removeAttr("disabled");
                    $('#bt-export').html('<i class="fas fa-file-excel"></i> Export CSV');
                    totalRequest = 4;
                    $('#data-export').val(JSON.stringify(quoteData));
                }
            }
        });
    }

    function addItem(i) {
        var deleteButton = '';
        if (i !== 0) deleteButton = '<div class="col-sm-1 col-md-1 mb-3 text-right">' +
            '                   <label>&nbsp;</label>' +
            '                   <button type="button" class="btn btn-secondary bt-remove" data-id="' + i + '"><i class="fas fa-minus-circle"></i></button>' +
            '                </div>';
        var html = '<div class="form-row product-item" id=item' + i + '>' +
            '                                    <div class="col-sm-1 col-md-1 mb-3">' +
            '                                        <label>Pallet#</label>' +
            '                                        <input type="number" class="form-control required number" value="1" data-rule-min=1 name="pallets[' + i + '][num_of_pallet]">' +
            '                                    </div>' +
            '                                    <div class="col-sm-1 col-md-1 mb-3">' +
            '                                        <label>Carton#</label>' +
            '                                        <input type="number" class="form-control required number" value="1" data-rule-min=1 name="pallets[' + i + '][num_of_carton]">' +
            '                                    </div>' +
            '                                    <div class="col-sm-1 col-md-1 mb-3">' +
            '                                        <label>Length</label>' +
            '                                        <input type="number" id="ip-length" class="ip-freight form-control required number" data-rule-min=1 name="pallets[' + i + '][length]">' +
            '                                    </div>' +
            '                                    <div class="col-sm-1 col-md-1 mb-3">' +
            '                                        <label>Width</label>' +
            '                                        <input type="number" id="ip-width" class="ip-freight form-control required number" data-rule-min=1 name="pallets[' + i + '][width]">' +
            '                                    </div>' +
            '                                    <div class="col-sm-1 col-md-1 mb-3">' +
            '                                        <label>Height</label>' +
            '                                        <input type="number" id="ip-height" class="ip-freight form-control required number" data-rule-min=1 name="pallets[' + i + '][height]">' +
            '                                    </div>' +
            '                                    <div class="col-sm-2 col-md-2 mb-3">' +
            '                                        <label>Weight</label>' +
            '                                        <input type="number" id="ip-weight" class="ip-freight form-control required number" data-rule-min=1 name="pallets[' + i + '][weight]">' +
            '                                    </div>' +
            '                                    <div class="col-sm-2 col-md-2 mb-3">' +
            '                                        <label>Freight class</label>' +
            '                                        <input type="text" name="pallets[' + i + '][freight_class]" class="form-control" readonly id="ip-freight" />'+
            '                                    </div>' +
            '                                    <div class="col-sm-2 col-md-2 mb-3">' +
            '                                        <label>Declared Value</label>' +
            '                                        <input type="number" class="form-control required number" data-rule-min=1 name="pallets[' + i + '][dec_value]">' +
            '                                    </div>' + deleteButton + '</div>';
        return html;
    }

    function getFreightClass(length, width, height, weight) {
        const FREIGHT_DESITY = {
            50: '50',
            35: '55',
            30: '60',
            22: '65',
            15: '70',
            13: '77.5',
            12: '85',
            10: '92.5',
            9: '100',
            8: '110',
            7: '125',
            6: '150',
            5: '175',
            4: '200',
            3: '250',
            2: '300',
            1: '400',
            0: '500'
        };
        var cubicInch = length * width * height;
        var cubicFet = cubicInch / 1728;
        var poundPerCubic = weight / cubicFet;
        var defineNumber = Math.floor(poundPerCubic);
        if (FREIGHT_DESITY[defineNumber]) return FREIGHT_DESITY[defineNumber];
        return '50';
    }
</script>
</body>
</html>
