<?php
	
	class shopping_item{
		//
		public 	$ir_req_id,//
				$ir_web_id,//
				$ir_sequence,//
				$ir_item_title,//
				$ir_item_dtl,
				$ir_shipping_cost,
				$addl_fee,//
				$ir_original_url,//
				$ir_original_page,//
				$ir_date_time,//
				$ir_reg_price,
				$ir_sale_price,
				$returned_links;//

		public $pattern;
				
		//
		function __construct($req, $web, $seq, $returned_links, $title = '', $url = '', $pattern = ''){
			$this->ir_req_id		=	$req;
			$this->ir_web_id		=	$web;
			$this->ir_sequence		=	$seq;
			$this->returned_links	=	$returned_links;
			$this->ir_item_title	=	$title;
			$this->ir_original_url	=	$url;
			$this->pattern 			=	$pattern;
		}

		//
		function get_dtl_from_db(){

		}

		//
		function get_dtl_from_url(){
			println('getting item dtl');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, '');
			curl_setopt($ch, CURLOPT_URL, $this->ir_original_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Price Watch');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


			if ($this->ir_original_page = curl_exec($ch)){
				if (preg_match_all($this->pattern, $this->ir_original_page, $item_info)){
					println('pattern match');
					$this->ir_reg_price = (float) str_replace(",", "", $item_info['listprice'][0]);
					$this->ir_sale_price = (float) str_replace(",", "", $item_info['price'][0]);
					if (isset($item['free'])){
						if (empty($item['free'][0])){
							$this->ir_shipping_cost = (float) str_replace(",", "", $item_info['shipping'][0]);
						}else{
							$this->ir_shipping_cost = 0;
						}
					}
					if (isset($item_info['dtl'])){
						if ($this->ir_web_id == 'amazon-eng'){
							$item_info['dtl'][0] = urldecode($item_info['dtl'][0]);
							
							preg_match_all('/\<\/h2\>(.*?)\<\/body\>/s', $item_info['dtl'][0], $amazon_descr);
							if (isset($amazon_descr[1][0])){
								$item_info['dtl'][0] = $amazon_descr[1][0];
							}
							
						}
						$dtl = preg_replace('/\<script\>(.*?)\<\/script\>/s', ' ', $item_info['dtl'][0]);
						$this->ir_item_dtl = preg_replace('/\<.*?\>/', ' ', $dtl);
					}
				}else{
					println('pattern not match');
				}
			}else{
				println('curl fail');
			}
			$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
			println($http_code);

			
		}

		//
		function store(){
			$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
				or die('connect db fail');

			$query = sprintf(get_sql_clause('store_item'), $this->ir_req_id, $this->ir_web_id, $this->ir_sequence, $this->ir_item_title, mysqli_real_escape_string($dbc, $this->ir_item_dtl), $this->ir_shipping_cost, $this->addl_fee, $this->ir_original_url, mysqli_real_escape_string($dbc, $this->ir_original_page), $this->ir_reg_price, $this->ir_sale_price, $this->returned_links);
			
			mysqli_query($dbc, $query)
				or println('fail to store item');

			mysqli_close($dbc);
		}
	}

?>