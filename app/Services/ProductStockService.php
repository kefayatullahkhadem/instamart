<?php

namespace App\Services;

use AizPackages\CombinationGenerate\Services\CombinationService;
use App\Models\ProductStock;
use App\Utility\ProductUtility;
use Auth;

class ProductStockService
{
    public function store(array $data, $product)
    {
        $collection = collect($data);

        $options = ProductUtility::get_attribute_options($collection);
        
        //Generates the combinations of customer choice options
        $combinations = (new CombinationService())->generate_combination($options);
        
        $variant = '';
        if (count($combinations) > 0) {
            $product->variant_product = 1;
            $product->save();
            foreach ($combinations as $key => $combination) {

                $sku = $this->generateUniqueSKU();
                $str = ProductUtility::get_combination_string($combination, $collection);
                $product_stock = new ProductStock();
                $product_stock->product_id = $product->id;
                $product_stock->variant = $str;
                $product_stock->price = request()['price_' . str_replace('.', '_', $str)];
                if(request()['sku_' . $str] == null){
                    $product_stock->sku = $sku;

                }else{
                    $product_stock->sku = request()['sku_' . str_replace('.', '_', $str)];

                }
                $product_stock->qty = request()['qty_' . str_replace('.', '_', $str)];
                $product_stock->image = request()['img_' . str_replace('.', '_', $str)];
                $product_stock->save();
            }
        } else {
            unset($collection['colors_active'], $collection['colors'], $collection['choice_no']);
            $qty = $collection['current_stock'];
            $price = $collection['unit_price'];
            unset($collection['current_stock']);

            $data = $collection->merge(compact('variant', 'qty', 'price'))->toArray();
            if($data['sku'] == null){
                $data['sku'] = $this->generateUniqueSKU();

            }

            ProductStock::create($data);
        }
    }

    public function product_duplicate_store($product_stocks , $product_new)
    {
        foreach ($product_stocks as $key => $stock) {
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product_new->id;
            $product_stock->variant     = $stock->variant;
            $product_stock->price       = $stock->price;
            $product_stock->sku         = $stock->sku;
            $product_stock->qty         = $stock->qty;
            $product_stock->save();
        }
    }

    private function generateUniqueSKU()
    {
        // Check if the user is authenticated and their role
        $user = Auth::user();
    
        if ($user) {
            if ($user->user_type == 'seller') {
                // If the user is a seller, use "Seller" + user ID as part of SKU
                $prefix = env('SELLER_PREFIX') . $user->id . "-";
            } elseif ($user->user_type == 'admin') {
                // If the user is an admin, use "Insta-" as the prefix
                $prefix = env('ADMIN_PREFIX');
            } else {
                // Handle other user roles as needed
                $prefix = "OtherRole-";
            }
        } else {
            // Handle cases where the user is not authenticated
            $prefix = "Guest-";
        }
    
        // Generate a random 6-digit number
        $randomNumber = mt_rand(100000, 999999);
    
        // Create the SKU by concatenating the prefix with the random number
        $sku = $prefix . $randomNumber;
    
        // Check if the SKU exists in the products table
        // You would need to replace 'ProductStock' with the actual model name for your products
        while (ProductStock::where('sku', $sku)->exists()) {
            // If the SKU already exists, generate a new random number and SKU
            $randomNumber = mt_rand(100000, 999999);
            $sku = $prefix . $randomNumber;
        }
    
        return $sku;
    }
    
}
