<?php 

//realm=&uname=&pword=&db=&apptoken=&thours=&rid=&fname=&lname&

$realm = $_POST["realm"];
$uname = $_POST["uname"];
$pword = $_POST["pword"];
$db_id = $_POST["db"];
$app_token = $_POST["apptoken"];
$thours = $_POST["thours"];

if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST["record_id"]) ) {

	$record_id = $_POST["record_id"];

	$data_list = array(
		'pswd' => "V6vZoZuMDE8ti5yU",
		'stamp' => date("Y-m-d H:i:s"),

		// 'fname' => "Test1",
		// 'lname' => "Test2",
		// 'email' => "t@test3.com",
		// 'addr' => "2006 Lonely Oak Drive",
		// 'addr2' => "",
		// 'city' => "Mobile",
		// 'state' => "AL",
		// 'zip' => "36602",
		// 'dob' => "1990-12-16",
		// 'gender' => "M",
		// 'landline' => "3334464556",	

		'fname' => $_GET["fname"],
		'lname' => $_GET["lname"],
		'email' => $_GET["email"],
		'addr' => $_GET["addr"],
		'addr2' => $_GET["addr2"],
		'city' => $_GET["city"],
		'state' => $_GET["state"],
		'zip' => $_GET["zip"],
		'dob' => date("Y-m-d", strtotime($_GET["dob"])),
		'gender' => $_GET["gender"],
		'landline' => $_GET["landline"],
		'cellphone' => $_GET["cellphone"],
	);

	$data_string = http_build_query($data_list);
	
	$url = "https://www.qmleads.com/live/q_an_incomingcalls/livefeed.php";

	$curl = curl_init();
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

	$post_data = array(
		"rid" =>$record_id,
		"_fid_16" =>$resp,
		"update_id" =>$update_id,
	);

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