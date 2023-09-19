@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h1 class="mb-0 h6">{{ translate('Edit Product') }}</h5>
    </div>
    <div class="">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form class="form form-horizontal mar-top" action="{{route('products.update', $product->id)}}" method="POST" enctype="multipart/form-data" id="choice_form">
            <div class="row gutters-5">
                <div class="col-lg-8">
                    <input name="_method" type="hidden" value="POST">
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    @csrf
                    <div class="card">
                        <ul class="nav nav-tabs nav-fill border-light">
                            @foreach (\App\Models\Language::all() as $key => $language)
                                <li class="nav-item">
                                    <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('products.admin.edit', ['id'=>$product->id, 'lang'=> $language->code] ) }}">
                                        <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                                        <span>{{$language->name}}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Product Name')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="name" placeholder="{{translate('Product Name')}}" value="{{ $product->getTranslation('name', $lang) }}" required>
                                </div>
                            </div>
                            <div class="form-group row" id="category">
                                <label class="col-lg-3 col-form-label">{{translate('Category')}}</label>
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
                            <input type="hidden" name="category_id" value="{{$product->category_id}}" id="final_category_id">
                            <div class="form-group row" id="brand">
                                <label class="col-lg-3 col-from-label">{{translate('Brand')}}</label>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id" data-live-search="true">
                                        <option value="">{{ translate('Select Brand') }}</option>
                                        @foreach (\App\Models\Brand::all() as $brand)
                                            <option value="{{ $brand->id }}" @if($product->brand_id == $brand->id) selected @endif>{{ $brand->getTranslation('name') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Unit')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i> </label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="unit" placeholder="{{ translate('Unit (e.g. KG, Pc etc)') }}" value="{{$product->getTranslation('unit', $lang)}}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{translate('Weight')}} <small>({{ translate('In Kg') }})</small></label>
                                <div class="col-md-8">
                                    <input type="number" class="form-control" name="weight" value="{{ $product->weight }}" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Minimum Purchase Qty')}}</label>
                                <div class="col-lg-8">
                                    <input type="number" lang="en" class="form-control" name="min_qty" value="@if($product->min_qty <= 1){{1}}@else{{$product->min_qty}}@endif" min="1" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Tags')}}</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control aiz-tag-input" name="tags[]" id="tags" value="{{ $product->tags }}" placeholder="{{ translate('Type to add a tag') }}" data-role="tagsinput">
                                </div>
                            </div>

                            @if (addon_is_activated('pos_system'))
                                <div class="form-group row">
                                    <label class="col-lg-3 col-from-label">{{translate('Barcode')}}</label>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" name="barcode" placeholder="{{ translate('Barcode') }}" value="{{ $product->barcode }}">
                                    </div>
                                </div>
                            @endif

                            @if (addon_is_activated('refund_request'))
                                <div class="form-group row">
                                    <label class="col-lg-3 col-from-label">{{translate('Refundable')}}</label>
                                    <div class="col-lg-8">
                                        <label class="aiz-switch aiz-switch-success mb-0" style="margin-top:5px;">
                                            <input type="checkbox" name="refundable" @if ($product->refundable == 1) checked @endif value="1">
                                            <span class="slider round"></span></label>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Product Images')}}</h5>
                        </div>
                        <div class="card-body">

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Gallery Images')}}</label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="photos" value="{{ $product->photos }}" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Thumbnail Image')}} <small>(290x300)</small></label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="thumbnail_img" value="{{ $product->thumbnail_img }}" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="form-group row">
                                                        <label class="col-lg-3 col-from-label">{{translate('Gallery Images')}}</label>
                            <div class="col-lg-8">
                                <div id="photos">
                                    @if(is_array(json_decode($product->photos)))
                                    @foreach (json_decode($product->photos) as $key => $photo)
                                    <div class="col-md-4 col-sm-4 col-xs-6">
                                        <div class="img-upload-preview">
                                            <img loading="lazy"  src="{{ uploaded_asset($photo) }}" alt="" class="img-responsive">
                                                <input type="hidden" name="previous_photos[]" value="{{ $photo }}">
                                                <button type="button" class="btn btn-danger close-btn remove-files"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                        </div> --}}
                            {{-- <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Thumbnail Image')}} <small>(290x300)</small></label>
                                <div class="col-lg-8">
                                    <div id="thumbnail_img">
                                        @if ($product->thumbnail_img != null)
                                        <div class="col-md-4 col-sm-4 col-xs-6">
                                            <div class="img-upload-preview">
                                                <img loading="lazy"  src="{{ uploaded_asset($product->thumbnail_img) }}" alt="" class="img-responsive">
                                                <input type="hidden" name="previous_thumbnail_img" value="{{ $product->thumbnail_img }}">
                                                <button type="button" class="btn btn-danger close-btn remove-files"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Product Videos')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Video Provider')}}</label>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker" name="video_provider" id="video_provider">
                                        <option value="youtube" <?php if ($product->video_provider == 'youtube') echo "selected"; ?> >{{translate('Youtube')}}</option>
                                        <option value="dailymotion" <?php if ($product->video_provider == 'dailymotion') echo "selected"; ?> >{{translate('Dailymotion')}}</option>
                                        <option value="vimeo" <?php if ($product->video_provider == 'vimeo') echo "selected"; ?> >{{translate('Vimeo')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Video Link')}}</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="video_link" value="{{ $product->video_link }}" placeholder="{{ translate('Video Link') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Product Variation')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row gutters-5">
                                <div class="col-lg-3">
                                    <input type="text" class="form-control" value="{{translate('Colors')}}" disabled>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker" data-live-search="true" data-selected-text-format="count" name="colors[]" id="colors" multiple>
                                        @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                            <option
                                                    value="{{ $color->code }}"
                                                    data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>"
                                                    <?php if (in_array($color->code, json_decode($product->colors))) echo 'selected' ?>
                                            ></option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-1">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input value="1" type="checkbox" name="colors_active" <?php if (count(json_decode($product->colors)) > 0) echo "checked"; ?> >
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row gutters-5">
                                <div class="col-lg-3">
                                    <input type="text" class="form-control" value="{{translate('Attributes')}}" disabled>
                                </div>
                                <div class="col-lg-8">
                                    <select name="choice_attributes[]" id="choice_attributes" data-selected-text-format="count" data-live-search="true" class="form-control aiz-selectpicker" multiple data-placeholder="{{ translate('Choose Attributes') }}">
                                        @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                            <option value="{{ $attribute->id }}" @if($product->attributes != null && in_array($attribute->id, json_decode($product->attributes, true))) selected @endif>{{ $attribute->getTranslation('name') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="">
                                <p>{{ translate('Choose the attributes of this product and then input values of each attribute') }}</p>
                                <br>
                            </div>

                            <div class="customer_choice_options" id="customer_choice_options">
                                @foreach (json_decode($product->choice_options) as $key => $choice_option)
                                    <div class="form-group row">
                                        <div class="col-lg-3">
                                            <input type="hidden" name="choice_no[]" value="{{ $choice_option->attribute_id }}">
                                            <input type="text" class="form-control" name="choice[]" value="{{ optional(\App\Models\Attribute::find($choice_option->attribute_id))->getTranslation('name') }}" placeholder="{{ translate('Choice Title') }}" disabled>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_{{ $choice_option->attribute_id }}[]" multiple>
                                                @foreach (\App\Models\AttributeValue::where('attribute_id', $choice_option->attribute_id)->get() as $row)
                                                    <option value="{{ $row->value }}" @if( in_array($row->value, $choice_option->values)) selected @endif>
                                                        {{ $row->value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            {{-- <input type="text" class="form-control aiz-tag-input" name="choice_options_{{ $choice_option->attribute_id }}[]" placeholder="{{ translate('Enter choice values') }}" value="{{ implode(',', $choice_option->values) }}" data-on-change="update_sku"> --}}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Product price + stock')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Unit price')}}</label>
                                <div class="col-lg-6">
                                    <input type="text" placeholder="{{translate('Unit price')}}" name="unit_price" class="form-control" value="{{$product->unit_price}}" required>
                                </div>
                            </div>

                            @php
                                $start_date = date('d-m-Y H:i:s', $product->discount_start_date);
                                $end_date = date('d-m-Y H:i:s', $product->discount_end_date);
                            @endphp

                            <div class="form-group row">
                                <label class="col-sm-3 col-from-label" for="start_date">{{translate('Discount Date Range')}}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control aiz-date-range" @if($product->discount_start_date && $product->discount_end_date) value="{{ $start_date.' to '.$end_date }}" @endif name="date_range" placeholder="{{translate('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Discount')}}</label>
                                <div class="col-lg-6">
                                    <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Discount')}}" name="discount" class="form-control" value="{{ $product->discount }}" required>
                                </div>
                                <div class="col-lg-3">
                                    <select class="form-control aiz-selectpicker" name="discount_type" required>
                                        <option value="amount" <?php if ($product->discount_type == 'amount') echo "selected"; ?> >{{translate('Flat')}}</option>
                                        <option value="percent" <?php if ($product->discount_type == 'percent') echo "selected"; ?> >{{translate('Percent')}}</option>
                                    </select>
                                </div>
                            </div>

                            @if(addon_is_activated('club_point'))
                                <div class="form-group row">
                                    <label class="col-md-3 col-from-label">
                                        {{translate('Set Point')}}
                                    </label>
                                    <div class="col-md-6">
                                        <input type="number" lang="en" min="0" value="{{ $product->earn_point }}" step="0.01" placeholder="{{ translate('1') }}" name="earn_point" class="form-control">
                                    </div>
                                </div>
                            @endif

                            <div id="show-hide-div">
                                <div class="form-group row" id="quantity">
                                    <label class="col-lg-3 col-from-label">{{translate('Quantity')}}</label>
                                    <div class="col-lg-6">
                                        <input type="number" lang="en" value="{{ optional($product->stocks->first())->qty }}" step="1" placeholder="{{translate('Quantity')}}" name="current_stock" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 col-from-label">
                                        {{translate('SKU')}}
                                    </label>
                                    <div class="col-md-6">
                                        <input  readonly type="text" placeholder="{{ translate('SKU') }}" value="{{ optional($product->stocks->first())->sku }}" name="sku" class="form-control">
                                    </div>
                                </div>
                            </div>
{{--                            <div class="form-group row">--}}
{{--                                <label class="col-md-3 col-from-label">--}}
{{--                                    {{translate('External link')}}--}}
{{--                                </label>--}}
{{--                                <div class="col-md-9">--}}
{{--                                    <input type="text" placeholder="{{ translate('External link') }}" name="external_link" value="{{ $product->external_link }}" class="form-control">--}}
{{--                                    <small class="text-muted">{{translate('Leave it blank if you do not use external site link')}}</small>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="form-group row">--}}
{{--                                <label class="col-md-3 col-from-label">--}}
{{--                                    {{translate('External link button text')}}--}}
{{--                                </label>--}}
{{--                                <div class="col-md-9">--}}
{{--                                    <input type="text" placeholder="{{ translate('External link button text') }}" name="external_link_btn" value="{{ $product->external_link_btn }}" class="form-control">--}}
{{--                                    <small class="text-muted">{{translate('Leave it blank if you do not use external site link')}}</small>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <br>--}}
                            <div class="sku_combination" id="sku_combination">

                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Product Description')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Description')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                                <div class="col-lg-9">
                                    <textarea class="aiz-text-editor" name="description">{{ $product->getTranslation('description', $lang) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Shipping Cost')}}</h5>
                    </div>
                    <div class="card-body">

                    </div>
                </div>-->

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('PDF Specification')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('PDF Specification')}}</label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="pdf" value="{{ $product->pdf }}" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('SEO Meta Tags')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Meta Title')}}</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="meta_title" value="{{ $product->meta_title }}" placeholder="{{translate('Meta Title')}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{translate('Description')}}</label>
                                <div class="col-lg-8">
                                    <textarea name="meta_description" rows="8" class="form-control">{{ $product->meta_description }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Meta Images')}}</label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="meta_img" value="{{ $product->meta_img }}" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{translate('Slug')}}</label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="{{translate('Slug')}}" id="slug" name="slug" value="{{ $product->slug }}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6" class="dropdown-toggle" data-toggle="collapse" data-target="#collapse_2">
                                {{translate('Shipping Configuration')}}
                            </h5>
                        </div>
                        <div class="card-body collapse show" id="collapse_2">
                            @if (get_setting('shipping_type') == 'product_wise_shipping')
                                <div class="form-group row">
                                    <label class="col-lg-6 col-from-label">{{translate('Free Shipping')}}</label>
                                    <div class="col-lg-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="radio" name="shipping_type" value="free" @if($product->shipping_type == 'free') checked @endif>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-6 col-from-label">{{translate('Flat Rate')}}</label>
                                    <div class="col-lg-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="radio" name="shipping_type" value="flat_rate" @if($product->shipping_type == 'flat_rate') checked @endif>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flat_rate_shipping_div" style="display: none">
                                    <div class="form-group row">
                                        <label class="col-lg-6 col-from-label">{{translate('Shipping cost')}}</label>
                                        <div class="col-lg-6">
                                            <input type="number" lang="en" min="0" value="{{ $product->shipping_cost }}" step="0.01" placeholder="{{ translate('Shipping cost') }}" name="flat_shipping_cost" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{translate('Is Product Quantity Mulitiply')}}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" name="is_quantity_multiplied" value="1" @if($product->is_quantity_multiplied == 1) checked @endif>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <p>
                                    {{ translate('Product wise shipping cost is disable. Shipping cost is configured from here') }}
                                    <a href="{{route('shipping_configuration.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index','shipping_configuration.edit','shipping_configuration.update'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Shipping Configuration')}}</span>
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Low Stock Quantity Warning')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">
                                    {{translate('Quantity')}}
                                </label>
                                <input type="number" name="low_stock_quantity" value="{{ $product->low_stock_quantity }}" min="0" step="1" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">
                                {{translate('Stock Visibility State')}}
                            </h5>
                        </div>

                        <div class="card-body">

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{translate('Show Stock Quantity')}}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="stock_visibility_state" value="quantity" @if($product->stock_visibility_state == 'quantity') checked @endif>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{translate('Show Stock With Text Only')}}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="stock_visibility_state" value="text" @if($product->stock_visibility_state == 'text') checked @endif>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{translate('Hide Stock')}}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="stock_visibility_state" value="hide" @if($product->stock_visibility_state == 'hide') checked @endif>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Cash On Delivery')}}</h5>
                        </div>
                        <div class="card-body">
                            @if (get_setting('cash_payment') == '1')
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-md-6 col-from-label">{{translate('Status')}}</label>
                                            <div class="col-md-6">
                                                <label class="aiz-switch aiz-switch-success mb-0">
                                                    <input type="checkbox" name="cash_on_delivery" value="1" @if($product->cash_on_delivery == 1) checked @endif>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p>
                                    {{ translate('Cash On Delivery option is disabled. Activate this feature from here') }}
                                    <a href="{{route('activation.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index','shipping_configuration.edit','shipping_configuration.update'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Cash Payment Activation')}}</span>
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Featured')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-md-6 col-from-label">{{translate('Status')}}</label>
                                        <div class="col-md-6">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="featured" value="1" @if($product->featured == 1) checked @endif>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Todays Deal')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-md-6 col-from-label">{{translate('Status')}}</label>
                                        <div class="col-md-6">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="todays_deal" value="1" @if($product->todays_deal == 1) checked @endif>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Flash Deal')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">
                                    {{translate('Add To Flash')}}
                                </label>
                                <select class="form-control aiz-selectpicker" name="flash_deal_id" id="video_provider">
                                    <option value="">{{ translate('Choose Flash Title') }}</option>
                                    @foreach(\App\Models\FlashDeal::where("status", 1)->get() as $flash_deal)
                                        <option value="{{ $flash_deal->id }}" @if($product->flash_deal_product && $product->flash_deal_product->flash_deal_id == $flash_deal->id) selected @endif>
                                            {{ $flash_deal->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="name">
                                    {{translate('Discount')}}
                                </label>
                                <input type="number" name="flash_discount" value="{{ $product->discount }}" min="0" step="0.01" class="form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label for="name">
                                    {{translate('Discount Type')}}
                                </label>
                                <select class="form-control aiz-selectpicker" name="flash_discount_type" id="">
                                    <option value="">{{ translate('Choose Discount Type') }}</option>
                                    <option value="amount" @if($product->discount_type == 'amount') selected @endif>
                                        {{translate('Flat')}}
                                    </option>
                                    <option value="percent" @if($product->discount_type == 'percent') selected @endif>
                                        {{translate('Percent')}}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('Estimate Shipping Time')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">
                                    {{translate('Shipping Days')}}
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="est_shipping_days" value="{{ $product->est_shipping_days }}" min="1" step="1" placeholder="{{translate('Shipping Days')}}">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupPrepend">{{translate('Days')}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{translate('VAT & Tax')}}</h5>
                        </div>
                        <div class="card-body">
                            @foreach(\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                                <label for="name">
                                    {{$tax->name}}
                                    <input type="hidden" value="{{$tax->id}}" name="tax_id[]">
                                </label>

                                @php
                                    $tax_amount = 0;
                                    $tax_type = '';
                                    foreach($tax->product_taxes as $row) {
                                        if($product->id == $row->product_id) {
                                            $tax_amount = $row->tax;
                                            $tax_type = $row->tax_type;
                                        }
                                    }
                                @endphp

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <input type="number" lang="en" min="0" value="{{ $tax_amount }}" step="0.01" placeholder="{{ translate('Tax') }}" name="tax[]" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <select class="form-control aiz-selectpicker" name="tax_type[]">
                                            <option value="amount" @if($tax_type == 'amount') selected @endif>
                                                {{translate('Flat')}}
                                            </option>
                                            <option value="percent" @if($tax_type == 'percent') selected @endif>
                                                {{translate('Percent')}}
                                            </option>
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
                            @php
                                $idz = $product->id;
                                $warranty = DB::table('product_warrantys')
                                    ->where('product_id', $idz)
                                    ->first();

                                // Check if a valid warranty record was found
                                if ($warranty) {
                                    // Check if the "warranty_type" property is set and non-empty
                                    $warranty_type = isset($warranty->warranty_type) ? $warranty->warranty_type : null;

                                    // Check if the "warranty_period" property is set and non-empty
                                    $warranty_period = isset($warranty->warranty_period) ? $warranty->warranty_period : null;
                                } else {
                                    // No warranty record found, set default values or handle as needed
                                    $warranty_type = null;
                                    $warranty_period = null;
                                }
                            @endphp

                            <div class="form-row">
                                <div class="form-group col-md-10">
                                    <select class="form-control aiz-selectpicker" name="warranty_type">
                                        <option value="">{{ translate('NO Warranty') }}</option>
                                        <option value="Brand Warranty" {{ $warranty_type === 'Brand Warranty' ? 'selected' : '' }}>
                                            {{ translate('Brand Warranty') }}
                                        </option>
                                        <option value="Agent Warranty" {{ $warranty_type === 'Agent Warranty' ? 'selected' : '' }}>
                                            {{ translate('Agent Warranty') }}
                                        </option>
                                        <option value="Instamart Warranty" {{ $warranty_type === 'Instamart Warranty' ? 'selected' : '' }}>
                                            {{ translate('Instamart Warranty') }}
                                        </option>
                                        <option value="Seller Warranty" {{ $warranty_type === 'Seller Warranty' ? 'selected' : '' }}>
                                            {{ translate('Seller Warranty') }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <label for="name">
                                Warranty Period
                            </label>
                            <div class="form-row">
                                <div class="form-group col-md-10">
                                    <select class="form-control aiz-selectpicker" name="warranty_period">
                                        @php
                                            $options = [
                                                '1 Month' => translate('1 Month'),
                                                '2 Months' => translate('2 Months'),
                                                '3 Months' => translate('3 Months'),
                                                '6 Months' => translate('6 Months'),
                                                '7 Months' => translate('7 Months'),
                                                '8 Months' => translate('8 Months'),
                                                '9 Months' => translate('9 Months'),
                                                '10 Months' => translate('10 Months'),
                                                '11 Months' => translate('11 Months'),
                                                '1 Year' => translate('1 Year'),
                                                '2 Years' => translate('2 Years'),
                                                '3 Years' => translate('3 Years'),
                                                '4 Years' => translate('4 Years'),
                                                '5 Years' => translate('5 Years'),
                                                '6 Years' => translate('6 Years'),
                                                '7 Years' => translate('7 Years'),
                                                '8 Years' => translate('8 Years'),
                                                '9 Years' => translate('9 Years'),
                                                '10 Years' => translate('10 Years'),
                                                '11 Years' => translate('11 Years'),
                                                '12 Years' => translate('12 Years'),
                                                '13 Years' => translate('13 Years'),
                                                '14 Years' => translate('14 Years'),
                                                '15 Years' => translate('15 Years'),
                                                '16 Years' => translate('16 Years'),
                                                '17 Years' => translate('17 Years'),
                                                '18 Years' => translate('18 Years'),
                                                '19 Years' => translate('19 Years'),
                                                '25 Years' => translate('25 Years'),
                                                '30 Years' => translate('30 Years'),
                                                'Life Time' => translate('Life Time'),
                                            ];
                                        @endphp
                                        @foreach ($options as $value => $label)
                                            <option value="{{ $value }}" {{ $warranty_period === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                        </div>
                    </div>
                    {{--       End Warranty        --}}
                </div>
                <div class="col-12">
                    <div class="mb-3 text-right">
                        <button type="submit" name="button" class="btn btn-info">{{ translate('Update Product') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@section('script')

    <script type="text/javascript">
        $(document).ready(function (){
            show_hide_shipping_div();
        });

        $("[name=shipping_type]").on("change", function (){
            show_hide_shipping_div();
        });

        function show_hide_shipping_div() {
            var shipping_val = $("[name=shipping_type]:checked").val();

            $(".flat_rate_shipping_div").hide();

            if(shipping_val == 'flat_rate'){
                $(".flat_rate_shipping_div").show();
            }
        }

        function add_more_customer_choice_option(i, name){
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type:"POST",
                url:'{{ route('products.add-more-choice-option') }}',
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
            if(!$('input[name="colors_active"]').is(':checked')){
                $('#colors').prop('disabled', true);
                AIZ.plugins.bootstrapSelect('refresh');
            }
            else{
                $('#colors').prop('disabled', false);
                AIZ.plugins.bootstrapSelect('refresh');
            }
            update_sku();
        });

        $(document).on("change", ".attribute_choice",function() {
            update_sku();
        });

        $('#colors').on('change', function() {
            update_sku();
        });

        function delete_row(em){
            $(em).closest('.form-group').remove();
            update_sku();
        }

        function delete_variant(em){
            $(em).closest('.variant').remove();
        }

        function update_sku(){
            $.ajax({
                type:"POST",
                url:'{{ route('products.sku_combination_edit') }}',
                data:$('#choice_form').serialize(),
                success: function(data){
                    $('#sku_combination').html(data);
                    setTimeout(() => {
                        AIZ.uploader.previewGenerate();
                    }, "2000");
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

        AIZ.plugins.tagify();

        $(document).ready(function(){
            update_sku();

            $('.remove-files').on('click', function(){
                $(this).parents(".col-md-4").remove();
            });
        });

        $('#choice_attributes').on('change', function() {
            $.each($("#choice_attributes option:selected"), function(j, attribute){
                flag = false;
                $('input[name="choice_no[]"]').each(function(i, choice_no) {
                    if($(attribute).val() == $(choice_no).val()){
                        flag = true;
                    }
                });
                if(!flag){
                    add_more_customer_choice_option($(attribute).val(), $(attribute).text());
                }
            });

            var str = @php echo $product->attributes @endphp;

            $.each(str, function(index, value){
                flag = false;
                $.each($("#choice_attributes option:selected"), function(j, attribute){
                    if(value == $(attribute).val()){
                        flag = true;
                    }
                });
                if(!flag){
                    $('input[name="choice_no[]"][value="'+value+'"]').parent().parent().remove();
                }
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

const selected_category_id = {{$product->category_id}};

// Function to remove all li elements by their ids
function removeAllLiElementsById(...ids) {
    ids.forEach(id => {
        var ulElement = document.getElementById(id);
        while (ulElement.firstChild) {
            ulElement.removeChild(ulElement.firstChild);
        }
    });
}

// Function to find a category by its ID in the categoriesData
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

const selectedCategoryPath = findCategoryById(categoriesData, selected_category_id);

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

function updateCategories(selected_category) {
    // Function to update the final_category_id input
    function updateFinalCategoryId(categoryId) {
        // console.log('set to ' + categoryId);
        $('#final_category_id').val(categoryId);
    }

    updateSelectedCategoryLabel(selectedCategoryPath);
    updateFinalCategoryId(selected_category);
}

updateCategories(selected_category_id);

// Document Ready Function
$(document).ready(function () {
    // Function to populate child categories in a select element
    function populateChildCategories(containerId, childCategories, notSelectedText) {
        let container = $('#' + containerId);
        container.empty();
        container.append('<option value="" selected>' + notSelectedText + '</option>');

        childCategories.forEach(function (childCategory) {
            container.append('<option value="' + childCategory.id + '">' + childCategory.name + '</option>');
        });

        $('.aiz-selectpicker').selectpicker('refresh');
    }

    // Function to hide an element by its ID
    function hideElementById(elementId) {
        $('#' + elementId).hide();
    }

    // Function to display child categories in the side menu
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
        // console.log('runned');
        $('.side-menu #main-category-list li').removeClass('selected');
        $(this).addClass('selected');
        let categoryId = $(this).data('category-id');
        updateCategories(categoryId);
        displayFirstChildCategories(categoryId);
    }

    // Attach the click event to the main category list items
    $('.side-menu #main-category-list li').click(selectMainCategory);

    // Function to handle first child category selection
    function selectFirstChildCategory() {
        removeAllLiElementsById("third-child-category-list");
        $('.side-menu #first-child-category-list li').removeClass('selected');
        $(this).addClass('selected');
        let categoryId = $(this).data('category-id');
        updateCategories(categoryId);
        displaySecondChildCategories(categoryId);
    }

    // Attach the click event to the first child category list items
    $('.side-menu #first-child-category-list').on('click', 'li', selectFirstChildCategory);

    // Function to handle second child category selection
    function selectSecondChildCategory() {
        $('.side-menu #second-child-category-list li').removeClass('selected');
        $(this).addClass('selected');
        let categoryId = $(this).data('category-id');
        updateCategories(categoryId);
        displayThirdChildCategories(categoryId);
    }

    // Attach the click event to the second child category list items
    $('.side-menu #second-child-category-list').on('click', 'li', selectSecondChildCategory);

    // Function to handle third child category selection
    function selectThirdChildCategory() {
        $('.side-menu #third-child-category-list li').removeClass('selected');
        $(this).addClass('selected');
        let categoryId = $(this).data('category-id');
        updateCategories(categoryId);
        displayFourthChildCategories(categoryId);
    }

    // Attach the click event to the third child category list items
    $('.side-menu #third-child-category-list').on('click', 'li', selectThirdChildCategory);

    // Function to handle fourth child category selection
    function selectFourthChildCategory() {
        $('.side-menu #fourth-child-category-list li').removeClass('selected');
        $(this).addClass('selected');
        let categoryId = $(this).data('category-id');
        updateCategories(categoryId);
        displayFifthChildCategories(categoryId);
    }

    // Attach the click event to the fourth child category list items
    $('.side-menu #fourth-child-category-list').on('click', 'li', selectFourthChildCategory);

    // Initially, trigger the click event to display first child categories for the default selected main category (if any)
    $('.side-menu #main-category-list li.selected').trigger('click');
});


    </script>

@endsection
