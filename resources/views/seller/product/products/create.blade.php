@extends('seller.layouts.app')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ translate('Add Your Product') }}</h1>
            </div>
        </div>
    </div>

    <!-- Error Meassages -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="" action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data"
          id="choice_form">
        <div class="row gutters-5">
            <div class="col-lg-8">
                @csrf
                <input type="hidden" name="added_by" value="seller">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Product Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Product Name') }}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="name"
                                       placeholder="{{ translate('Product Name') }}" onchange="update_sku()" required>
                            </div>
                        </div>
                        <div class="form-group row" id="category">
                            <label class="col-lg-3 col-from-label">{{translate('Category')}}</label>
                            <div class="col-lg-8">
                                <div class="side-menu-container">
                                    <div class="side-menu">
                                        <div class="col-md-4">
                                            <ul id="main-category-list">
                                                <!-- Main Categories -->
                                                @foreach ($categories as $category)
                                                    <li class="category-item" data-category-id="{{ $category->id }}">{{ $category->getTranslation('name') }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <ul class="child-category-list" id="first-child-category-list"></ul>
                                        </div>
                                        <div class="col-md-4">
                                            <ul class="child-category-list" id="second-child-category-list"></ul>
                                        </div>
                                        <div class="col-md-4">
                                            <ul class="child-category-list" id="third-child-category-list"></ul>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top: 10px; font-weight:bold" id="selected-category-label">Selected Categories:</div>
                            </div>
                        </div>
                        <!-- Hidden input field to hold the final selected category ID -->
                        <input type="hidden" name="category_id" id="final_category_id">

                        <div class="form-group row" id="brand">
                            <label class="col-md-3 col-from-label">{{ translate('Brand') }}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id"
                                        data-live-search="true">
                                    <option value="">{{ translate('Select Brand') }}</option>
                                    @foreach (\App\Models\Brand::all() as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Unit') }}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="unit"
                                       placeholder="{{ translate('Unit (e.g. KG, Pc etc)') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Weight') }}
                                <small>({{ translate('In Kg') }})</small></label>
                            <div class="col-md-8">
                                <input type="number" class="form-control" name="weight" step="0.01" value="0.00"
                                       placeholder="0.00">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Minimum Purchase Qty') }}</label>
                            <div class="col-md-8">
                                <input type="number" lang="en" class="form-control" name="min_qty" value="1"
                                       min="1" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Tags') }}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control aiz-tag-input" name="tags[]"
                                       placeholder="{{ translate('Type and hit enter to add a tag') }}">
                            </div>
                        </div>
                        @if (addon_is_activated('pos_system'))
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ translate('Barcode') }}</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="barcode"
                                           placeholder="{{ translate('Barcode') }}">
                                </div>
                            </div>
                        @endif
                        @if (addon_is_activated('refund_request'))
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ translate('Refundable') }}</label>
                                <div class="col-md-8">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="refundable" checked value="1">
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Product Images') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label"
                                   for="signinSrEmail">{{ translate('Gallery Images') }}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image"
                                     data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="photos" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ translate('Thumbnail Image') }}
                                <small>(290x300)</small></label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="thumbnail_img" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Product Videos') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Video Provider') }}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="video_provider" id="video_provider">
                                    <option value="youtube">{{ translate('Youtube') }}</option>
                                    <option value="dailymotion">{{ translate('Dailymotion') }}</option>
                                    <option value="vimeo">{{ translate('Vimeo') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Video Link') }}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="video_link"
                                       placeholder="{{ translate('Video Link') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Product Variation') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-3">
                                <input type="text" class="form-control" value="{{ translate('Colors') }}" disabled>
                            </div>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="colors[]"
                                        data-selected-text-format="count" id="colors" multiple disabled>
                                    @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                        <option value="{{ $color->code }}"
                                                data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>">
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" type="checkbox" name="colors_active">
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-3">
                                <input type="text" class="form-control" value="{{ translate('Attributes') }}"
                                       disabled>
                            </div>
                            <div class="col-md-8">
                                <select name="choice_attributes[]" id="choice_attributes"
                                        class="form-control aiz-selectpicker" data-live-search="true"
                                        data-selected-text-format="count" multiple
                                        data-placeholder="{{ translate('Choose Attributes') }}">
                                    @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                        <option value="{{ $attribute->id }}">{{ $attribute->getTranslation('name') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <p>{{ translate('Choose the attributes of this product and then input values of each attribute') }}
                            </p>
                            <br>
                        </div>

                        <div class="customer_choice_options" id="customer_choice_options">

                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Product price + stock') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Unit price') }}</label>
                            <div class="col-md-6">
                                <input type="number" lang="en" min="0" value="0" step="0.01"
                                       placeholder="{{ translate('Unit price') }}" name="unit_price" class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 control-label"
                                   for="start_date">{{ translate('Discount Date Range') }}</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control aiz-date-range" name="date_range"
                                       placeholder="{{ translate('Select Date') }}" data-time-picker="true"
                                       data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Discount') }}</label>
                            <div class="col-md-6">
                                <input type="number" lang="en" min="0" value="0" step="0.01"
                                       placeholder="{{ translate('Discount') }}" name="discount" class="form-control"
                                       required>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control aiz-selectpicker" name="discount_type">
                                    <option value="amount">{{ translate('Flat') }}</option>
                                    <option value="percent">{{ translate('Percent') }}</option>
                                </select>
                            </div>
                        </div>

                        <div id="show-hide-div">
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ translate('Quantity') }}</label>
                                <div class="col-md-6">
                                    <input type="number" lang="en" min="0" value="0" step="1"
                                           placeholder="{{ translate('Quantity') }}" name="current_stock"
                                           class="form-control" required>
                                </div>
                            </div>
                            <div style="display: none !important" class="form-group row">
                                <label class="col-md-3 col-from-label">
                                    {{ translate('SKU') }}
                                </label>
                                <div class="col-md-6">
                                    <input type="hidden" placeholder="{{ translate('SKU') }}" name="sku"
                                           class="form-control">
                                </div>
                            </div>
                        </div>
{{--                        <div class="form-group row">--}}
{{--                            <label class="col-md-3 col-from-label">--}}
{{--                                {{ translate('External link') }}--}}
{{--                            </label>--}}
{{--                            <div class="col-md-9">--}}
{{--                                <input type="text" placeholder="{{ translate('External link') }}"--}}
{{--                                       name="external_link" class="form-control">--}}
{{--                                <small--}}
{{--                                        class="text-muted">{{ translate('Leave it blank if you do not use external site link') }}</small>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="form-group row">--}}
{{--                            <label class="col-md-3 col-from-label">--}}
{{--                                {{ translate('External link button text') }}--}}
{{--                            </label>--}}
{{--                            <div class="col-md-9">--}}
{{--                                <input type="text" placeholder="{{ translate('External link button text') }}"--}}
{{--                                       name="external_link_btn" class="form-control">--}}
{{--                                <small--}}
{{--                                        class="text-muted">{{ translate('Leave it blank if you do not use external site link') }}</small>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <br>--}}
                        <div class="sku_combination" id="sku_combination">

                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Product Description') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Description') }}</label>
                            <div class="col-md-8">
                                <textarea class="aiz-text-editor" name="description"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('PDF Specification') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label"
                                   for="signinSrEmail">{{ translate('PDF Specification') }}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="document">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="pdf" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('SEO Meta Tags') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Meta Title') }}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="meta_title"
                                       placeholder="{{ translate('Meta Title') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Description') }}</label>
                            <div class="col-md-8">
                                <textarea name="meta_description" rows="8" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label"
                                   for="signinSrEmail">{{ translate('Meta Image') }}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="meta_img" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">
                            {{ translate('Shipping Configuration') }}
                        </h5>
                    </div>

                    <div class="card-body">
                        @if (get_setting('shipping_type') == 'product_wise_shipping')
                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{ translate('Free Shipping') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="shipping_type" value="free" checked>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{ translate('Flat Rate') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="shipping_type" value="flat_rate">
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="flat_rate_shipping_div" style="display: none">
                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{ translate('Shipping cost') }}</label>
                                    <div class="col-md-6">
                                        <input type="number" lang="en" min="0" value="0"
                                               step="0.01" placeholder="{{ translate('Shipping cost') }}"
                                               name="flat_shipping_cost" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{translate('Is Product Quantity Mulitiply')}}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="is_quantity_multiplied" value="1">
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        @else
                            <p>
                                {{ translate('Shipping configuration is maintained by Admin.') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Low Stock Quantity Warning') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name">
                                {{ translate('Quantity') }}
                            </label>
                            <input type="number" name="low_stock_quantity" value="1" min="0"
                                   step="1" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">
                            {{ translate('Stock Visibility State') }}
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="form-group row">
                            <label class="col-md-6 col-from-label">{{ translate('Show Stock Quantity') }}</label>
                            <div class="col-md-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="radio" name="stock_visibility_state" value="quantity" checked>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-6 col-from-label">{{ translate('Show Stock With Text Only') }}</label>
                            <div class="col-md-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="radio" name="stock_visibility_state" value="text">
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-6 col-from-label">{{ translate('Hide Stock') }}</label>
                            <div class="col-md-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="radio" name="stock_visibility_state" value="hide">
                                    <span></span>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Cash On Delivery') }}</h5>
                    </div>
                    <div class="card-body">
                        @if (get_setting('cash_payment') == '1')
                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{ translate('Status') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="cash_on_delivery" value="1" checked="">
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        @else
                            <p>
                                {{ translate('Cash On Delivery activation is maintained by Admin.') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Estimate Shipping Time') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name">
                                {{ translate('Shipping Days') }}
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="est_shipping_days" min="1"
                                       step="1" placeholder="{{ translate('Shipping Days') }}">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend">{{ translate('Days') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('VAT & Tax') }}</h5>
                    </div>
                    <div class="card-body">
                        @foreach (\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                            <label for="name">
                                {{ $tax->name }}
                                <input type="hidden" value="{{ $tax->id }}" name="tax_id[]">
                            </label>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <input type="number" lang="en" min="0" value="0" step="0.01"
                                           placeholder="{{ translate('Tax') }}" name="tax[]" class="form-control"
                                           required>
                                </div>
                                <div class="form-group col-md-6">
                                    <select class="form-control aiz-selectpicker" name="tax_type[]">
                                        <option value="amount">{{ translate('Flat') }}</option>
                                        <option value="percent">{{ translate('Percent') }}</option>
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{--        Warranty        --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Service & Warranty') }}</h5>
                    </div>
                    <div class="card-body">
                        <label for="name">
                            Warranty Type
                        </label>
                        <div class="form-row">
                            <div class="form-group col-md-10">
                                <select class="form-control aiz-selectpicker" name="warranty_type">
                                    <option value="">{{ translate('NO Warranty') }}</option>
                                    <option value="Brand Warranty">{{ translate('Brand Warranty') }}</option>
                                    <option value="Agent Warranty">{{ translate('Agent Warranty') }}</option>
                                    <option value="Instamart Warranty">{{ translate('Instamart Warranty') }}</option>
                                    <option value="Seller Warranty">{{ translate('Seller Warranty') }}</option>
                                </select>
                            </div>
                        </div>
                        <label for="name">
                            Warranty Period
                        </label>
                        <div class="form-row">
                            <div class="form-group col-md-10">
                                <select class="form-control aiz-selectpicker" name="warranty_period">
                                    <option value="1 Month">{{ translate('1 Month') }}</option>
                                    <option value="2 Months">{{ translate('2 Months') }}</option>
                                    <option value="3 Months">{{ translate('3 Months') }}</option>
                                    <option value="6 Months">{{ translate('6 Months') }}</option>
                                    <option value="7 Months">{{ translate('7 Months') }}</option>
                                    <option value="8 Months">{{ translate('8 Months') }}</option>
                                    <option value="9 Months">{{ translate('9 Months') }}</option>
                                    <option value="10 Months">{{ translate('10 Months') }}</option>
                                    <option value="11 Months">{{ translate('11 Months') }}</option>
                                    <option value="1 Year">{{ translate('1 Year') }}</option>
                                    <option value="2 Years">{{ translate('2 Years') }}</option>
                                    <option value="3 Years">{{ translate('3 Years') }}</option>
                                    <option value="4 Years">{{ translate('4 Years') }}</option>
                                    <option value="5 Years">{{ translate('5 Years') }}</option>
                                    <option value="6 Years">{{ translate('6 Years') }}</option>
                                    <option value="7 Years">{{ translate('7 Years') }}</option>
                                    <option value="8 Years">{{ translate('8 Years') }}</option>
                                    <option value="9 Years">{{ translate('9 Years') }}</option>
                                    <option value="10 Years">{{ translate('10 Years') }}</option>
                                    <option value="11 Years">{{ translate('11 Years') }}</option>
                                    <option value="12 Years">{{ translate('12 Years') }}</option>
                                    <option value="13 Years">{{ translate('13 Years') }}</option>
                                    <option value="14 Years">{{ translate('14 Years') }}</option>
                                    <option value="15 Years">{{ translate('15 Years') }}</option>
                                    <option value="16 Years">{{ translate('16 Years') }}</option>
                                    <option value="17 Years">{{ translate('17 Years') }}</option>
                                    <option value="18 Years">{{ translate('18 Years') }}</option>
                                    <option value="19 Years">{{ translate('19 Years') }}</option>
                                    <option value="25 Years">{{ translate('25 Years') }}</option>
                                    <option value="30 Years">{{ translate('30 Years') }}</option>
                                    <option value="Life Time">{{ translate('Life Time') }}</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
                {{--       End Warranty        --}}

            </div>
            <div class="col-12">
                <div class="mar-all text-right mb-2">
                    <button type="submit" name="button" value="publish"
                            class="btn btn-primary">{{ translate('Upload Product') }}</button>
                </div>
            </div>
        </div>

    </form>
@endsection

@section('script')
<script type="text/javascript">
    $("[name=shipping_type]").on("change", function() {
        $(".product_wise_shipping_div").hide();
        $(".flat_rate_shipping_div").hide();
        if ($(this).val() == 'product_wise') {
            $(".product_wise_shipping_div").show();
        }
        if ($(this).val() == 'flat_rate') {
            $(".flat_rate_shipping_div").show();
        }

    });

    function add_more_customer_choice_option(i, name){
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"POST",
                url:'{{ route('seller.products.add-more-choice-option') }}',
                data:{
                    attribute_id: i
                },
                success: function(data) {
                    var obj = JSON.parse(data);
                    $('#customer_choice_options').append('\
                <div class="form-group row">\
                    <div class="col-md-3">\
                        <input type="hidden" name="choice_no[]" value="'+i+'">\
                        <input type="text" class="form-control" name="choice[]" value="'+name+'" placeholder="{{ translate('Choice Title') }}" readonly>\
                    </div>\
                    <div class="col-md-8">\
                        <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_'+ i +'[]" multiple>\
                            '+obj+'\
                        </select>\
                    </div>\
                </div>');
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            });


        }


    $('input[name="colors_active"]').on('change', function() {
            if(!$('input[name="colors_active"]').is(':checked')) {
                $('#colors').prop('disabled', true);
                AIZ.plugins.bootstrapSelect('refresh');
            }
            else {
                $('#colors').prop('disabled', false);
                AIZ.plugins.bootstrapSelect('refresh');
            }
            update_sku();
        });


    $(document).on("change", ".attribute_choice", function() {
        update_sku();
    });

    $('#colors').on('change', function() {
        update_sku();
    });

    $('input[name="unit_price"]').on('keyup', function() {
        update_sku();
    });

    // $('input[name="name"]').on('keyup', function() {
    //     update_sku();
    // });

    function delete_row(em) {
        $(em).closest('.form-group row').remove();
        update_sku();
    }

    function delete_variant(em) {
        $(em).closest('.variant').remove();
    }

    function update_sku(){
        console.log('{{ route('seller.products.sku_combination') }}');

            $.ajax({
                type:"POST",
                url:'{{ route('seller.products.sku_combination') }}',
                data:$('#choice_form').serialize(),
                success: function(data) {
                    $('#sku_combination').html(data);
                    AIZ.uploader.previewGenerate();
                    AIZ.plugins.fooTable();
                    if (data.length > 1) {
                        $('#show-hide-div').hide();
                    }
                    else {
                        $('#show-hide-div').show();
                    }
                }
            });
        }

    $('#choice_attributes').on('change', function() {
        $('#customer_choice_options').html(null);
        $.each($("#choice_attributes option:selected"), function() {
            add_more_customer_choice_option($(this).val(), $(this).text());
        });
        update_sku();
    });








          // Check if the categoriesData variable is already defined
          categoriesData = typeof categoriesData !== 'undefined' ? categoriesData : [];

// Loop through the main categories and their childrenCategories
@foreach ($categories as $category)

categoryData = {
id: {{ $category->id }},
name: "{{ $category->getTranslation('name') }}",
children: [
    @foreach ($category->childrenCategories as $childCategory)
    {
    id: {{ $childCategory->id }},
    name: "{{ $childCategory->getTranslation('name') }}",
    @if (count($childCategory->childrenCategories) > 0)
    children: [
        @foreach ($childCategory->childrenCategories as $grandchildCategory)
        {
        id: {{ $grandchildCategory->id }},
        name: "{{ $grandchildCategory->getTranslation('name') }}",
        @if (count($grandchildCategory->childrenCategories) > 0)
        children: [
            @foreach ($grandchildCategory->childrenCategories as $fourthLevelCategory)
            {
            id: {{ $fourthLevelCategory->id }},
            name: "{{ $fourthLevelCategory->getTranslation('name') }}",
            @if (count($fourthLevelCategory->childrenCategories) > 0)
            children: [
                @foreach ($fourthLevelCategory->childrenCategories as $fifthLevelCategory)
                {
                id: {{ $fifthLevelCategory->id }},
                name: "{{ $fifthLevelCategory->getTranslation('name') }}"
                },
                @endforeach
            ],
            @endif
            },
            @endforeach
        ],
        @endif
        },
        @endforeach
    ],
    @endif
    },
    @endforeach
]
};

// Add the categoryData to the categoriesData array
categoriesData.push(categoryData);
@endforeach

// console.log(categoriesData);

function removeAllLiElementsById(...ids) {
ids.forEach(id => {
    const ulElement = document.getElementById(id);
    while (ulElement.firstChild) {
    ulElement.removeChild(ulElement.firstChild);
    }
});
}

function updateCategories(selected_category) {
function findCategoryById(categories, targetId, path = []) {
    for (const category of categories) {
    const newPath = [...path, category]; // Create a new path array with the current category
    if (category.id === targetId) {
        return newPath; // If the target ID is found, return the full path
    }
    if (category.children && category.children.length > 0) {
        const result = findCategoryById(category.children, targetId, newPath);
        if (result) {
        return result;
        }
    }
    }
    return null;
}

const selectedCategoryPath = findCategoryById(categoriesData, selected_category);

// Function to decode HTML entities
function decodeHtmlEntities(text) {
const elem = document.createElement('textarea');
elem.innerHTML = text;
return elem.value;
}

// Function to update the label with the selected category hierarchy
function updateSelectedCategoryLabel(selectedCategoryPath) {
const label = "Selected Categories: " + selectedCategoryPath.map(category => decodeHtmlEntities(category.name)).join(' -> ');
$("#selected-category-label").html(label);
}


updateSelectedCategoryLabel(selectedCategoryPath);
}

function updateFinalCategoryId(categoryId) {
//   console.log('set to ' + categoryId);
$('#final_category_id').val(categoryId);
}

$(document).ready(function () {
function updateFinalCategoryId(categoryId) {
    // console.log('set to ' + categoryId);
    $('#final_category_id').val(categoryId);
    updateCategories(categoryId);
}

function populateChildCategories(containerId, childCategories, notSelectedText) {
    let container = $('#' + containerId);
    container.empty();
    container.append('<option value="" selected>' + notSelectedText + '</option>');

    childCategories.forEach(function (childCategory) {
    container.append('<option value="' + childCategory.id + '">' + childCategory.name + '</option>');
    });

    $('.aiz-selectpicker').selectpicker('refresh');
}

function displayChildCategories(categoryId) {
    let selectedCategory = categoriesData.find(function (category) {
    return category.id == categoryId;
    });

    if (selectedCategory && selectedCategory.children.length > 0) {
    populateChildCategories('child_category_container', selectedCategory.children, 'Not Selected');
    } else {
    $('#child_category_container').empty();
    $('#child_category_container').append('<option value="">No Child Categories</option>');
    $('.aiz-selectpicker').selectpicker('refresh');
    }

    $('#third_child_category_container').empty();
    $('#fourth_child_category_container').empty();
    $('#fifth_child_category_container').empty();
    hideElementById('third_category');
    hideElementById('fourth_category');
    hideElementById('fifth_category');
    $('.aiz-selectpicker').selectpicker('refresh');
}

function displayFirstChildCategories(mainCategoryId) {
    $('#first-child-category-list').empty();
    let selectedMainCategory = categoriesData.find(function (category) {
    return category.id == mainCategoryId;
    });

    if (selectedMainCategory && selectedMainCategory.children.length > 0) {
    selectedMainCategory.children.forEach(function (childCategory) {
        $('#first-child-category-list').append('<li data-category-id="' + childCategory.id + '">' + childCategory.name + '</li>');
    });
    }
}

function displaySecondChildCategories(firstChildCategoryId) {
    $('#second-child-category-list').empty();
    let selectedFirstChildCategory = categoriesData.reduce(function (acc, category) {
    return acc.concat(category.children);
    }, []).find(function (childCategory) {
    return childCategory && childCategory.id  == firstChildCategoryId;
    });

    if (selectedFirstChildCategory && selectedFirstChildCategory.children.length > 0) {
    selectedFirstChildCategory.children.forEach(function (childCategory) {
        $('#second-child-category-list').append('<li data-category-id="' + childCategory.id + '">' + childCategory.name + '</li>');
    });
    }
}

function displayThirdChildCategories(secondChildCategoryId) {
    // console.log("displaythird = " + secondChildCategoryId);

    $('#third-child-category-list').empty();
    let selectedSecondChildCategory = categoriesData.reduce(function (acc, category) {
    return acc.concat(category.children);
    }, []).reduce(function (acc, childCategory) {
    return acc.concat(childCategory.children);
    }, []).find(function (childCategory) {
        // console.log(childCategory);
        return childCategory && childCategory.id === secondChildCategoryId;
    });

    // console.log(selectedSecondChildCategory);
    if (selectedSecondChildCategory && selectedSecondChildCategory.children.length > 0) {
    selectedSecondChildCategory.children.forEach(function (childCategory) {
        $('#third-child-category-list').append('<li data-category-id="' + childCategory.id + '">' + childCategory.name + '</li>');
    });
    }
}

function displayFourthChildCategories(thirdChildCategoryId) {
    $('#fourth-child-category-list').empty();
    let selectedThirdChildCategory = categoriesData.reduce(function (acc, category) {
    return acc.concat(category.children);
    }, []).reduce(function (acc, childCategory) {
    return acc.concat(childCategory.children);
    }, []).reduce(function (acc, childCategory) {
    return acc.concat(childCategory.children);
    }, []).find(function (childCategory) {
    return childCategory && childCategory.id  == thirdChildCategoryId;
    });

    if (selectedThirdChildCategory && selectedThirdChildCategory.children) {
    selectedThirdChildCategory.children.forEach(function (childCategory) {
        $('#fourth-child-category-list').append('<li data-category-id="' + childCategory.id + '">' + childCategory.name + '</li>');
    });
    }
}

function displayFifthChildCategories(fourthChildCategoryId) {
    $('#fifth-child-category-list').empty();
    let selectedFourthChildCategory = categoriesData.reduce(function (acc, category) {
    return acc.concat(category.children);
    }, []).reduce(function (acc, childCategory) {
    return acc.concat(childCategory.children);
    }, []).reduce(function (acc, childCategory) {
    return acc.concat(childCategory.children);
    }, []).reduce(function (acc, childCategory) {
    return acc.concat(childCategory.children);
    }, []).find(function (childCategory) {
    return childCategory && childCategory.id  == fourthChildCategoryId;
    });

    if (selectedFourthChildCategory && selectedFourthChildCategory.children) {
    selectedFourthChildCategory.children.forEach(function (childCategory) {
        $('#fifth-child-category-list').append('<li data-category-id="' + childCategory.id + '">' + childCategory.name + '</li>');
    });
    }
}

// Function to handle main category selection
function selectMainCategory() {
    removeAllLiElementsById("second-child-category-list", "third-child-category-list");

    $('.side-menu #main-category-list li').removeClass('selected');
    $(this).addClass('selected');
    let categoryId = $(this).data('category-id');
    updateFinalCategoryId(categoryId);
    displayFirstChildCategories(categoryId);
}

// Attach the click event to the main category list items
$('.side-menu #main-category-list li').click(selectMainCategory);

// Function to handle first child category selection
function selectFirstChildCategory() {
    removeAllLiElementsById("third-child-category-list");

    $('.side-menu #first-child-category-list li').removeClass('selected');
    $(this).addClass('selected');
    // console.log('first child category selected');
    let categoryId = $(this).data('category-id');
    updateFinalCategoryId(categoryId);
    displaySecondChildCategories(categoryId);
}

// Attach the click event to the first child category list items
$('.side-menu #first-child-category-list').on('click', 'li', selectFirstChildCategory);

// Function to handle second child category selection
function selectSecondChildCategory() {
    $('.side-menu #second-child-category-list li').removeClass('selected');

    $(this).addClass('selected');
    // console.log('second child category selected');
    let categoryId = $(this).data('category-id');
    // console.log(categoryId);

    updateFinalCategoryId(categoryId);
    displayThirdChildCategories(categoryId);
}

// Attach the click event to the second child category list items
$('.side-menu #second-child-category-list').on('click', 'li', selectSecondChildCategory);

// Function to handle third child category selection
function selectThirdChildCategory() {
    $('.side-menu #third-child-category-list li').removeClass('selected');
    $(this).addClass('selected');
    // console.log('third child category selected');
    let categoryId = $(this).data('category-id');
    // console.log(categoryId);
    updateFinalCategoryId(categoryId);
    displayFourthChildCategories(categoryId);
}

// Attach the click event to the third child category list items
$('.side-menu #third-child-category-list').on('click', 'li', selectThirdChildCategory);

// Function to handle fourth child category selection
function selectFourthChildCategory() {
    $('.side-menu #fourth-child-category-list li').removeClass('selected');
    // console.log('fourth child category selected');
    $(this).addClass('selected');
    let categoryId = $(this).data('category-id');
    // console.log(categoryId);
    updateFinalCategoryId(categoryId);
    displayFifthChildCategories(categoryId);
}

// Attach the click event to the fourth child category list items
$('.side-menu #fourth-child-category-list').on('click', 'li', selectFourthChildCategory);

// Initially, trigger the click event to display first child categories for the default selected main category (if any)
$('.side-menu #main-category-list li.selected').trigger('click');
});




</script>









@endsection
