<?php 

$realm = $_POST["realm"];
$uname = $_POST["uname"];
$pword = $_POST["pword"];
$db_id = $_POST["db"];
$app_token = $_POST["apptoken"];
$thours = $_POST["thours"];

if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST["record_id"]) ) {

	$record_id = $_POST["record_id"];

	$data_list = array();
	foreach ($_POST as $key => $value) {
	    if (strpos($key, "qb_send_") === 0) {
	         $data_list[str_replace("qb_send_", "", $key)] = $value;
	    }
	}	

	$data_string = http_build_query($data_list);
	
	$url = $_POST["set_url"];

	$curl = curl_init();
	if ($_POST["request_method"] =="get") {

		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => $url."?".$data_string,
		    CURLOPT_USERAGENT => 'API Request'
		));

	} elseif ($_POST["request_method"] =="post") {

		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => $url,
		    CURLOPT_USERAGENT => 'API Request',
		    CURLOPT_HTTPHEADER => array(
		    	//'Content-Type: application/xml'
		    ),
		    CURLOPT_POST => 1,
		    CURLOPT_POSTFIELDS => $data_string
		));

	}
	
	$resp = curl_exec($curl);
	curl_close($curl);

	/*======================
	QuickBase API
	======================================*/
	// Start connection
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_URL => "https://".$realm.".quickbase.com/db/main?a=API_Authenticate&username=".$uname."&password=".$pword."&hours=".$thours."",
	    CURLOPT_USERAGENT => 'QB Ticket Request'
	));
	$resp1 = curl_exec($curl);
	curl_close($curl);

	$xml = new SimpleXMLElement($resp1);
	$ticket = $xml->ticket;
	$user_id = $xml->userid;

	// Start connection
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_URL => "https://".$realm.".quickbase.com/db/".$db_id."?a=API_GetRecordInfo&rid=".$record_id."&ticket=".$ticket."&apptoken=".$app_token."",
	    CURLOPT_USERAGENT => 'QB Record Request'
	));
	$resp2 = curl_exec($curl);
	curl_close($curl);

	$xml = new SimpleXMLElement($resp2);
	$update_id = $xml->update_id;

	$field_options = array(
		"raw_response" =>$resp,
	);

	$post_data = array(
		"rid" =>$record_id,
		"update_id" =>$update_id,
	);
	foreach ($_POST as $key => $value) {
	    if (strpos($key, "_fid_") === 0 || strpos($key, "_fnm_") === 0 ) {
	         $post_data[$key] = $field_options[$value];
	    }
	}	

	$post_string = http_build_query($post_data);

	$url = "https://".$realm.".quickbase.com/db/".$db_id."?a=API_EditRecord&".$post_string."&ticket=".$ticket."&apptoken=".$app_token."";

	// Start connection to QuickBase
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_URL => $url,
	    CURLOPT_USERAGENT => 'QB API Request'
	));
	$resp = curl_exec($curl);
	curl_close($curl);

}