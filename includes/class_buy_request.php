<?php

	class buy_request{
		public 		$ir_req_id,
				$eamil_id,
				$ir_date_time,
				$ir_req_descr,
				$ir_searched; 

		function get_req_from_db($row){
			$this->ir_req_id	=	$row['ir_req_id'];
			$this->email 		=	$row['email_id'];
			$this->ir_req_descr 	= 	$row['ir_req_descr'];
		}

		function get_req_by_id($id){
			$this->ir_req_id = $id;

			$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
				or die('connect db fail');

			$query = sprintf(get_sql_clause('get_req_by_id'), $id);
			
			$data = mysqli_query($dbc, $query)
				or die('can\'t build req');

			$row = mysqli_fetch_array($data);

			$this->get_req_from_db($row);

			mysqli_close($dbc);
		}

		function search(){
			//connect database
			$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
				or die('connect db fail');
 			
 			$query = sprintf(get_sql_clause('get_srch_source'),'shopping');
			$data = mysqli_query($dbc, $query)
				or die('fail to get srch source');

			while ($row = mysqli_fetch_array($data)){
				$srch_sources[] = new srch_source($row);
			}

			foreach ($srch_sources as $source) {
				$flag = false;
				$items = array();//清空数组
				if (!empty($source->search_box_url) && $source->search($this->ir_req_descr)){//curl success
					$item_infos = $source->get_item();//if patter match fail, will send email in this

					if (sizeof($item_infos) > 0){//have srlt
						$flag = true;
						println('get '.sizeof($item_infos).' item');
						$i = 0;
						foreach ($item_infos as $item_info){//遍历rslt
							println('item url:'.$item_info['url']);//...........
							$items[] = new shopping_item($this->ir_req_id, $source->ir_web_id, $i+1, 'Y', $item_info['title'], $item_info['url'], $source->srch_pattern2);//php array[]=new xx() will follow the back creata a new class
							$items[$i]->get_dtl_from_url();
							$items[$i]->store();//...............................
							$i++;
						}
					}
				}
				if (false == $flag){
					$items[] = new shopping_item($this->ir_req_id, $source->ir_web_id, 0, 'N');
					$items[0]->store();
				}
			}
			$query = "UPDATE ir_work_buy_request SET ir_searched = 'Y' WHERE ir_req_id = $this->ir_req_id";
			mysqli_query($dbc, $query)
				or die('fail to update ir_searched');
			mysqli_close($dbc);
		}
	}

?>