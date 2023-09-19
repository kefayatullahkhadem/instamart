<?php

namespace App\Http\Controllers\Seller;

use AizPackages\CombinationGenerate\Services\CombinationService;
use App\Http\Requests\ProductRequest;
use App\Mail\EmailManager;
use App\Models\Shop;
use Illuminate\Http\Request;
use App\Models\AttributeValue;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTax;

use App\Models\ProductTranslation;
use Carbon\Carbon;
use Combinations;
use Artisan;
use Auth;
use Illuminate\Support\Facades\DB;
use Str;
use Illuminate\Support\Facades\Mail;

use App\Services\ProductService;
use App\Services\ProductTaxService;
use App\Services\ProductFlashDealService;
use App\Services\ProductStockService;

class ProductController extends Controller
{
    protected $productService;
    protected $productTaxService;
    protected $productFlashDealService;
    protected $productStockService;

    public function __construct(
        ProductService $productService,
        ProductTaxService $productTaxService,
        ProductFlashDealService $productFlashDealService,
        ProductStockService $productStockService
    ) {
        $this->productService = $productService;
        $this->productTaxService = $productTaxService;
        $this->productFlashDealService = $productFlashDealService;
        $this->productStockService = $productStockService;
    }

    public function index(Request $request)
    {
        $search = null;
        $products = Product::where('user_id', Auth::user()->id)->where('digital', 0)->where('auction_product', 0)->where('wholesale_product', 0)->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $search = $request->search;
            $products = $products->where('name', 'like', '%' . $search . '%');
        }
        $products = $products->paginate(10);
        return view('seller.product.products.index', compact('products', 'search'));
    }

    public function create(Request $request)
    {

        if (addon_is_activated('seller_subscription')) {
            if (seller_package_validity_check()) {
                $categories = Category::where('parent_id', 0)
                ->where('digital', 0)
                ->with(['childrenCategories' => function ($query) {
                    $query->where('digital', 0); // Exclude child categories with digital = 1
                }])
                ->get();
                return view('seller.product.products.create', compact('categories'));
            } else {
                flash(translate('Please upgrade your package.'))->warning();
                return back();
            }
        }
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with(['childrenCategories' => function ($query) {
                $query->where('digital', 0); // Exclude child categories with digital = 1
            }])
            ->get();
        return view('seller.product.products.create', compact('categories'));
    }

    /**
     * Send Add new Prodcut Email
     */
    public function sendProductMail($mail,$product_name,$shopname){
        $htmlContent = '
            <html>
                <head>
                    <title>Added New Product</title>
                </head>
                <body>
                    <h1>Added New Productr</h1>
                    <table cellspacing="0" style="border: 2px dashed #FB4314; width: 100%;">
                        <tr>
                            <th>Shop Name :</th><td>'.$shopname.'</td>
                        </tr>
                        <tr>
                            <th>Product Name :</th><td>'.$product_name.'</td>
                        </tr>
                    </table>
                </body>
            </html>';
        $array['view'] = 'emails.newsletter';
        $array['subject'] = "Added New Product!";
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = $htmlContent;
        try {
            Mail::to($mail)->queue(new EmailManager($array));
        } catch (\Exception $e) {
            dd($e);
        }
        return back();
    }

    /**
     * This function use for store Warranty
     */
    public function store_warranty($product_idx, $warranty_type, $warranty_period) {
        DB::table('product_warrantys')->insert([
            'product_id' => $product_idx,
            'warranty_type' => $warranty_type,
            'warranty_period' => $warranty_period
        ]);
    }
    /**
     * This function use for update Warranty
     */
    public function update_warranty($product_idx, $warranty_type, $warranty_period) {
        DB::table('product_warrantys')
            ->where('product_id', $product_idx) // Specify the condition for the update
            ->update([
                'warranty_type' => $warranty_type,
                'warranty_period' => $warranty_period
            ]);
    }
    /**
     * This function use for warrnty Delete
     */
    public function delete_warranty($product_idx) {
        DB::table('product_warrantys')
            ->where('product_id', $product_idx)
            ->delete();
    }
    public function store(ProductRequest $request)
    {
        if (addon_is_activated('seller_subscription')) {
            if (!seller_package_validity_check()) {
                flash(translate('Please upgrade your package.'))->warning();
                return redirect()->route('seller.products');
            }
        }

        $product = $this->productService->store($request->except([
            '_token', 'sku', 'choice', 'tax_id', 'tax', 'tax_type', 'flash_deal_id', 'flash_discount', 'flash_discount_type'
        ]));
        $request->merge(['product_id' => $product->id]);

        $product_idx = $product->id;
        $warranty_type = $request->warranty_type ;
        $warranty_period = $request->warranty_period;
        $this->store_warranty($product_idx,$warranty_type,$warranty_period);
//VAT & Tax
//        if ($request->tax_id) {
//            $this->productTaxService->store($request->only([
//                'tax_id', 'tax', 'tax_type', 'product_id'
//            ]));
//        }

        //Product Stock
        $this->productStockService->store($request->only([
            'colors_active', 'colors', 'choice_no', 'unit_price', 'sku', 'current_stock', 'product_id'
        ]), $product);

        // Product Translations
        $request->merge(['lang' => env('DEFAULT_LANGUAGE')]);
        ProductTranslation::create($request->only([
            'lang', 'name', 'unit', 'description', 'product_id'
        ]));

        flash(translate('Product has been inserted successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        $product_name = $product->name;
        $shopnamex = Auth::user()->id;
        $shopnamedb = Shop::where('user_id', $shopnamex)
            ->first();
        if ($shopnamedb) { // check if the query returned any results
            $shopname = $shopnamedb->name; // access the "name" property of the object
        } else {
            // handle the case where no rows were found
            $shopname = 'Unknown Shop';
        }
        $mail = 'Info@instamart.lk';
//        $mail = 'sankaamazon@gmail.com';
        $this->sendProductMail($mail,$product_name,$shopname);
        return redirect()->route('seller.products');
    }

    public function edit(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if (Auth::user()->id != $product->user_id) {
            flash(translate('This product is not yours.'))->warning();
            return back();
        }

        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        return view('seller.product.products.edit', compact('product', 'categories', 'tags', 'lang'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        //Product
        $product = $this->productService->update($request->except([
            '_token', 'sku', 'choice', 'tax_id', 'tax', 'tax_type', 'flash_deal_id', 'flash_discount', 'flash_discount_type'
        ]), $product);

        //Product Stock
        foreach ($product->stocks as $key => $stock) {
            $stock->delete();
        }
        $request->merge(['product_id' => $product->id]);
        $this->productStockService->store($request->only([
            'colors_active', 'colors', 'choice_no', 'unit_price', 'sku', 'current_stock', 'product_id'
        ]), $product);

        $product_idx = $product->id;
        $warranty_type = $request->warranty_type ;
        $warranty_period = $request->warranty_period;
//        check have data
        $warranty = DB::table('product_warrantys')
            ->where('product_id', $product_idx)
            ->first();
        if($warranty){
            $this->update_warranty($product_idx, $warranty_type, $warranty_period);
        }else{
            $this->store_warranty($product_idx, $warranty_type, $warranty_period);
        }

//        //VAT & Tax
//        if ($request->tax_id) {
//            ProductTax::where('product_id', $product->id)->delete();
//            $request->merge(['product_id' => $product->id]);
//            $this->productTaxService->store($request->only([
//                'tax_id', 'tax', 'tax_type', 'product_id'
//            ]));
//        }

        // Product Translations
        ProductTranslation::updateOrCreate(
            $request->only([
                'lang', 'product_id'
            ]),
            $request->only([
                'name', 'unit', 'description'
            ])
        );


        flash(translate('Product has been updated successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return back();
    }

    public function sku_combination(Request $request)
    {
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                foreach ($request[$name] as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = (new CombinationService())->generate_combination($options);
        return view('backend.product.products.sku_combinations', compact('combinations', 'unit_price', 'colors_active', 'product_name'));
    }

    public function sku_combination_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $product_name = $request->name;
        $unit_price = $request->unit_price;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                foreach ($request[$name] as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = (new CombinationService())->generate_combination($options);
        return view('backend.product.products.sku_combinations_edit', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'product'));
    }

    public function add_more_choice_option(Request $request)
    {
        $all_attribute_values = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->get();

        $html = '';

        foreach ($all_attribute_values as $row) {
            $html .= '<option value="' . $row->value . '">' . $row->value . '</option>';
        }

        echo json_encode($html);
    }

    public function updatePublished(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->published = $request->status;
        if (addon_is_activated('seller_subscription') && $request->status == 1) {
            $shop = $product->user->shop;
            if (
                $shop->package_invalid_at == null
                || Carbon::now()->diffInDays(Carbon::parse($shop->package_invalid_at), false) < 0
                || $shop->product_upload_limit <= $shop->user->products()->where('published', 1)->count()
            ) {
                return 2;
            }
        }
        $product->save();
        return 1;
    }

    public function updateFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->seller_featured = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }

    public function duplicate($id)
    {
        $product = Product::find($id);
        if (Auth::user()->id != $product->user_id) {
            flash(translate('This product is not yours.'))->warning();
            return back();
        }
        if (addon_is_activated('seller_subscription')) {
            if (!seller_package_validity_check()) {
                flash(translate('Please upgrade your package.'))->warning();
                return back();
            }
        }

        if (Auth::user()->id == $product->user_id) {
            $product_new = $product->replicate();
            $product_new->slug = $product_new->slug . '-' . Str::random(5);
            $product_new->save();

            //Product Stock
            $this->productStockService->product_duplicate_store($product->stocks, $product_new);

            //VAT & Tax
            $this->productTaxService->product_duplicate_store($product->taxes, $product_new);

            flash(translate('Product has been duplicated successfully'))->success();
            return redirect()->route('seller.products');
        } else {
            flash(translate('This product is not yours.'))->warning();
            return back();
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if (Auth::user()->id != $product->user_id) {
            flash(translate('This product is not yours.'))->warning();
            return back();
        }
        $product_idx= $id;
        $this->delete_warranty($product_idx);

        $product->product_translations()->delete();
        $product->stocks()->delete();
        $product->taxes()->delete();


        if (Product::destroy($id)) {
            Cart::where('product_id', $id)->delete();

            flash(translate('Product has been deleted successfully'))->success();

            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return back();
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }



    public function categories_comission(Request $request)
    {
        $search = null;
        $main_category = null;
        
        $categories = Category::latest();
        $main_categories = Category::where('parent_id', '0')->get();
        if ($request->search != '') {
            $search = $request->search;
            $categories = $categories->where('name', 'like', '%' . $search . '%');
        }
        if ($request->main_category != '') {
            $main_category = $request->main_category;
            $categories = $categories->where('parent_id',$main_category );
        }
        $categories = $categories->paginate(20);
        return view('seller.product.products.categories_comission', compact('categories','main_categories', 'search', 'main_category'));
    }
}
