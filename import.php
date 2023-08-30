<?php
$new_file = $cart_file_handling_name."/$cart_partner_name.php";
rename($temp_folder_name."ps_ippgateway", $cart_file_handling_name);
rename("$cart_file_handling_name/ps_ippgateway.php", $new_file);
$str=file_get_contents($new_file);
$str=str_replace("{{PlaceHolder-PartnerName}}", $partner_name,$str);
$str=str_replace("{{PlaceHolder-PartnerUrl}}", $partner_url,$str);
$str=str_replace("{{PlaceHolder-PartnerFolder}}", $cart_partner_name,$str);
file_put_contents($new_file, $str);
