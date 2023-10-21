<?php
$new_file = $cart_file_handling_name."/$cart_partner_name.php";
rename($temp_folder_name."ps_ippgateway", $cart_file_handling_name);
$class_name = str_replace(array(" ","_","!","æ","ø","å","'"),"",$partner_name);

rename("$cart_file_handling_name/paymentexample.php", $new_file);
$str=file_get_contents($new_file);
$str=str_replace("PaymentExample", "PAYMENT".$class_name,$str);
$str=str_replace("PAYMENTEXAMPLE", "PAYMENT".strtoupper($class_name),$str);
$str=str_replace("Display Payment Example", "Visa or MasterCard from ".$partner_name,$str);
$str=str_replace("module:paymentexample", "module:$cart_file_handling_name",$str);

file_put_contents($new_file, $str);
