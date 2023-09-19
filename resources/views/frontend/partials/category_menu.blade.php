<div class="aiz-category-menu bg-white rounded-0 border-top" id="category-sidebar" style="border-radius: 5px;">
    <ul class="list-unstyled categories no-scrollbar mb-0 text-left">
        @foreach (\App\Models\Category::where('level', 0)->orderBy('order_level', 'desc')->get()->take(13) as $key => $category)
            <li class="category-nav-element " data-id="{{ $category->id }}" style="padding-top: 6px; padding-bottom: 8px; font-size: .83rem; font-family: Roboto,-apple-system,BlinkMacSystemFont,Segoe UI,Helvetica Neue,Arial,sans-serif; border-radius: 5px;">
                <a href="{{ route('products.category', $category->slug) }}" class="text-truncate text-dark px-4 fs-14  hov-column-gap-1" style="padding-top: 6px; padding-down: 8px; font-size: .83rem; font-family: Roboto,-apple-system,BlinkMacSystemFont,Segoe UI,Helvetica Neue,Arial,sans-serif; ">
                    <img class="cat-image lazyload mr-2 opacity-60"
                         src="{{ static_asset('assets/img/placeholder.jpg') }}"
                         data-src="{{ uploaded_asset($category->icon) }}"
                         width="16"
                         alt="{{ $category->getTranslation('name') }}"
                         onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    <span class="cat-name has-transition">{{ $category->getTranslation('name') }}</span>
                </a>
                @if(count(\App\Utility\CategoryUtility::get_immediate_children_ids($category->id))>0)
                    <div class="sub-cat-menu c-scrollbar-light border p-4 shadow-none">
                        <div class="c-preloader text-center absolute-center">
                            <i class="las la-spinner la-spin la-3x opacity-70"></i>
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
