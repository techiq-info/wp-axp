<?php
require_once("../../../../wp-load.php");

   if(isset($_FILES['file'])){
      $errors= array();
      $file_name = $_FILES['file']['name'];
      $file_size =$_FILES['file']['size'];
      $file_tmp =$_FILES['file']['tmp_name'];
      $file_type=$_FILES['file']['type'];
      $var_dd = explode('.',$_FILES['file']['name']);
      $var_dd = end($var_dd);
      $file_ext=strtolower($var_dd);
      $fieldArr = false;
      if(get_option('wc_fields_account')){
         $fieldArr = get_option('wc_fields_account');
      }
      elseif(get_option('wc_fields_billing')){
         $fieldArr = get_option('wc_fields_billing');
      }
      if(is_array($fieldArr)){

      foreach ($fieldArr as $key => $value){
         if($value['type'] == 'file'){
			 
			if(empty($value['maxfile']) || !isset($value['maxfile'])){
				$maxsize =  5 * 1000000;
			} else{
				$maxsize =  $value['maxfile'] * 1000000;
			}
            
	

      $extensionsArr = array();
      $extensions= $value['extoptions'];
      foreach ($value['extoptions'] as $value) {
         $extensionsArr[] = $value; 
      }
    
         if(in_array($file_ext,$extensionsArr)=== false){
         $errors[]="extension not allowed, please choose a ".implode(",", $extensionsArr)." file.";
      }
      
      if($file_size > $maxsize){
         $errors[]='File size must be excately '.$maxsize.' MB';
      }
      
      if(empty($errors)==true){
         session_start();
         move_uploaded_file($file_tmp,"uploads/".$file_name);
         $_SESSION['order_file'] = plugins_url( 'uploads/'.$file_name, __FILE__ );
         echo "File Uploaded";
      }else{
         print_r($errors);
      }
   }

}

   }
}
?>