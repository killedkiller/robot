<?php



	class srch_source{
		//
		public 	$ir_web_id,
				$source_url,
				$search_box_url,
				$language,
				$rank,
				$srch_pattern1,
				$srch_pattern2,
				$srch_pattern3,
				$category,
				$partner,
				$country,
				$public;

		//
		public 	$descr,
				$srch_rslt_page;

		//
		function __construct($row){
			$this->ir_web_id		=	$row['ir_web_id'];
			$this->source_url		=	$row['source_url'];
			$this->search_box_url	=	$row['search_box_url'];
			$this->srch_pattern1	=	$row['srch_pattern1'];	
			$this->srch_pattern2	=	$row['srch_pattern2'];
			$this->srch_pattern3	=	$row['srch_pattern3'];
		}

		//
		public function search($descr){
			$this->descr = $descr;
			
			switch ($this->ir_web_id) {
				case 'amazon-eng':
					$descr = preg_replace('/\s/', '%20', $descr);
					break;
				
				case 'ebay-eng':
					$descr = preg_replace('/\s/', '+', $descr);
					break;

				case 'overstock-eng':
					$descr = preg_replace('/\s/', '+', $descr);
					break;

				case 'shopping-eng':
					$descr = preg_replace('/\s/', '%20', $descr);
					break;
			}
			
			$srch_url = $this->search_box_url;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, '');
			$srch_url = preg_replace('/\{srch_str\}/', $descr, $srch_url);
			println('search url:'.$srch_url);
			curl_setopt($ch, CURLOPT_URL, $srch_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Price Watch');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$this->srch_rslt_page = curl_exec($ch);

			$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
			println($http_code);
			if  ($http_code == '200'){
				println('search success');
				return true;
				// $web = fopen("web.txt", "w") or die("Unable to open file!");
				// fwrite($web, $this->srch_rslt_page);
				// fclose($web);
			}else{
				println('search fail, http code '.$http_code);
				return false;
			}

			curl_close($ch);
		}

		//
		public function get_item(){ 
			$items = array();

			preg_match_all($this->srch_pattern1, $this->srch_rslt_page, $matches);
			if (empty($matches['notfoundflag'][0])){
				println('get item success');

				println($this->descr);
				for ($i = 0; $i < sizeof($matches[0]); $i++){

					$keyword_in_title = true;
					$keyword_explode = explode(' ', $this->descr);
					foreach ($keyword_explode as $k_explode){
						if ( !stripos(' '.$matches['title'][$i], $k_explode) ){
							$keyword_in_title = false;
						}
					}
					//preg_match_all return a two dimension array     xx(a)xx(b)xx(c) matches[0]  match the whole [1]match (a)pattern...    [0][0]the first to match the whole pattern

					if (true == $keyword_in_title){ 

						if ($this->ir_web_id == 'shopping-eng'){
							$items[] = array('title' => $matches['title'][$i], 'url' => 'http://shopping.com'.$matches['url'][$i]);
							//$items[$i]['url'] = 'http://shopping.com/'.$items[$i]['url'];
						}else{
							$items[] = array('title' => $matches['title'][$i], 'url' => $matches['url'][$i]);
						}
						if(sizeof($items) == 5) 
							break;
					}
				}

			}else{
				println('get item fail');
			}
			
			return $items;
		}

	}

?>