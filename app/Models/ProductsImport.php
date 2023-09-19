<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Auth;
use Carbon\Carbon;
use DB;
use Storage;

//class ProductsImport implements ToModel, WithHeadingRow, WithValidation
class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, ToModel
{
    private $rows = 0;

    public function collection(Collection $rows)
    {
        $canImport = true;
        $user = Auth::user();
        if ($user->user_type == 'seller' && addon_is_activated('seller_subscription')) {
            if ((count($rows) + $user->products()->count()) > $user->shop->product_upload_limit
                || $user->shop->package_invalid_at == null
                || Carbon::now()->diffInDays(Carbon::parse($user->shop->package_invalid_at), false) < 0
            ) {
                $canImport = false;
                flash(translate('Please upgrade your package.'))->warning();
            }
        }

        if ($canImport) {
            foreach ($rows as $row) {
                $approved = 1;
                if ($user->user_type == 'seller' && get_setting('product_approve_by_admin') == 1) {
                    $approved = 0;
                }

                $productId = Product::create([
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'added_by' => $user->user_type == 'seller' ? 'seller' : 'admin',
                    'user_id' => $user->user_type == 'seller' ? $user->id : User::where('user_type', 'admin')->first()->id,
                    'approved' => $approved,
                    'category_id' => $row['category_id'],
                    'brand_id' => $row['brand_id'],
                    'video_provider' => $row['video_provider'],
                    'video_link' => $row['video_link'],
                    'tags' => $row['tags'],
                    'unit_price' => $row['unit_price'],
                    'unit' => $row['unit'],
                    'meta_title' => $row['meta_title'],
                    'meta_description' => $row['meta_description'],
                
                    'colors' => json_encode(array()),
                    'choice_options' => json_encode(array()),
                    'variations' => json_encode(array()),
                    'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($row['slug']))) . '-' . Str::random(5),
                    'thumbnail_img' => $this->downloadThumbnail($row['thumbnail_img']),
                    'photos' => implode(',', array_filter([
                        $this->downloadGalleryImages($row['photos']),
                        $this->downloadGalleryImages($row['photos1']),
                        $this->downloadGalleryImages($row['photos2']),
                        $this->downloadGalleryImages($row['photos3']),
                        $this->downloadGalleryImages($row['photos4']),
                        $this->downloadGalleryImages($row['photos5']),
                        $this->downloadGalleryImages($row['photos6']),
                        $this->downloadGalleryImages($row['photos7']),
                        $this->downloadGalleryImages($row['photos8']),
                    ], 'is_numeric')),
                    
                ]);
                ProductStock::create([
                    'product_id' => $productId->id,
                    'qty' => $row['current_stock'],
                    'price' => $row['unit_price'],
                    'sku' => $this->generateUniqueSKU(),
                    'variant' => '',
                ]);

                DB::table('product_warrantys')->insert([
                    'product_id' => $productId->id,
                    'warranty_type' => $row['warranty_type'],
                    'warranty_period' => $row['warranty_period'],
                ]);
            }

            flash(translate('Products imported successfully'))->success();
        }
    }

    public function model(array $row)
    {
        ++$this->rows;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function rules(): array
    {
        return [
            // Can also use callback validation rules
            'unit_price' => function ($attribute, $value, $onFailure) {
                if (!is_numeric($value)) {
                    $onFailure('Unit price is not numeric');
                }
            }
        ];
    }

    public function downloadThumbnail($url)
    {
        try {
            $upload = new Upload;
            $upload->external_link = $url;
            $upload->type = 'image';
            $upload->save();

            return $upload->id;
        } catch (\Exception $e) {
        }
        return null;
    }

    public function downloadGalleryImages($urls)
    {
        $data = array();
        foreach (explode(',', str_replace(' ', '', $urls)) as $url) {
            if (!empty($url)) {
                $downloadedValue = $this->downloadThumbnail($url);
                if (!empty($downloadedValue)) {
                    $data[] = $downloadedValue;
                }
            }
        }
        return implode(',', $data);
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
