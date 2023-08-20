@extends('layouts.admin.app')

@section('title', translate('messages.expense_report'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mb-2">
                <span class="page-header-icon">
                    <img src="{{ asset('public/assets/admin/img/report.png') }}" class="w--22" alt="">
                </span>
                <span>
                    {{ translate('messages.expense_report') }}
                </span>
            </h1>
            <div class="__page-header-txt">
                {{ translate('messages.expense_report_discription') }}
            </div>

        </div>

        <!-- End Page Header -->

        <div class="card mb-20">
            <div class="card-body">
                <h4 class="">{{ translate('Search Data') }}</h4>
                <form method="get">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <select name="zone_id" class="form-control js-select2-custom"
                                onchange="set_zone_filter('{{ url()->full() }}',this.value)" id="zone">
                                <option value="all">{{ translate('messages.All Zones') }}</option>
                                @foreach (\App\Models\Zone::orderBy('name')->get(['id','name']) as $z)
                                    <option value="{{ $z['id'] }}"
                                        {{ isset($zone) && $zone->id == $z['id'] ? 'selected' : '' }}>
                                        {{ $z['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select name="restaurant_id" onchange="set_restaurant_filter('{{ url()->full() }}',this.value)"
                                data-placeholder="{{ translate('messages.select') }} {{ translate('messages.restaurant') }}"
                                class="js-data-example-ajax form-control">
                                @if (isset($restaurant))
                                    <option value="{{ $restaurant->id }}" selected>{{ $restaurant->name }}</option>
                                @else
                                    <option value="all" selected>{{ translate('messages.all') }} {{ translate('messages.restaurants') }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select name="customer_id" onchange="set_customer_filter('{{ url()->full() }}',this.value)"
                                data-placeholder="{{ translate('messages.select') }} {{ translate('messages.customer') }}"
                                class="js-data-example-ajax-2 form-control">
                                @if (isset($customer))
                                    <option value="{{ $customer->id }}" selected>{{ $customer->f_name . ' ' .$customer->l_name }}</option>
                                @else
                                    <option value="all" selected>{{ translate('messages.all') }} {{ translate('messages.customers') }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select class="form-control" name="filter"
                                onchange="set_time_filter('{{ url()->full() }}',this.value)">
                                <option value="all_time" {{ isset($filter) && $filter == 'all_time' ? 'selected' : '' }}>
                                    {{ translate('messages.All Time') }}</option>
                                <option value="this_year" {{ isset($filter) && $filter == 'this_year' ? 'selected' : '' }}>
                                    {{ translate('messages.This Year') }}</option>
                                <option value="previous_year"
                                    {{ isset($filter) && $filter == 'previous_year' ? 'selected' : '' }}>
                                    {{ translate('messages.Previous Year') }}</option>
                                <option value="this_month"
                                    {{ isset($filter) && $filter == 'this_month' ? 'selected' : '' }}>
                                    {{ translate('messages.This Month') }}</option>
                                <option value="this_week" {{ isset($filter) && $filter == 'this_week' ? 'selected' : '' }}>
                                    {{ translate('messages.This Week') }}</option>
                                <option value="custom" {{ isset($filter) && $filter == 'custom' ? 'selected' : '' }}>
                                    {{ translate('messages.Custom') }}</option>
                            </select>
                        </div>
                        @if (isset($filter) && $filter == 'custom')
                            <div class="col-sm-6 col-md-3">
                                <input type="date" name="from" id="from_date" class="form-control"
                                    placeholder="{{ translate('Start Date') }}"
                                    value={{ $from ? $from  : '' }} required>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <input type="date" name="to" id="to_date" class="form-control"
                                    placeholder="{{ translate('End Date') }}"
                                    value={{ $to ? $to  : '' }}  required>
                            </div>
                        @endif
                        <div class="col-sm-6 col-md-3 ml-auto">
                            <button type="submit"
                                class="btn btn-primary btn-block">{{ translate('Filter') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- End Stats -->
        <!-- Card -->
        <div class="card mt-3">
            <!-- Header -->
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h3 class="card-title">
                        {{ translate('messages.expense') }} {{ translate('messages.lists') }} <span
                            class="badge badge-soft-secondary" id="countItems">{{ $expense->total() }}</span>
                    </h3>
                    <form  class="search-form">
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input name="search" value="{{ request()->search ?? null }}"   type="search" class="form-control" placeholder="{{ translate('Search by Order ID or type ') }}">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
                    <!-- Static Export Button -->
                    <div class="hs-unfold ml-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn font--sm"
                            href="javascript:;"
                            data-hs-unfold-options="{
                                &quot;target&quot;: &quot;#usersExportDropdown&quot;,
                                &quot;type&quot;: &quot;css-animation&quot;
                            }"
                            data-hs-unfold-target="#usersExportDropdown" data-hs-unfold-invoker="">
                            <i class="tio-download-to mr-1"></i> {{ translate('export') }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right hs-unfold-content-initialized hs-unfold-css-animation animated hs-unfold-reverse-y hs-unfold-hidden">

                            <span class="dropdown-header">{{ translate('download_options') }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.report.expense-export', ['type'=>'excel',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.report.expense-export', ['type'=>'csv',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>
                    <!-- Static Export Button -->
                </div>
            </div>
            <!-- End Header -->

            <!-- Body -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless middle-align __txt-14px">
                        <thead class="thead-light white--space-false">
                            <tr>
                                <th >{{translate('sl')}}</th>
                                <th class="text-center" >{{translate('messages.order_id')}}</th>
                                <th class="text-center" >{{translate('Date & Time')}}</th>
                                <th class="text-center" >{{ translate('Expense Type') }}</th>
                                <th class="text-center" >{{ translate('Customer Name') }}</th>
                                <th class="border-0 text-right pr-xl-5">
                                    <div class="pr-xl-5">
                                        {{translate('expense amount')}}
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach ($expense as $key => $exp)
                            <tr>
                                <td scope="row">{{$key+$expense->firstItem()}}</td>
                                <td class="text-center" >
                                    {{-- <div> --}}
                                        @if (isset($exp['order_id']))
                                        <a href="{{route('admin.order.details',['id'=>$exp['order_id']])}}">{{$exp['order_id']}}</a>
                                        @else
                                        <label class="badge badge-danger">{{translate('messages.invalid')}} {{translate('messages.order')}} {{translate('messages.data')}}</label>
                                        @endif

                                    {{-- </div> --}}
                                </td>
                                <td class="text-center">
                                    {{  Carbon\Carbon::parse($exp->created_at)->locale(app()->getLocale())->translatedFormat('d M Y '.config('timeformat')) }}

                                    {{-- {{$exp->created_at->format('Y-m-d '.config('timeformat'))}} --}}
                                </td>
                                <td class="text-center" >
                                {{Str::title(translate("messages.{$exp['type']}"))}}</td>

                                <td class="text-center">
                                    @if (isset($exp->order->customer))
                                    {{ $exp->order->customer->f_name.' '.$exp->order->customer->l_name }}
                                    @else
                                    <label class="badge badge-danger">{{translate('messages.invalid')}} {{translate('messages.customer')}} {{translate('messages.data')}}</label>

                                    @endif
                                </td>
                                <td class="text-right pr-xl-5">
                                    <div class="pr-xl-5">
                                        {{\App\CentralLogics\Helpers::format_currency($exp['amount'])}}
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- End Table -->


                @if (count($expense) !== 0)
                    <hr>
                    <div class="page-area">
                        {!! $expense->withQueryString()->links() !!}
                    </div>
                @endif
                @if (count($expense) === 0)
                    <div class="empty--data">
                        <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                        <h5>
                            {{ translate('no_data_found') }}
                        </h5>
                    </div>
                @endif
            </div>
            <!-- End Body -->
        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script')
@endpush

@push('script_2')
    <script src="{{ asset('public/assets/admin') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{ asset('public/assets/admin') }}/vendor/chartjs-chart-matrix/dist/chartjs-chart-matrix.min.js">
    </script>
    <script src="{{ asset('public/assets/admin') }}/js/hs.chartjs-matrix.js"></script>

    <script>
        $(document).on('ready', function() {
            $('.js-data-example-ajax').select2({
                ajax: {
                    url: '{{ url('/') }}/admin/restaurant/get-restaurants',
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            // all:true,
                            @if (isset($zone))
                                zone_ids: [{{ $zone->id }}],
                            @endif

                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    __port: function(params, success, failure) {
                        var $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });

            $('.js-data-example-ajax-2').select2({
                ajax: {
                    url: '{{ url('/') }}/admin/customer/select-list',
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            // all:true,
                            @if (isset($zone))
                                zone_ids: [{{ $zone->id }}],
                            @endif

                            @if (request('restaurant_id'))
                                restaurant_id: {{ request('restaurant_id') }},
                            @endif
                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    __port: function(params, success, failure) {
                        var $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });
        });
    </script>

    <script>
        $('#from_date,#to_date').change(function() {
            let fr = $('#from_date').val();
            let to = $('#to_date').val();
            if (fr != '' && to != '') {
                if (fr > to) {
                    $('#from_date').val('');
                    $('#to_date').val('');
                    toastr.error('Invalid date range!', Error, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            }

        })

    </script>
@endpush

