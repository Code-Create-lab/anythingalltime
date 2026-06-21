<?php

namespace App\Http\Controllers\Storeapi;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class StvarientController extends Controller
{
    public function varient_list(Request $request)
    {
        $id = $request->product_id ?? $request->p_id;
        $p = DB::table('product')
            ->where('product_id', $id)
            ->first();

        $store_id = $request->store_id;

        $product = DB::table('product_varient')
            ->select('*', 'product_id as p_id')
            ->where('added_by', $store_id)
            ->where('product_id', $id)
            ->get();
        $currency = DB::table('currency')
            ->select('currency_sign')
            ->get();

        if (count($product) > 0) {
            $message = ['status' => '1', 'message' => 'Varients', 'data' => $product];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'No Varients', 'data' => []];

            return $message;
        }
    }

    public function AddNewproduct(Request $request)
    {
        $store_id = $request->store_id;
        $id = $request->product_id;
        $mrp = $request->mrp ?? $request->strick_mrp;
        $price = $request->price ?? $request->strick_price;
        $unit = $request->unit;
        $quantity = $request->quantity;
        $description = $request->description;
        $date = date('d-m-Y');
        $created_at = date('d-m-Y h:i a');

        $ean = $request->ean;
        $image = 'N/A';

        // Handle multiple variants if arrays are passed
        if (is_array($unit) && is_array($quantity)) {
            $insertedCount = 0;
            $totalVariants = count($unit);

            for ($i = 0; $i < $totalVariants; $i++) {
                $insert = DB::table('product_varient')
                    ->insertGetId([
                        'product_id' => $id,
                        'base_mrp' => $mrp,
                        'base_price' => $price,
                        'varient_image' => $image,
                        'unit' => $unit[$i] ?? '',
                        'quantity' => $quantity[$i] ?? 0,
                        'description' => $description,
                        'ean' => $ean,
                        'approved' => 0,
                        'added_by' => $store_id,
                    ]);

                if ($insert) {
                    // Also add to store_products for inventory tracking
                    DB::table('store_products')->insert([
                        'store_id' => $store_id,
                        'p_id' => $id,
                        'varient_id' => $insert,
                        'stock' => 1,
                        'mrp' => $mrp,
                        'price' => $price,
                        'min_ord_qty' => 1,
                        'max_ord_qty' => 100,
                    ]);
                    $insertedCount++;
                }
            }

            if ($insertedCount > 0) {
                $message = ['status' => '1', 'message' => 'Variant Created Successfully'];

                return $message;
            } else {
                $message = ['status' => '0', 'message' => 'something went wrong'];

                return $message;
            }
        } else {
            // Handle single variant
            $insert = DB::table('product_varient')
                ->insertGetId(['product_id' => $id, 'base_mrp' => $mrp, 'base_price' => $price, 'varient_image' => $image, 'unit' => $unit, 'quantity' => $quantity, 'description' => $description, 'ean' => $ean, 'approved' => 0, 'added_by' => $store_id]);

            if ($insert) {
                // Also add to store_products for inventory tracking
                DB::table('store_products')->insert([
                    'store_id' => $store_id,
                    'p_id' => $id,
                    'varient_id' => $insert,
                    'stock' => 1,
                    'mrp' => $mrp,
                    'price' => $price,
                    'min_ord_qty' => 1,
                    'max_ord_qty' => 100,
                ]);
                $message = ['status' => '1', 'message' => 'Product Varient Added'];

                return $message;
            } else {
                $message = ['status' => '0', 'message' => 'something went wrong'];

                return $message;
            }
        }
    }

    public function Updateproduct(Request $request)
    {
        $store_id = $request->store_id;
        $product_id = $request->varient_id;
        $id = $request->product_id ?? $request->p_id;
        $mrp = $request->mrp ?? $request->strick_mrp;
        $price = $request->price ?? $request->strick_price;
        $unit = $request->unit;
        $quantity = $request->quantity;
        $description = $request->description;
        $date = date('d-m-Y');
        $created_at = date('d-m-Y h:i a');
        $ean = $request->ean;
        $varient_image = 'N/A';

        // First check if the variant exists
        $existing_variant = DB::table('product_varient')
            ->where('varient_id', $product_id)
            ->first();

        if (! $existing_variant) {
            $message = ['status' => '0', 'message' => 'something went wrong'];

            return $message;
        }

        $varient_update = DB::table('product_varient')
            ->where('varient_id', $product_id)
            ->update(['varient_image' => $varient_image, 'unit' => $unit, 'quantity' => $quantity, 'description' => $description, 'ean' => $ean, 'base_mrp' => $mrp, 'base_price' => $price, 'approved' => 0]);
        $st_varient_upd = DB::table('store_products')
            ->where('varient_id', $product_id)
            ->where('store_id', $store_id)
            ->first();
        if ($st_varient_upd) {
            $st_varient_update = DB::table('store_products')
                ->where('varient_id', $product_id)
                ->where('store_id', $store_id)
                ->update(['price' => $price, 'mrp' => $mrp]);
        } else {
            // Create new store_products entry if it doesn't exist
            $st_varient_update = DB::table('store_products')
                ->insert([
                    'store_id' => $store_id,
                    'p_id' => $id,
                    'varient_id' => $product_id,
                    'stock' => 1,
                    'mrp' => $mrp,
                    'price' => $price,
                    'min_ord_qty' => 1,
                    'max_ord_qty' => 100,
                ]);
        }
        if ($varient_update || $st_varient_update) {

            $message = ['status' => '1', 'message' => 'Variant Updated Successfully'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'something went wrong'];

            return $message;
        }
    }

    public function deleteproduct(Request $request)
    {
        $varient_id = $request->varient_id;
        $store_id = $request->store_id;
        $st_varient_upd = DB::table('store_products')
            ->where('varient_id', $varient_id)
            ->where('store_id', $store_id)
            ->first();
        if ($st_varient_upd) {

            $getmain = DB::table('product_varient')->where('varient_id', $varient_id)->first();
            $getall = DB::table('product_varient')->where('product_id', $getmain->product_id)->get();
            if (count($getall) == 1) {
                $editp = DB::table('product')->where('product_id', $getmain->product_id)->update(['added_by' => 0]);
                $delete = DB::table('store_products')->where('varient_id', $varient_id)
                    ->where('store_id', $store_id)->delete();
            } else {

                $delete = DB::table('store_products')->where('varient_id', $varient_id)
                    ->where('store_id', $store_id)->delete();
            }
        } else {

            $getmain = DB::table('product_varient')->where('varient_id', $varient_id)->first();
            if (! $getmain) {
                $message = ['status' => '0', 'message' => 'Variant not found'];

                return $message;
            }
            $getall = DB::table('product_varient')->where('product_id', $getmain->product_id)->get();
            if (count($getall) == 1) {

                $delete = DB::table('product_varient')->where('varient_id', $varient_id)->delete();
                $deleteprod = DB::table('product')->where('product_id', $getmain->product_id)->delete();
                $deleteold = DB::table('tags')
                    ->where('product_id', $getmain->product_id)
                    ->delete();

                $message = ['status' => '1', 'message' => 'Variant Deleted Successfully'];

                return $message;
            } else {
                $delete = DB::table('product_varient')->where('varient_id', $varient_id)->delete();
            }
        }

        if ($delete) {
            $message = ['status' => '1', 'message' => 'Variant Deleted Successfully'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'something went wrong'];

            return $message;
        }
    }
}
