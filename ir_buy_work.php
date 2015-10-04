<?php

	require_once('connectvars.php');
	require_once('includes/class_srch_source.php');
	require_once('includes/class_buy_request.php');
	require_once('includes/class_shopping_item.php');

	function println($str){
		//echo $str."\n";
	}

	//this function will get sql clause by name
	function get_sql_clause($func_name){

		//connect database
		$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
			or die('connect fail');

		//get sql clause
		$query = "SELECT * FROM ir_sql_tbl WHERE ir_func_name = '$func_name'";
		$data = mysqli_query($dbc, $query)
			or die('get sql fail');
		$row = mysqli_fetch_array($data);

		mysqli_close($dbc);

		//return sql clause as a string
		return $row['ir_sql_clause'];
	}

	function auto_srch(){

		//connect database
		$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
			or die('connect db fail');

		$query = sprintf(get_sql_clause('get_buy_request'));
		$data = mysqli_query($dbc,$query)
			or die ('fail to get buy request');

		while ($row = mysqli_fetch_array($data)){
			$req = new buy_request();
			$req->get_req_from_db($row);
			$req->search();
		}

		mysqli_close($dbc);
	}



	function test1(){
		$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
			or die('connect db fail');
		$query = sprintf(get_sql_clause('get_srch_source'),'shopping');
		$data = mysqli_query($dbc,$query)
			or die ('fail to get buy request');

		while ($row = mysqli_fetch_array($data)){
			if ( $row['rank'] == 4) {
				$shp = new srch_source($row);
				$shp->search('kindle');
				$info = $shp->get_item();
				print_r($info);
			}
			
		}

		mysqli_close($dbc);
	}

	function test2(){
		$p ='/\<table class\="a\-lineitem"\>(?:.*\<td class\="a\-span12 a\-color\-secondary a\-size\-base a\-text\-strike"\>\$(?<listprice>.*?)\<\/td\>)?.*?\<tr\>.*?\<span id\="priceblock_(?:our|sale)price" class\="a\-size\-medium a\-color\-price"\>\$(?<price>.*?)\<\/span\>(?:.*?(?:(?:\<span class\="a\-size\-base a\-color\-secondary"\>.*?\+\s\$(?<shipping>.*?)\sshipping.*?\<\/span\>)|(?<free>\<b\>FREE\sShipping\<\/b\>)))?.*?\<\/td\>.*?var\siframeContent\s\=\s"(?<dtl>.*?)";/s';

		$u = 'http://www.amazon.com/Apple-iPhone-16GB-White-Verizon/dp/B004ZLYBQ4/ref=sr_1_1/188-5444621-3934134?ie=UTF8&amp;qid=1419063224&amp;sr=8-1&amp;keywords=iphone';
		$item = new shopping_item(15, '...', 15, 'Y', 'title', $u, $p);
		$item->get_dtl_from_url();
		$item->store();
	}
	
	//test1();
	//test2();
	auto_srch();


?>