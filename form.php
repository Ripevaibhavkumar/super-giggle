<?php 
include(ABSPATH . 'wp-config.php');
global $wpdb;
global $current_user;
$personalics =  'personalics';
$flag = 0;
$api_get= $wpdb->get_results("Select * from $personalics where id = '1'");
 if(!empty($api_get)){ }
 if($_REQUEST['submit_api']){
	 
		$ch = curl_init();
		$headers = array(
		'Content-Type: application/vnd.api+json',
		'Authorization: Bearer '.$_REQUEST['api_key'],
		);
		curl_setopt($ch, CURLOPT_URL,"https://api.personalics.com/v1/account/");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$authToken = curl_exec($ch);
		$res = preg_replace('~\{(?:[^{}]|(?R))*\}~', '', $authToken);
		$json = str_replace($res, "", $authToken);
		$json1 = json_decode($json, true);
		//echo "<pre>"; print_r($json1); echo "</pre>"; 
		$error = $json1['errors'];
		$siteid = $json1['data']['attributes']['siteId'];
		$site_domain = $json1['data']['attributes']['domain'];
		$track_url = "//trk.personalics.com/piwik.php"; 
   //echo "<pre>"; print_r($error); echo "</pre>";
   if(!$error){
	 $one_id =  $wpdb->get_results("Select * from $personalics where id = '1'");
	 $api = $_REQUEST['api_key'];
		if(!empty($one_id)){
		$sql ="UPDATE `personalics` SET `api_key`='".$api."',`siteid`='".$siteid."',`sitedomain`='".$site_domain."',`trackurl`='".$track_url."' WHERE id = 1";
		}
		else{
		$sql ="Insert into `personalics` ('api_key', 'id', 'siteid','sitedomain','trackurl') values('".$api."', '1','".$siteid."','".$site_domain."','".$track_url."')";
		}
   }
   else{
		$sql ="UPDATE `personalics` SET `api_key`='".$api."',`siteid`='".$siteid."',`sitedomain`='".$site_domain."',`trackurl`='".$track_url."' WHERE id = 1";
		echo "<p style='color: red' align='center'>";
	      echo 'API Key is not authenticating. Please try again with correct API Key.';
		echo "</p>";
   }
   $rez = $wpdb->query($sql);
	if($rez){ $flag = 1;}
}
	$flag;
	 if($flag==1){
		 $api_get= $wpdb->get_results("Select * from $personalics where id = '1'");
	 }
	 else{
		 $api_get= $wpdb->get_results("Select * from $personalics where id = '1'");
	 }
 
?>

<form action="" id="personalic_form" class="personalic_form" method="post">
<div align="center">
 Enter API Key <textarea class="api_key" name="api_key" style="width:500px;margin: 30px 0 10px;" required><?php  foreach ($api_get as $aaa)
 {
 echo $aaa->api_key; } ?></textarea></br>
 
 Enter Site ID <input type="text" class="site_id" value="<?php  foreach ($api_get as $aaa)
 {
 echo $aaa->siteid; } ?>" name="site_id" style="width:500px;margin: 30px 0 10px;"></input></br>
 
 Enter Domain <input type="text" class="domain" value="<?php  foreach ($api_get as $aaa)
 {
 echo $aaa->sitedomain; } ?>" name="domain" style="width:500px;margin: 30px 0 10px;"></input></br>
 
 Enter Track URL <input type="text" class="url_track" value="<?php  foreach ($api_get as $aaa)
 {
 echo $aaa->trackurl; } ?>" name="url_track" style="width:500px;margin: 30px 0 10px;"></input></br>
 
<input type="submit" name="submit_api" value="Submit"></input>
</div>
</form>
