<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(){

        if (!Cache::has('products')) {
            $query = DB::table('products')
                ->join('collections','products.collection_id', '=', 'collections.id')
                ->join('collection_lines','collections.collection_line_id', '=', 'collection_lines.id')
                ->join('order_products', 'order_products.product_id', '=', 'products.id')
                ->select('products.name as product',
                    'collections.name as collection',
                    'collection_lines.name as collection_line',
                    DB::raw('count(order_products.product_id) as count'),
                    DB::raw('- (sum(order_products.price) - count(order_products.product_id) * products.cost_of_sale * 100) / sum(order_products.price)  as margin'),
                    DB::raw('sum(order_products.price) as total'))
                ->groupBy('products.name')
                ->get();

            Cache::put('products', $query, 3600);
            return $query;
        }
        else return Cache::get('products');

    }
}
