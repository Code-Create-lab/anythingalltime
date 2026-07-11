@extends('admin.layout.app')

@section('preload-section')
  <link rel="stylesheet" href="{{url('assets/theme_assets/plugins/apexcharts/dist/apexcharts.css')}}">
@endsection

@section ('content')

@php
    $statusChip = [
        'Pending' => 'gg-chip-warning',
        'Confirmed' => 'gg-chip-info',
        'Out_For_Delivery' => 'gg-chip-info',
        'Completed' => 'gg-chip-success',
        'Cancelled' => 'gg-chip-danger',
    ];
@endphp

<!-- BEGIN hero + primary KPIs -->
<div class="row">
  <div class="col-xl-4 mb-3 d-flex">
    <div class="card gg-hero w-100">
      <div class="card-body d-flex flex-column p-4">
        <div class="d-flex align-items-start">
          <div class="flex-grow-1">
            <div class="gg-hero-label">{{ __('keywords.This Week Earning')}}</div>
            <div class="gg-hero-value gg-num mt-1">{{$currency_sign}}{{number_format($this_week, 2)}}</div>
          </div>
          @if($difference >= 0)
            <span class="gg-delta gg-delta-up"><i class="fa fa-arrow-up"></i> {{number_format(abs($difference), 1)}}%</span>
          @else
            <span class="gg-delta gg-delta-down"><i class="fa fa-arrow-down"></i> {{number_format(abs($difference), 1)}}%</span>
          @endif
        </div>
        <div class="text-white-50 fs-12px mt-1">{{ __('keywords.vs last week')}} ({{$currency_sign}}{{number_format($last_week, 2)}})</div>

        <div class="gg-hero-divider my-3"></div>

        <div class="row">
          <div class="col-4">
            <div class="gg-hero-label">{{ __('keywords.Store Earnings')}}</div>
            <div class="text-white font-weight-600 gg-num">{{$currency_sign}}{{number_format($store_earnings, 2)}}</div>
          </div>
          <div class="col-4">
            <div class="gg-hero-label">{{ __('keywords.Admin Earnings')}}</div>
            <div class="text-white font-weight-600 gg-num">{{$currency_sign}}{{number_format($admin_earnings, 2)}}</div>
          </div>
          <div class="col-4">
            <div class="gg-hero-label">{{ __('keywords.Today Earnings')}}</div>
            <div class="text-white font-weight-600 gg-num">{{$currency_sign}}{{number_format($today_earnings, 2)}}</div>
          </div>
        </div>

        <div class="mt-auto pt-4">
          <a href="{{route('finance')}}" class="btn btn-yellow btn-sm pl-4 pr-4 pt-2 pb-2 fs-13px"><i class="fa fa-wallet mr-2"></i>{{ __('keywords.Go To Store Earnings')}}</a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-8 mb-3">
    <div class="row h-100">
      <div class="col-sm-6 mb-3">
        <div class="card gg-kpi gg-lift h-100">
          <div class="card-body d-flex align-items-start">
            <div class="flex-grow-1">
              <div class="gg-kpi-label">{{ __('keywords.Total Revenue')}}</div>
              <div class="gg-kpi-value gg-num">{{$currency_sign}}{{number_format($total_earnings, 2)}}</div>
              <div class="gg-kpi-sub">{{ __('keywords.All time')}} &middot; {{ __('keywords.Completed orders only')}}</div>
            </div>
            <div class="gg-kpi-icon bg-primary-soft"><i class="fa fa-coins"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 mb-3">
        <div class="card gg-kpi gg-lift h-100">
          <div class="card-body d-flex align-items-start">
            <div class="flex-grow-1">
              <div class="gg-kpi-label">{{ __('keywords.Total Orders')}}</div>
              <div class="gg-kpi-value gg-num">{{number_format($total_orders)}}</div>
              <div class="gg-kpi-sub">
                <span class="gg-chip gg-chip-success">{{number_format($completed_orders)}} {{ __('keywords.Completed')}}</span>
                <span class="gg-chip gg-chip-danger ml-1">{{number_format($cancelled_orders)}} {{ __('keywords.Cancelled')}}</span>
              </div>
            </div>
            <div class="gg-kpi-icon bg-accent-soft"><i class="fa fa-shopping-basket"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 mb-3 mb-xl-0">
        <div class="card gg-kpi gg-lift h-100">
          <div class="card-body d-flex align-items-start">
            <div class="flex-grow-1">
              <div class="gg-kpi-label">{{ __('keywords.Customers')}}</div>
              <div class="gg-kpi-value gg-num">{{number_format($total_users)}}</div>
              <div class="gg-kpi-sub">{{ __('keywords.Registered customers')}}</div>
            </div>
            <div class="gg-kpi-icon bg-success-soft"><i class="fa fa-users"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 mb-0">
        <div class="card gg-kpi gg-lift h-100">
          <div class="card-body d-flex align-items-start">
            <div class="flex-grow-1">
              <div class="gg-kpi-label">{{ __('keywords.Average Order Value')}}</div>
              <div class="gg-kpi-value gg-num">{{$currency_sign}}{{number_format($avg_order_value, 2)}}</div>
              <div class="gg-kpi-sub">{{ __('keywords.Completed orders only')}}</div>
            </div>
            <div class="gg-kpi-icon bg-danger-soft"><i class="fa fa-chart-line"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END hero + primary KPIs -->

<!-- BEGIN weekly pulse -->
<div class="row">
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card gg-kpi h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="gg-kpi-label mb-0">{{ __('keywords.New Orders')}}</div>
          @if($diff_ord >= 0)<span class="gg-delta gg-delta-up"><i class="fa fa-arrow-up"></i> {{number_format(abs($diff_ord),1)}}%</span>
          @else<span class="gg-delta gg-delta-down"><i class="fa fa-arrow-down"></i> {{number_format(abs($diff_ord),1)}}%</span>@endif
        </div>
        <div class="gg-kpi-value gg-num mt-2">{{number_format($this_week_ord)}}</div>
        <div class="gg-kpi-sub">{{ __('keywords.This Week')}} &middot; {{ __('keywords.vs last week')}} {{number_format($last_week_ord)}}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card gg-kpi h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="gg-kpi-label mb-0">{{ __('keywords.Pending Orders')}}</div>
          @if($diff_pen <= 0)<span class="gg-delta gg-delta-up"><i class="fa fa-arrow-down"></i> {{number_format(abs($diff_pen),1)}}%</span>
          @else<span class="gg-delta gg-delta-down"><i class="fa fa-arrow-up"></i> {{number_format(abs($diff_pen),1)}}%</span>@endif
        </div>
        <div class="gg-kpi-value gg-num mt-2">{{number_format($this_week_pen)}}</div>
        <div class="gg-kpi-sub">{{ __('keywords.This Week')}} &middot; {{ __('keywords.vs last week')}} {{number_format($last_week_pen)}}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card gg-kpi h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="gg-kpi-label mb-0">{{ __('keywords.Cancelled Orders')}}</div>
          @if($diff_can <= 0)<span class="gg-delta gg-delta-up"><i class="fa fa-arrow-down"></i> {{number_format(abs($diff_can),1)}}%</span>
          @else<span class="gg-delta gg-delta-down"><i class="fa fa-arrow-up"></i> {{number_format(abs($diff_can),1)}}%</span>@endif
        </div>
        <div class="gg-kpi-value gg-num mt-2">{{number_format($this_week_can)}}</div>
        <div class="gg-kpi-sub">{{ __('keywords.This Week')}} &middot; {{ __('keywords.vs last week')}} {{number_format($last_week_can)}}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card gg-kpi h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="gg-kpi-label mb-0">{{ __('keywords.New Users')}}</div>
          @if($diff_usr >= 0)<span class="gg-delta gg-delta-up"><i class="fa fa-arrow-up"></i> {{number_format(abs($diff_usr),1)}}%</span>
          @else<span class="gg-delta gg-delta-down"><i class="fa fa-arrow-down"></i> {{number_format(abs($diff_usr),1)}}%</span>@endif
        </div>
        <div class="gg-kpi-value gg-num mt-2">{{number_format($this_week_usr)}}</div>
        <div class="gg-kpi-sub">{{ __('keywords.This Week')}} &middot; {{ __('keywords.vs last week')}} {{number_format($last_week_usr)}}</div>
      </div>
    </div>
  </div>
</div>
<!-- END weekly pulse -->

<!-- BEGIN platform snapshot strip -->
<div class="row">
  <div class="col-6 col-md-4 col-xl-2 mb-3">
    <a href="{{route('storeclist')}}" class="gg-mini h-100">
      <div class="gg-kpi-icon bg-primary-soft"><i class="fa fa-store"></i></div>
      <div>
        <div class="gg-mini-value gg-num">{{number_format($active_stores)}}<span class="gg-mini-label">/{{number_format($total_stores)}}</span></div>
        <div class="gg-mini-label">{{ __('keywords.Active Stores')}}</div>
      </div>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2 mb-3">
    <a href="{{route('storeapprove')}}" class="gg-mini h-100">
      <div class="gg-kpi-icon bg-accent-soft"><i class="fa fa-user-clock"></i></div>
      <div>
        <div class="gg-mini-value gg-num">{{number_format($pending_stores)}}</div>
        <div class="gg-mini-label">{{ __('keywords.Pending Approvals')}}</div>
      </div>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2 mb-3">
    <a href="{{route('d_boylist')}}" class="gg-mini h-100">
      <div class="gg-kpi-icon bg-success-soft"><i class="fa fa-motorcycle"></i></div>
      <div>
        <div class="gg-mini-value gg-num">{{number_format($total_drivers)}}</div>
        <div class="gg-mini-label">{{ __('keywords.Delivery Boy')}}</div>
      </div>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2 mb-3">
    <a href="{{route('productlist')}}" class="gg-mini h-100">
      <div class="gg-kpi-icon bg-primary-soft"><i class="fa fa-boxes"></i></div>
      <div>
        <div class="gg-mini-value gg-num">{{number_format($total_products)}}</div>
        <div class="gg-mini-label">{{ __('keywords.Products')}}</div>
      </div>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2 mb-3">
    <a href="{{route('catlist')}}" class="gg-mini h-100">
      <div class="gg-kpi-icon bg-accent-soft"><i class="fa fa-layer-group"></i></div>
      <div>
        <div class="gg-mini-value gg-num">{{number_format($total_categories)}}</div>
        <div class="gg-mini-label">{{ __('keywords.Categories')}}</div>
      </div>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2 mb-3">
    <a href="{{route('sales_today')}}" class="gg-mini h-100">
      <div class="gg-kpi-icon bg-success-soft"><i class="fa fa-calendar-day"></i></div>
      <div>
        <div class="gg-mini-value gg-num">{{number_format($today_orders)}}</div>
        <div class="gg-mini-label">{{ __('keywords.Today Orders')}}</div>
      </div>
    </a>
  </div>
</div>
<!-- END platform snapshot strip -->

<!-- BEGIN trend + status charts -->
<div class="row">
  <div class="col-xl-8 mb-3 d-flex">
    <div class="card w-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="flex-grow-1">
            <div class="gg-card-title">{{ __('keywords.Revenue & Orders Trend')}}</div>
            <div class="gg-card-sub">{{ __('keywords.Last 30 Days')}}</div>
          </div>
          <a href="{{route('admin_all_orders')}}" class="btn btn-outline-primary btn-sm">{{ __('keywords.View all')}}</a>
        </div>
        <div id="gg-chart-trend" style="min-height: 320px;"></div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 mb-3 d-flex">
    <div class="card w-100">
      <div class="card-body">
        <div class="gg-card-title">{{ __('keywords.Order Status Split')}}</div>
        <div class="gg-card-sub mb-2">{{ __('keywords.Last 30 Days')}}</div>
        <div id="gg-chart-status" style="min-height: 300px;"></div>
      </div>
    </div>
  </div>
</div>
<!-- END trend + status charts -->

<!-- BEGIN secondary charts -->
<div class="row">
  <div class="col-xl-4 mb-3 d-flex">
    <div class="card w-100">
      <div class="card-body">
        <div class="gg-card-title">{{ __('keywords.Payment Methods')}}</div>
        <div class="gg-card-sub mb-2">{{ __('keywords.Completed orders only')}} &middot; {{ __('keywords.All time')}}</div>
        <div id="gg-chart-payment" style="min-height: 280px;"></div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 mb-3 d-flex">
    <div class="card w-100">
      <div class="card-body">
        <div class="gg-card-title">{{ __('keywords.Top Stores')}}</div>
        <div class="gg-card-sub mb-2">{{ __('keywords.Total Revenue')}} &middot; {{ __('keywords.Last 30 Days')}}</div>
        <div id="gg-chart-stores" style="min-height: 280px;"></div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 mb-3 d-flex">
    <div class="card w-100">
      <div class="card-body">
        <div class="gg-card-title">{{ __('keywords.New Users')}}</div>
        <div class="gg-card-sub mb-2">{{ __('keywords.Last 30 Days')}}</div>
        <div id="gg-chart-users" style="min-height: 280px;"></div>
      </div>
    </div>
  </div>
</div>
<!-- END secondary charts -->

<!-- BEGIN bestseller + latest orders -->
<div class="row">
  <div class="col-xl-5 mb-3 d-flex">
    <div class="card w-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="flex-grow-1">
            <div class="gg-card-title">{{ __('keywords.Bestseller')}}</div>
            <div class="gg-card-sub">{{ __('keywords.Top product sales this week')}}</div>
          </div>
        </div>
        @if(count($topselling) > 0)
          @foreach($topselling as $topsellings)
          <div class="gg-list-row">
            <div class="gg-list-thumb">
              <img src="{{$url_aws.$topsellings->varient_image}}" alt="{{$topsellings->product_name}}" loading="lazy" />
            </div>
            <div class="flex-grow-1">
              <div class="text-dark font-weight-600">{{$topsellings->product_name}}</div>
              <div class="fs-12px text-muted">{{$topsellings->quantity}}{{$topsellings->unit}} &middot; {{$currency_sign}}{{number_format((float) $topsellings->revenue, 2)}}</div>
            </div>
            <div class="text-right">
              <div class="text-dark font-weight-700 gg-num">{{$topsellings->count}}</div>
              <div class="fs-12px text-muted">{{ __('keywords.sales')}}</div>
            </div>
          </div>
          @endforeach
        @else
          <div class="text-center text-muted py-5">
            <i class="fa fa-box-open fa-2x mb-2 d-block"></i>
            {{ __('keywords.No data found')}}
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-xl-7 mb-3 d-flex">
    <div class="card w-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="flex-grow-1">
            <div class="gg-card-title">{{ __('keywords.Orders')}}</div>
            <div class="gg-card-sub">{{ __('keywords.Latest order history')}}</div>
          </div>
          <a href="{{route('admin_all_orders')}}" class="btn btn-outline-primary btn-sm">{{ __('keywords.View all')}}</a>
        </div>

        <div class="table-responsive mb-n2">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th class="pl-0">#</th>
                <th>{{ __('keywords.Cart_id')}}</th>
                <th>{{ __('keywords.Customers')}}</th>
                <th class="text-center">{{ __('keywords.Status')}}</th>
                <th class="text-right">{{ __('keywords.Price')}}</th>
                <th class="text-right pr-0">{{ __('keywords.Details')}}</th>
              </tr>
            </thead>
            <tbody>
              @if(count($ongoin) > 0)
                @php $i = 1; @endphp
                @foreach($ongoin as $ongoing)
                <tr>
                  <td class="pl-0 text-muted">{{$i}}</td>
                  <td>
                    <div class="font-weight-600 text-dark">{{$ongoing->cart_id}}</div>
                    <div class="fs-12px text-muted">{{$ongoing->delivery_date}}</div>
                  </td>
                  <td>
                    <div class="font-weight-600 text-dark">{{$ongoing->name}}</div>
                    <div class="fs-12px text-muted">{{$ongoing->user_phone}}</div>
                  </td>
                  <td class="text-center">
                    <span class="gg-chip {{ $statusChip[$ongoing->order_status] ?? 'gg-chip-muted' }}">{{ str_replace('_', ' ', $ongoing->order_status) }}</span>
                  </td>
                  <td class="text-right gg-num font-weight-600">{{$currency_sign}}{{number_format((float) $ongoing->total_price, 2)}}</td>
                  <td class="text-right pr-0">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal1{{$ongoing->cart_id}}">{{ __('keywords.Details')}}</button>
                  </td>
                </tr>
                @php $i++; @endphp
                @endforeach
              @else
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">{{ __('keywords.No data found')}}</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END bestseller + latest orders -->

<!--/////////Order details modal//////////-->
@foreach($ongoin as $ords)
<div class="modal fade" id="exampleModal1{{$ords->cart_id}}" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel{{$ords->cart_id}}" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderModalLabel{{$ords->cart_id}}">{{ __('keywords.Order Details')}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>{{ __('keywords.Order_Id')}} : </strong>{{$ords->cart_id}}<br />
            <strong>{{ __('keywords.Customer_name')}} : </strong>{{$ords->receiver_name}}<br />
            <strong>{{ __('keywords.Contact')}} : </strong>{{$ords->receiver_phone}}@if($ords->user_phone != $ords->receiver_phone), {{$ords->user_phone}}@endif<br />
            <strong>{{ __('keywords.Delivery_Date')}} : </strong>{{$ords->delivery_date}}<br />
            <strong>{{ __('keywords.Time_Slot')}} : </strong>{{$ords->time_slot}}
          </div>
          <div class="col-md-6 text-md-right">
            <strong>{{ __('keywords.Delivery Address')}}</strong><br />
            <b>{{$ords->type}} :</b> {{$ords->house_no}}, {{$ords->society}},<br>@if($ords->landmark != NULL){{$ords->landmark}},<br>@endif{{$ords->city}}, {{$ords->state}},<br>
            {{$ords->pincode}}
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>{{ __('keywords.Product_Name')}}</th>
                <th>{{ __('keywords.Qty')}}</th>
                <th>{{ __('keywords.Tax')}}</th>
                <th class="text-right">{{ __('keywords.Price')}}</th>
                <th class="text-right">{{ __('keywords.Total_Price')}}</th>
              </tr>
            </thead>
            <tbody>
              @if(count($details) > 0)
                @foreach($details as $detailss)
                  @if($detailss->cart_id == $ords->cart_id)
                  <tr>
                    <td>
                      <img style="width:25px;height:25px;border-radius:50%" src="{{url($detailss->varient_image)}}" alt="">
                      {{$detailss->product_name}} ({{$detailss->quantity}}{{$detailss->unit}})
                    </td>
                    <td class="gg-num">{{$detailss->qty}}</td>
                    <td>@if($detailss->tx_per == 0 || $detailss->tx_per == NULL)0 @else {{$detailss->tx_per}}@endif % @if($detailss->tx_per != 0 && $detailss->tx_name != NULL)({{$detailss->tx_name}})@endif</td>
                    <td class="text-right gg-num">@if($detailss->price_without_tax != NULL){{$detailss->price_without_tax}} @else {{$detailss->price}} @endif</td>
                    <td class="text-right gg-num">{{$detailss->price}}</td>
                  </tr>
                  @endif
                @endforeach
              @else
                <tr>
                  <td colspan="5">{{ __('keywords.No data found')}}</td>
                </tr>
              @endif
              <tr>
                <td colspan="4" class="text-right"><strong>{{ __('keywords.Products_Price')}} :</strong></td>
                <td class="text-right gg-num"><strong>{{$ords->price_without_delivery}}</strong></td>
              </tr>
              <tr>
                <td colspan="4" class="text-right"><strong>{{ __('keywords.Delivery_Charge')}} :</strong></td>
                <td class="text-right gg-num"><strong>+{{$ords->delivery_charge}}</strong></td>
              </tr>
              @if($ords->paid_by_wallet > 0)
              <tr>
                <td colspan="4" class="text-right"><strong>{{ __('keywords.Paid By Wallet')}} :</strong></td>
                <td class="text-right gg-num"><strong>-{{$ords->paid_by_wallet}}</strong></td>
              </tr>
              @endif
              @if($ords->coupon_discount > 0)
              <tr>
                <td colspan="4" class="text-right"><strong>{{ __('keywords.Coupon Discount')}} :</strong></td>
                <td class="text-right gg-num"><strong>-{{$ords->coupon_discount}}</strong></td>
              </tr>
              @endif
              <tr>
                <td colspan="4" class="text-right"><strong>{{ __('keywords.Net_Total(Payable)')}} :</strong></td>
                <td class="text-right gg-num"><strong>{{$ords->rem_price}}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" data-dismiss="modal" aria-hidden="true">{{ __('keywords.Close')}}</button>
      </div>
    </div>
  </div>
</div>
@endforeach

@endsection

@section('postload-section')
<script src="{{url('assets/theme_assets/plugins/apexcharts/dist/apexcharts.min.js')}}"></script>
<script>
(function () {
    var currency = @json($currency_sign);
    var trendLabels = @json($trend_labels);
    var revenueSeries = @json($revenue_series);
    var ordersSeries = @json($orders_series);
    var usersSeries = @json($users_series);
    var statusLabels = @json($status_labels);
    var statusSeries = @json($status_series);
    var paymentLabels = @json($payment_labels);
    var paymentSeries = @json($payment_series);
    var storeLabels = @json($top_store_labels);
    var storeSeries = @json($top_store_series);

    var money = function (val) {
        if (val === null || typeof val === 'undefined') return '';
        return currency + Number(val).toLocaleString(undefined, { maximumFractionDigits: 2 });
    };
    var count = function (val) {
        if (val === null || typeof val === 'undefined') return '';
        return Number(val).toLocaleString();
    };

    var baseChart = {
        fontFamily: "'Fira Sans', sans-serif",
        foreColor: '#64748B',
        toolbar: { show: false },
        animations: { enabled: !window.matchMedia('(prefers-reduced-motion: reduce)').matches }
    };
    var baseGrid = { borderColor: '#EEF2F7', strokeDashArray: 4 };
    var noData = { text: @json(__('keywords.No data found')), style: { color: '#94A3B8', fontSize: '13px' } };

    // Revenue (area) + Orders (column), dual y-axis
    new ApexCharts(document.querySelector('#gg-chart-trend'), {
        chart: Object.assign({ type: 'line', height: 330, stacked: false }, baseChart),
        series: [
            { name: @json(__('keywords.Total Revenue')), type: 'area', data: revenueSeries },
            { name: @json(__('keywords.Orders')), type: 'column', data: ordersSeries }
        ],
        colors: ['#1E40AF', '#D97706'],
        stroke: { width: [3, 0], curve: 'smooth' },
        fill: {
            type: ['gradient', 'solid'],
            gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 95] }
        },
        plotOptions: { bar: { columnWidth: '45%', borderRadius: 3 } },
        labels: trendLabels,
        xaxis: { tickAmount: 10, labels: { rotate: 0, hideOverlappingLabels: true } },
        yaxis: [
            { title: { text: @json(__('keywords.Total Revenue')) }, labels: { formatter: money } },
            { opposite: true, title: { text: @json(__('keywords.Orders')) }, labels: { formatter: count } }
        ],
        tooltip: {
            shared: true,
            intersect: false,
            y: [{ formatter: money }, { formatter: count }]
        },
        legend: { position: 'top', horizontalAlign: 'right' },
        grid: baseGrid,
        dataLabels: { enabled: false },
        noData: noData
    }).render();

    // Order status donut, semantic colors per status
    var statusColorMap = {
        'completed': '#059669',
        'pending': '#D97706',
        'cancelled': '#DC2626',
        'confirmed': '#3B82F6',
        'out for delivery': '#7C3AED'
    };
    var statusColors = statusLabels.map(function (label, idx) {
        var fallback = ['#1E40AF', '#0D9488', '#D97706', '#7C3AED', '#DC2626', '#64748B'];
        return statusColorMap[String(label).toLowerCase()] || fallback[idx % fallback.length];
    });
    new ApexCharts(document.querySelector('#gg-chart-status'), {
        chart: Object.assign({ type: 'donut', height: 300 }, baseChart),
        series: statusSeries,
        labels: statusLabels,
        colors: statusColors,
        legend: { position: 'bottom' },
        stroke: { width: 2, colors: ['#FFFFFF'] },
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: @json(__('keywords.Orders')), formatter: function (w) { return count(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)); } } } } } },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: count } },
        noData: noData
    }).render();

    // Payment methods donut
    new ApexCharts(document.querySelector('#gg-chart-payment'), {
        chart: Object.assign({ type: 'donut', height: 280 }, baseChart),
        series: paymentSeries,
        labels: paymentLabels,
        colors: ['#1E40AF', '#0D9488', '#D97706', '#7C3AED', '#DC2626', '#64748B'],
        legend: { position: 'bottom' },
        stroke: { width: 2, colors: ['#FFFFFF'] },
        plotOptions: { pie: { donut: { size: '68%' } } },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: count } },
        noData: noData
    }).render();

    // Top stores horizontal bar
    new ApexCharts(document.querySelector('#gg-chart-stores'), {
        chart: Object.assign({ type: 'bar', height: 280 }, baseChart),
        series: [{ name: @json(__('keywords.Total Revenue')), data: storeSeries }],
        colors: ['#3B82F6'],
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
        xaxis: { categories: storeLabels, labels: { formatter: money } },
        tooltip: { y: { formatter: money } },
        grid: baseGrid,
        dataLabels: { enabled: false },
        noData: noData
    }).render();

    // New users area
    new ApexCharts(document.querySelector('#gg-chart-users'), {
        chart: Object.assign({ type: 'area', height: 280, sparkline: { enabled: false } }, baseChart),
        series: [{ name: @json(__('keywords.New Users')), data: usersSeries }],
        colors: ['#0D9488'],
        stroke: { width: 3, curve: 'smooth' },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 95] } },
        labels: trendLabels,
        xaxis: { tickAmount: 6, labels: { rotate: 0, hideOverlappingLabels: true } },
        yaxis: { labels: { formatter: count } },
        tooltip: { y: { formatter: count } },
        grid: baseGrid,
        dataLabels: { enabled: false },
        noData: noData
    }).render();
})();
</script>
@endsection
