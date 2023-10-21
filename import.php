<?php
$new_file = $cart_file_handling_name."/$cart_partner_name.php";
rename($temp_folder_name."ps_ippgateway", $cart_file_handling_name);


rename("$cart_file_handling_name/paymentexample.php", $new_file);
$str=file_get_contents($new_file);
$str=str_replace("PaymentExample", "PAYMENT".$partner_name,$str);
$str=str_replace("PAYMENTEXAMPLE", "PAYMENT".strtoupper($partner_name),$str);
$str=str_replace("Display Payment Example", "Visa or MasterCard from ".$partner_name,$str);
$str=str_replace("module:paymentexample", "module:$cart_file_handling_name",$str);

file_put_contents($new_file, $str);
