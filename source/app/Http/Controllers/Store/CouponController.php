<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CouponController extends Controller
{
    public function __construct()
    {
        $storage = DB::table('image_space')
            ->first();

        if ($storage->aws == 1) {
            $this->storage_space = 's3.aws';
        } elseif ($storage->digital_ocean == 1) {
            $this->storage_space = 's3.digitalocean';
        } else {
            $this->storage_space = 'same_server';
        }

    }

    public function couponlist(Request $request)
    {
        $title = trans('keywords.Coupon List');
        $store_email = Auth::guard('store')->user()->email;
        $store = DB::table('store')
            ->where('email', $store_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $coupon = DB::table('coupon')
            ->where('store_id', $store->id)
            ->paginate(10);

        return view('store.coupon.couponlist', compact('title', 'coupon', 'store', 'store_email', 'logo'));
    }

    public function coupon(Request $request)
    {
        $title = 'Add Coupon';
        $store_email = Auth::guard('store')->user()->email;
        $store = DB::table('store')
            ->where('email', $store_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $coupon = DB::table('coupon')
            ->where('store_id', $store->id)
            ->get();

        return view('store.coupon.couponadd', compact('title', 'coupon', 'store', 'store_email', 'logo'));
    }

    public function addcoupon(Request $request)
    {
        if ($request->discount_type == 'percent' && $request->amount > 99) {
            return redirect()->back()->withErrors('Check amount introduced and try again');
        }
        $date = date('d-m-Y');
        $coupon_name = $request->coupon_name;
        $coupon_code = $request->coupon_code;
        $coupon_desc = $request->coupon_desc;
        $valid_to = $request->valid_to;
        $valid_from = $request->valid_from;
        $cart_value = $request->cart_value;
        $coupon_type = ($request->coupon_type != 'firstsale') ? 'default' : 'forder';
        $restriction = $request->restriction;
        $discount = floatval($request->coupon_discountxt);
        $store_email = Auth::guard('store')->user()->email;
        $discount_type = $request->discount_type;
        $max_discount = (! isset($request->max_discountxt) || $request->max_discountxt < 0) ? 0 : $request->max_discountxt;
        $store = DB::table('store')
            ->where('email', $store_email)
            ->first();

        $this->validate(
            $request,
            [
                'coupon_name' => 'required',
                'image' => 'required|mimes:jpeg,png,jpg|max:1000',
                'coupon_code' => 'required',
                'coupon_desc' => 'required',
                'valid_to' => 'required',
                'valid_from' => 'required',
                'cart_value' => 'required',
                'restriction' => 'required',
                'discount_type' => 'required',
            ],
            [
                'coupon_name.required' => 'Coupon Name Required',
                'image.required' => 'Select Image',
                'coupon_code.required' => 'Coupon Code Required',
                'coupon_desc.required' => 'Coupon Description Required',
                'valid_to.required' => 'Date Required',
                'valid_from.required' => 'Date Required',
                'cart_value.required' => 'Cart value Required',
                'restriction.required' => 'Enter Uses Restiction limit',
                'discount_type.required' => 'Select a way to apply discount to coupon',
            ]
        );

        if ($request->hasFile('image')) {
            $image = $request->image;
            $fileName = $image->getClientOriginalName();
            $fileName = str_replace(' ', '-', $fileName);

            if ($this->storage_space != 'same_server') {
                $image_name = $image->getClientOriginalName();
                $image = $request->file('image');
                $filePath = '/coupon/'.$image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('image'), 'r+'), 'public');
            } else {
                $image->move('images/coupon/'.$date.'/', $fileName);
                $filePath = '/images/coupon/'.$date.'/'.$fileName;
            }
        } else {
            $filePath = 'N/A';
        }

        $insert = DB::table('coupon')
            ->insert([
                'typecoupon' => $coupon_type,
                'coupon_name' => $coupon_name,
                'coupon_image' => $filePath,
                'coupon_description' => $coupon_desc,
                'coupon_code' => $coupon_code,
                'start_date' => $valid_from,
                'end_date' => $valid_to,
                'cart_value' => $cart_value,
                'max_discount' => $max_discount,
                'amount' => $discount,
                'type' => $discount_type,
                'uses_restriction' => $restriction,
                'store_id' => $store->id]);

        return redirect()->back()->withSuccess(trans('keywords.Added Successfully'));
    }

    public function editcoupon(Request $request)
    {
        $title = 'Edit Coupon';
        $store_email = Auth::guard('store')->user()->email;
        $store = DB::table('store')
            ->where('email', $store_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $coupon_id = $request->coupon_id;
        $coupon = DB::table('coupon')
            ->where('coupon_id', $coupon_id)
            ->first();

        return view('store.coupon.couponedit', compact('coupon', 'coupon_id', 'title', 'store', 'logo'));
    }

    public function updatecoupon(Request $request)
    {
        $date = date('d-m-Y');
        $coupon_id = $request->coupon_id;
        $coupon_name = $request->coupon_name;
        $coupon_code = $request->coupon_code;
        $coupon_type = ($request->coupon_type != 'firstsale') ? 'default' : 'forder';
        $coupon_desc = $request->coupon_desc;
        $valid_to = $request->valid_to;
        $discount = floatval($request->coupon_discountxt);
        $valid_from = $request->valid_from;
        $cart_value = $request->cart_value;
        $restriction = $request->restriction;
        $discount_type = $request->discount_type;
        $max_discount = (! isset($request->max_discountxt) || $request->max_discountxt < 0) ? 0 : $request->max_discountxt;

        $this->validate(
            $request,
            [
                'coupon_name' => 'required',
                'coupon_code' => 'required',
                'coupon_desc' => 'required',
                'valid_to' => 'required',
                'valid_from' => 'required',
                'cart_value' => 'required',
                'restriction' => 'required',
            ],
            [
                'coupon_name.required' => 'Coupon Name Required',
                'coupon_code.required' => 'Coupon Code Required',
                'coupon_desc.required' => 'Coupon Description Required',
                'valid_to.required' => 'Date Required',
                'valid_from.required' => 'Date Required',
                'cart_value.required' => 'Cart value Required',
                'restriction.required' => 'Enter Uses Restiction limit',

            ]
        );

        if ($request->hasFile('image')) {
            $this->validate(
                $request,
                [
                    'image' => 'required|mimes:jpeg,png,jpg|max:1000',
                ],
                [
                    'image.required' => 'Select Image',

                ]
            );
            $image = $request->image;
            $fileName = $image->getClientOriginalName();
            $fileName = str_replace(' ', '-', $fileName);

            if ($this->storage_space != 'same_server') {
                $image_name = $image->getClientOriginalName();
                $image = $request->file('image');
                $filePath = '/coupon/'.$image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('image'), 'r+'), 'public');
            } else {

                $image->move('images/coupon/'.$date.'/', $fileName);
                $filePath = '/images/coupon/'.$date.'/'.$fileName;

            }
        } else {
            $check = DB::table('coupon')
                ->where('coupon_id', $coupon_id)
                ->first();
            $filePath = $check->coupon_image;
        }

        $update = DB::table('coupon')
            ->where('coupon_id', $coupon_id)
            ->update([
                'typecoupon' => $coupon_type,
                'coupon_name' => $coupon_name,
                'coupon_image' => $filePath,
                'coupon_description' => $coupon_desc,
                'coupon_code' => $coupon_code,
                'start_date' => $valid_to,
                'amount' => $discount,
                'type' => $discount_type,
                'end_date' => $valid_from,
                'cart_value' => $cart_value,
                'uses_restriction' => $restriction,
                'max_discount' => $max_discount]);

        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function deletecoupon(Request $request)
    {
        $coupon_id = $request->coupon_id;

        $getfile = DB::table('coupon')
            ->where('coupon_id', $coupon_id)
            ->first();

        $delete = DB::table('coupon')->where('coupon_id', $request->coupon_id)->delete();
        if ($delete) {
            return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }
}
