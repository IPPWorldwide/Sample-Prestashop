<?php
$new_file = $cart_file_handling_name."/$cart_partner_name.php";
rename($temp_folder_name."ps_ippgateway", $cart_file_handling_name);
$class_name = str_replace(array(" ","_","!","æ","ø","å","'"),"",$partner_name);

rename("$cart_file_handling_name/paymentexample.php", $new_file);
$str=file_get_contents($new_file);
$str=str_replace("PaymentExample", strtolower($class_name),$str);
$str=str_replace("PAYMENTEXAMPLE", strtoupper($class_name),$str);
$str=str_replace("Display Payment Example", "Visa or MasterCard from ".$partner_name,$str);
$str=str_replace("module:paymentexample", "module:$cart_file_handling_name",$str);
file_put_contents($new_file, $str);

$str=file_get_contents($cart_file_handling_name."/config.xml");
$str=str_replace("paymentexample", strtolower($class_name),$str);
$str=str_replace("<![CDATA[Payment Example]]>", "Visa or MasterCard from ".$partner_name,$str);
$str=str_replace("<![CDATA[Description of Payment Example]]>", "Handle payments with VISA or MasterCard",$str);
file_put_contents($new_file, $str);
