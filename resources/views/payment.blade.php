<?php


//checkout URL


//custom fields
//cus_1|cus_2|cus_3|cus_4
//$custom_fields = base64_encode('custom_variable_01|custom_variable_02|custom_variable_03|custom_variable_04');

?>

        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Pavan Welihinda">
    <title></title>
</head>
<body>

<h1>Please Wait You Will Be Redirected to Payment Gateway..</h1>
<form action="<?php echo $postdata['url']; ?>" method="POST">

    <input type="hidden" name="first_name" value="{{$postdata['first_name']}}"><br>
    <input type="hidden" name="last_name" value="{{$postdata['last_name']}}"><br>

    <input type="hidden" name="email" value="{{$postdata['email']}}"><br>
    <input type="hidden" name="contact_number" value="{{$postdata['contact_number']}}"><br>

    <input type="hidden" name="address_line_one" value="Colombo"><br>


    <input type="hidden" name="process_currency" value="LKR"><br>
    <input type="hidden" name="payment_gateway_id" value=""><br>
    <input type="hidden" name="bankMID" value="TESTWEBXTOKMSUSD"><br>
    <input type="hidden" name="custom_fields" value="{{$postdata['custom_fields']}}">
    <input type="hidden" name="enc_method" value="JCs3J+6oSz4V0LgE0zi/Bg==">
    <input type="hidden" name="multiple_payment_gateway_ids" value="2|3|4|5|35|96" placeholder="| seperated ids" >
    <br/>
    <!-- POST parameters -->
    <input type="hidden" name="secret_key" value="{{$postdata['secret_key']}}" >
    <input type="hidden" name="payment" value="{{$postdata['payment']}}" >
    <input id="submit_btn" type="submit" value="Pay Now" style="display: none" >
</form>

</body>
</html>

<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>

<script>
    $(document).ready(function(){
        $("#submit_btn").trigger("click");
    });
</script>
