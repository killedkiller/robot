<?php
// +----------------------------------------------------------------------
// | PHP Version :5.3.10 
// +----------------------------------------------------------------------
// | Copyright (c) 2014 Inforobot Inc,all rights reserved.
// +----------------------------------------------------------------------
// | Author:J_Yin  <yjm_95@163.com>
// +----------------------------------------------------------------------

/**
 * find the answer for question
 * from shopping information collected by shopping work robot
 */
	//initialize action result, this is a global variable
	session_start();
	require('ir_buy_work.php');
	//error_reporting(0);
	$request_answer	;
	//fetch datebase connection variables
	require_once('connectvars.php');
	
	
	$description 		=	"";//搜索商品的名称 
	

	
	$request_array	 	=	search_from_request($description);

	$id 				=	$request_array[0]['request_id'];
	
	call_robot($id);
	



	/**
     * get the sql sentence by $func_name
     * @param string  $func_name  the condition to find sentence
     * @return string the sql sentence it get
     * @author J_Yin <yjm_95@163.com> 
	 */
	function get_sql_clause($func_name){   

		//connect database
		$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
			or die('fail');

		//get sql clause
		$query = "SELECT * FROM ir_sql_tbl WHERE ir_func_name = '$func_name'";
		$data = mysqli_query($dbc, $query)
			or die('fail');
		$row = mysqli_fetch_array($data);

		mysqli_close($dbc);

		//return sql clause as a string
		return $row['ir_sql_clause'];
	}
   /**
	* judge if call the robot to work
	* @param 	int 	  $id				the ir_req_id
	* @return 	array     $request_answer	the array of result
	*/
	function call_robot($id){
		//connect database
		$dbc   =  mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
			or die('fail12');
		//get the detail of this request
		 
		$query  = 	sprintf(get_sql_clause('get_one_request'), $id);
		
		$data   =   mysqli_query($dbc, $query)
				or die('fail13');

		$row 	= 	mysqli_fetch_array($data);
		
		//if the request have been searched
		$if_searched	=	 $row['ir_searched'];
		//if not ,call robot to search 
		if($if_searched 	==	 "N"){
			 $a =new buy_request();
			 $a->get_req_by_id($id);
			 
			 $a->search();
			 
		}
		mysqli_close($dbc);
	}


  
?>		