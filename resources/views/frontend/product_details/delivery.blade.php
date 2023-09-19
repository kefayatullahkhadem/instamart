
<div class="card" style="padding:10px;">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{--        <p style="font-size: 0.85rem;"><b>DELIVERY & RETURNS</b></p>--}}
    <!--<hr>-->
    <!--<img src="{{ static_asset('assets/img/svglogo.jpeg') }}" style="height:35px; padding-bottom:5px;">-->
    <!-- <p>Instamart Delivery on thousands of products in Srilanka</p><hr>-->
    <p style="font-size: 0.85rem;"><b>Choose your location</b></p>
    <form action="{{ route('cities.store') }}" method="POST">
        <div class="form-group mb-3">
            {{--            <label for="country">{{translate('State')}}</label>--}}
            <select class="select2 form-control" id="state-dd" name="state_id" data-toggle="select2" data-placeholder="Choose ..." data-live-search="true">
                <!--enter here-->
                @foreach ($states as $state)
                    <option value="{{ $state->id }}" @if($selected_state){{($selected_state->id==$state->id)?"selected":""}}  @endif>{{ $state->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            {{--            <label>{{ translate('City') }}</label>--}}
            <select id="city-dd" class="form-control">
                <option value="">Nothing to Selected</option>
            </select>
        </div>

        <div class="form-group mb-3">
            @if($detailedProduct->cash_on_delivery == 1)
                <div class ="card row" style="margin: 3px; background-color: #198754;">
                    <p style="font-size: 0.90rem; color: white; padding-top: 4%;  padding-left: 4%;"><img src="{{ static_asset('assets/img/cashondel.png') }}" style="height:30px;"><b> Cash On Delivery Available</b></p>
                </div><br>
            @endif
            <div class ="row">
                <div class ="col-2">
                    <img src="{{ static_asset('assets/icon/doordelivery.png') }}" style="height:35px; padding-bottom:5px;">
                </div>
                <div class ="col-10">
                    <p ><b style="font-size: 0.85rem;">Door Delivery</b><br>
                        Delivery <b>Rs </b><b id="door">0</b> <br>
                        Delivery by <b>@php echo date("jS F", strtotime("+3 days")); @endphp</b> when you order within next <b>7hrs 58mins</b>
                    </p>
                </div>
            </div>
            <div class ="row">
                <div class ="col-2">
                    <img src="{{ static_asset('assets/icon/pickup.png') }}" style="height:35px; padding-bottom:5px;">
                </div>
                <div class ="col-10">
                    <p ><b style="font-size: 0.85rem;">Pickup Station</b><br>
                        Delivery <b>Rs</b> <b id="pickup">0</b> <br>
                        Available for pick up from <b>@php echo date("jS F", strtotime("+3 days")); @endphp</b> when you order within next <b>7hrs 58mins</b>
                    </p>
                </div>
            </div>
            @php
                $idx = $detailedProduct->id;
                $warranty = DB::table('product_warrantys')
                 ->where('product_id', $idx)
                 ->first();
            @endphp
            @php if(isset($warranty->warranty_type) && $warranty->warranty_type){ @endphp
            <hr>
            <div class ="row">
                <div class ="col-2">
                    <img src="{{ static_asset('assets/icon/warranty.png') }}" style="height:30px; padding-bottom:5px;">
                </div>
                <div class ="col-10">
                    <p ><b style="font-size: 0.9rem;">{{$warranty->warranty_period}} {{$warranty->warranty_type}}</b>
                    </p>
                </div>
            </div>
            @php }else{ }@endphp

        </div>

    </form>
</div>


<script>

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    $('#state-dd').change(function(){
        var option = $(this).val();
        $.ajax({
            url: "{{url('api/get-cityx')}}",
            type: 'get',
            data: {
                option: option,
            },
            success: function(response){
                var len = response.length;
                $('#city-dd').empty();
                for(var i=0; i<len; i++){
                    var id = response[i].id;
                    var name = response[i].name;
                    $('#city-dd').append("<option value='"+id+"'>"+name+"</option>");
                }
            }
        });
    });

    $('#city-dd').on('change', function () {
        var idState1 = this.value;
        $("#city-cost").val('');
        $.ajax({
            url: "{{url('api/fetch-city-cost')}}",
            type: "get",
            data: {
                _token: CSRF_TOKEN,
                city_id: idState1,
                user_id:'{{\Auth::id();}}',
            },
            dataType: 'json',
            success: function (res) {
                $("#city-cost").val(res.city.cost);
                var door = document.getElementById("door");
                var pickup = document.getElementById("pickup");
                var doorcost =res.city.cost;
                var pickupcost =(res.city.cost * 50)/100;
                door.innerHTML = doorcost;
                door.style.backgroundColor = "red";
                door.style.color = "white";
                door.style.padding = "2%";
                pickup.innerHTML = pickupcost;
                pickup.style.backgroundColor = "red";
                pickup.style.color = "white";
                pickup.style.padding = "2%";

            }
        });
    });
    //    window.location.reload(true);
</script>
