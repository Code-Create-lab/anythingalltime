<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ImageStoragePicker;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ImageStoragePicker;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function adminHome(Request $request)
    {
        $title = 'Dashboard';
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $currency = DB::table('currency')->first();
        $currency_sign = $currency->currency_sign ?? '';

        $currentDate = Carbon::now();
        $currentDate1 = Carbon::now();
        $agoDate = $currentDate->subDays($currentDate->dayOfWeek)->subWeek();
        $from = date('Y-m-d', strtotime($agoDate));
        $nowDate = $currentDate1->subDays($currentDate1->dayOfWeek);

        $to = date('Y-m-d', strtotime($nowDate));
        $ddate = date('Y-m-d');
        $next_date = date('Y-m-d', strtotime($ddate.' + '.'1'.' days'));

        // Percentage change helper (current vs previous period)
        $pct = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }

            return (($current - $previous) / $previous) * 100;
        };

        $last_week = DB::table('orders')
            ->where('order_status', 'Completed')
            ->whereBetween('delivery_date', [$from, $to])
            ->sum('total_price');

        $this_week = DB::table('orders')
            ->where('order_status', 'Completed')
            ->whereBetween('delivery_date', [$to, $next_date])
            ->sum('total_price');
        $difference = $pct($this_week, $last_week);

        $last_week_ord = DB::table('orders')
            ->where('order_status', 'Completed')
            ->whereBetween('delivery_date', [$from, $to])
            ->count();

        $this_week_ord = DB::table('orders')
            ->where('order_status', '!=', 'Cancelled')
            ->whereBetween('delivery_date', [$to, $next_date])
            ->count();
        $diff_ord = $pct($this_week_ord, $last_week_ord);

        $last_week_can = DB::table('orders')
            ->where('order_status', 'Cancelled')
            ->whereBetween('delivery_date', [$from, $to])
            ->count();

        $this_week_can = DB::table('orders')
            ->where('order_status', 'Cancelled')
            ->whereBetween('delivery_date', [$to, $next_date])
            ->count();
        $diff_can = $pct($this_week_can, $last_week_can);

        $last_week_pen = DB::table('orders')
            ->where('order_status', 'pending')
            ->whereBetween('delivery_date', [$from, $to])
            ->count();

        $this_week_pen = DB::table('orders')
            ->where('order_status', 'pending')
            ->whereBetween('delivery_date', [$to, $next_date])
            ->count();
        $diff_pen = $pct($this_week_pen, $last_week_pen);

        $last_week_usr = DB::table('users')
            ->whereBetween('reg_date', [$from, $to])
            ->count();
        $this_week_usr = DB::table('users')
            ->whereBetween('reg_date', [$to, $next_date])
            ->count();
        $diff_usr = $pct($this_week_usr, $last_week_usr);

        $total_earnings = DB::table('orders')
            ->where('order_status', 'Completed')
            ->sum('total_price');

        $today_earnings = DB::table('orders')
            ->where('order_status', 'Completed')
            ->where('delivery_date', $ddate)
            ->sum('total_price');

        $today_orders = DB::table('orders')
            ->where('order_date', $ddate)
            ->count();

        $store_earning = DB::table('store')
            ->join('orders', 'store.id', '=', 'orders.store_id')
            ->select(DB::raw('SUM(orders.price_without_delivery)-SUM(orders.price_without_delivery)*(store.admin_share)/100 as sumprice'))
            ->groupBy('orders.order_status', 'store.admin_share')
            ->where('orders.order_status', 'Completed')
            ->where('orders.payment_method', '!=', null)
            ->first();
        if ($store_earning) {
            if ($store_earning->sumprice != null) {
                $store_earnings = $store_earning->sumprice;
            } else {
                $store_earnings = 0;
            }
        } else {
            $store_earnings = 0;
        }

        $admin_earnings = $total_earnings - $store_earnings;

        // ---- Platform totals -------------------------------------------------
        $total_orders = DB::table('orders')->count();
        $completed_orders = DB::table('orders')->where('order_status', 'Completed')->count();
        $cancelled_orders = DB::table('orders')->where('order_status', 'Cancelled')->count();
        $pending_orders = DB::table('orders')->where('order_status', 'Pending')->count();
        $ofd_orders = DB::table('orders')->where('order_status', 'Out_For_Delivery')->count();
        $avg_order_value = $completed_orders > 0 ? $total_earnings / $completed_orders : 0;

        $total_users = DB::table('users')->count();
        $total_stores = DB::table('store')->count();
        $active_stores = DB::table('store')->where('store_status', 1)->where('admin_approval', 1)->count();
        $pending_stores = DB::table('store')->where('admin_approval', '!=', 1)->count();
        $total_drivers = DB::table('delivery_boy')->count();
        $total_products = DB::table('product')->count();
        $total_categories = DB::table('categories')->count();

        // ---- 30-day trend series (revenue, orders, new users) ---------------
        $trend_start = Carbon::today()->subDays(29);
        $trend_from = $trend_start->format('Y-m-d');

        $revenue_rows = DB::table('orders')
            ->select('delivery_date as d', DB::raw('SUM(total_price) as total'))
            ->where('order_status', 'Completed')
            ->where('delivery_date', '>=', $trend_from)
            ->groupBy('delivery_date')
            ->pluck('total', 'd');

        $order_rows = DB::table('orders')
            ->select('order_date as d', DB::raw('COUNT(*) as total'))
            ->where('order_date', '>=', $trend_from)
            ->groupBy('order_date')
            ->pluck('total', 'd');

        $user_rows = DB::table('users')
            ->select('reg_date as d', DB::raw('COUNT(*) as total'))
            ->where('reg_date', '>=', $trend_from)
            ->groupBy('reg_date')
            ->pluck('total', 'd');

        $trend_labels = [];
        $revenue_series = [];
        $orders_series = [];
        $users_series = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $trend_start->copy()->addDays($i)->format('Y-m-d');
            $trend_labels[] = $trend_start->copy()->addDays($i)->format('d M');
            $revenue_series[] = round((float) ($revenue_rows[$day] ?? 0), 2);
            $orders_series[] = (int) ($order_rows[$day] ?? 0);
            $users_series[] = (int) ($user_rows[$day] ?? 0);
        }

        // ---- Order status distribution (last 30 days) ------------------------
        $status_rows = DB::table('orders')
            ->select('order_status', DB::raw('COUNT(*) as total'))
            ->where('order_date', '>=', $trend_from)
            ->whereNotNull('order_status')
            ->groupBy('order_status')
            ->orderBy('total', 'desc')
            ->get();
        $status_labels = [];
        $status_series = [];
        foreach ($status_rows as $row) {
            $status_labels[] = str_replace('_', ' ', $row->order_status);
            $status_series[] = (int) $row->total;
        }

        // ---- Payment method split (completed orders) -------------------------
        $payment_rows = DB::table('orders')
            ->select('payment_method', DB::raw('COUNT(*) as total'))
            ->where('order_status', 'Completed')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->orderBy('total', 'desc')
            ->get();
        $payment_labels = [];
        $payment_series = [];
        foreach ($payment_rows as $row) {
            $payment_labels[] = ucfirst($row->payment_method);
            $payment_series[] = (int) $row->total;
        }

        // ---- Top stores by revenue (last 30 days) -----------------------------
        $top_stores = DB::table('orders')
            ->join('store', 'orders.store_id', '=', 'store.id')
            ->select('store.store_name', DB::raw('SUM(orders.total_price) as revenue'), DB::raw('COUNT(*) as orders_count'))
            ->where('orders.order_status', 'Completed')
            ->where('orders.delivery_date', '>=', $trend_from)
            ->groupBy('store.store_name')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get();
        $top_store_labels = [];
        $top_store_series = [];
        foreach ($top_stores as $row) {
            $top_store_labels[] = $row->store_name;
            $top_store_series[] = round((float) $row->revenue, 2);
        }

        // ---- Bestsellers this week (revenue/qty aggregated in the same query) --
        $topselling = DB::table('store_orders')
            ->join('orders', 'store_orders.order_cart_id', '=', 'orders.cart_id')
            ->select('store_orders.store_id', 'store_orders.product_name', 'store_orders.varient_id', 'store_orders.varient_image', 'store_orders.quantity', 'store_orders.unit', 'store_orders.description', DB::raw('count(store_orders.varient_id) as count'), DB::raw('SUM(store_orders.qty) as totalqty'), DB::raw('SUM(store_orders.price) as revenue'))
            ->groupBy('store_orders.store_id', 'store_orders.product_name', 'store_orders.varient_id', 'store_orders.varient_image', 'store_orders.quantity', 'store_orders.unit', 'store_orders.description')
            ->orderBy('count', 'desc')
            ->where('orders.order_status', 'Completed')
            ->whereBetween('orders.delivery_date', [$to, $next_date])
            ->limit(5)
            ->get();

        $ongoin = DB::table('orders')
            ->join('store', 'orders.store_id', '=', 'store.id')
            ->join('address', 'orders.address_id', '=', 'address.address_id')
            ->leftJoin('delivery_boy', 'orders.dboy_id', '=', 'delivery_boy.dboy_id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select('orders.*', 'users.name', 'users.user_phone', 'store.store_name', 'address.type', 'address.house_no', 'address.society', 'address.landmark', 'address.city', 'address.state', 'address.pincode', 'address.receiver_name', 'address.receiver_phone', 'delivery_boy.boy_name')
            ->where('orders.order_status', '!=', null)
            ->where('orders.payment_method', '!=', null)
            ->orderBy('orders.order_id', 'DESC')
            ->limit(8)
            ->get();

        $url_aws = $this->getImageStorage();

        $details = DB::table('orders')
            ->join('store_orders', 'orders.cart_id', '=', 'store_orders.order_cart_id')
            ->where('store_orders.store_approval', 1)
            ->get();

        return view('admin.home', compact(
            'title', 'admin', 'logo', 'currency_sign',
            'total_earnings', 'store_earnings', 'admin_earnings', 'today_earnings', 'today_orders',
            'last_week', 'difference', 'this_week',
            'diff_ord', 'last_week_ord', 'this_week_ord',
            'last_week_can', 'this_week_can', 'diff_can',
            'diff_pen', 'last_week_pen', 'this_week_pen',
            'diff_usr', 'last_week_usr', 'this_week_usr',
            'total_orders', 'completed_orders', 'cancelled_orders', 'pending_orders', 'ofd_orders', 'avg_order_value',
            'total_users', 'total_stores', 'active_stores', 'pending_stores', 'total_drivers', 'total_products', 'total_categories',
            'trend_labels', 'revenue_series', 'orders_series', 'users_series',
            'status_labels', 'status_series', 'payment_labels', 'payment_series',
            'top_stores', 'top_store_labels', 'top_store_series',
            'topselling', 'ongoin', 'url_aws', 'to', 'next_date', 'details'
        ));
    }
}
