<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_controller extends CI_Controller {
	function __construct(){
		parent::__construct();
	}
	
	/*
		>> Error log should be added prefix Error:
		>> status
		1=Pending | 2=Approved | 3=Resolved | 4=Forwarded  | 5=Deployed  | 6=New  | 7=Active  | 
		8=Initiated  | 9=On Progress  | 10=Delivered  | -2=Declined | -3=Canceled | 
		-5=Taking out | -6=Renewed/Replaced | -7=Inactive
	*/
	function report_purchase_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_available_laptop');
		$this->webspice->permission_verify('report_available_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.CREATED_DATE ';
    $groupby = null;
    $where = null;
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

		$initialSQL = " SELECT TBL_STOCK.* FROM TBL_STOCK ";

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    	
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				
				$this->load->view('report/print_report_purchase_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    $_SESSION['sql'] = $sql;
    $result = $this->db->query($sql)->result();
		$data['get_record'] = $result;
		$this->load->view('report/report_purchase_laptop', $data);
	}
	
	function report_available_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_available_laptop');
		$this->webspice->permission_verify('report_available_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.CREATED_DATE ';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 11';
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

		$initialSQL = " SELECT TBL_STOCK.* FROM TBL_STOCK ";

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    	
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				
				$this->load->view('report/print_report_available_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    $_SESSION['sql'] = $sql;
    $result = $this->db->query($sql)->result();
		$data['get_record'] = $result;
		$this->load->view('report/report_available_laptop', $data);
	}	
	
	function report_assign_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_assign_laptop');
		$this->webspice->permission_verify('report_assign_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.UPDATED_DATE';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 12';
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

		$initialSQL = "
			SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
			TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
			TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE
			FROM TBL_STOCK 
			INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID 
		";

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				$this->load->view('report/print_report_assign_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    
    $_SESSION['sql'] = $sql;
    $result = $this->db->query($sql)->result();
		$data['get_record'] = $result;
		$this->load->view('report/report_assign_laptop', $data);
	}
	
	function report_lost_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_lost_laptop');
		$this->webspice->permission_verify('report_lost_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.STOCK_ID ASC ';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 14 ';
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

	$initialSQL = "
		SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
		TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
		TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE 
		FROM TBL_STOCK 
		INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID 
	";

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    	
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				
				$this->load->view('report/print_report_lost_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    

    
    $_SESSION['sql'] = $sql;
    
    $result = $this->db->query($sql)->result();
  	
		$data['get_record'] = $result;
		

		$this->load->view('report/report_lost_laptop', $data);
	}
	# call confirmation for redirect another url with message
	
	function report_broken_fault_laptop(){

		#$this->db->query("TRUNCATE TABLE TBL_STOCK");
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_broken_fault_laptop');
		$this->webspice->permission_verify('report_broken_fault_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.STOCK_ID ASC ';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 13 ';
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

		$initialSQL = "
		SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
		TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
		TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE 
		FROM TBL_STOCK 
		INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID 
		";

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    	
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				
				$this->load->view('report/print_report_broken_fault_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    

    
    $_SESSION['sql'] = $sql;
    
    $result = $this->db->query($sql)->result();
  	
		$data['get_record'] = $result;
		

		$this->load->view('report/report_broken_fault_laptop', $data);
	}
	
	function report_noc_with_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_noc_with_laptop');
		$this->webspice->permission_verify('report_noc_with_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.STOCK_ID ASC';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 15';
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

	$initialSQL = "
		SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
		TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
		TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE 
		FROM TBL_STOCK 
		INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID 
	";

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    	
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				
				$this->load->view('report/print_report_noc_with_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    

    
    $_SESSION['sql'] = $sql;
    
    $result = $this->db->query($sql)->result();
		$data['get_record'] = $result;
		$this->load->view('report/report_noc_with_laptop', $data);
	}
	
	function report_noc_without_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_noc_with_laptop');
		$this->webspice->permission_verify('report_noc_with_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_DISTRIBUTION.CREATED_DATE ';
    $groupby = null;
    $where = ' WHERE TBL_DISTRIBUTION.STATUS = 16 AND ROWNUM <= 50';
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

	$initialSQL = "
		SELECT TBL_DISTRIBUTION.*, 
		TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
		TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
		TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE, TBL_STOCK.MODEL, TBL_STOCK.LAPTOP_SR_NO, TBL_STOCK.BRAND, TBL_STOCK.EOL_DATE, TBL_STOCK.PO_NO, TBL_STOCK.PURCHASE_DATE
		FROM TBL_DISTRIBUTION
		INNER JOIN TBL_LAPTOP_USER ON TBL_DISTRIBUTION.USER_ID = TBL_LAPTOP_USER.USER_ID
		INNER JOIN TBL_STOCK ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
	";

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    	
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				
				$this->load->view('report/print_report_noc_without_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    $_SESSION['sql'] = $sql;
	
    $result = $this->db->query($sql)->result();
		$data['get_record'] = $result;
		$this->load->view('report/report_noc_without_laptop', $data);
	}
	
	function distribution_report(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_assign_laptop');
		$this->webspice->permission_verify('assign_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_DISTRIBUTION.CREATED_DATE ';
    $groupby = null;
    $where = null;
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

		$initialSQL = "
		SELECT TBL_DISTRIBUTION.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
		TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, 
		TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE, TBL_STOCK.LAPTOP_SR_NO, TBL_STOCK.MODEL, TBL_STOCK.BRAND, TBL_STOCK.PURCHASE_DATE,
		TBL_STOCK.EOL_DATE, TBL_STOCK.PO_NO
		FROM TBL_DISTRIBUTION
		INNER JOIN TBL_LAPTOP_USER ON TBL_DISTRIBUTION.USER_ID = TBL_LAPTOP_USER.USER_ID
		INNER JOIN TBL_STOCK ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
		";

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_DISTRIBUTION', 
			$InputField = array('TBL_LAPTOP_USER.USER_DIVISION', 'TBL_LAPTOP_USER.USER_DEPARTMENT', 'TBL_DISTRIBUTION.USER_DESIGNATION', 'TBL_LAPTOP_USER.USER_TYPE', 'TBL_DISTRIBUTION.STATUS'), 
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL','TBL_STOCK.PURCHASE_DATE','TBL_LAPTOP_USER.EMPLOYEE_ID','TBL_LAPTOP_USER.USER_DIVISION', 'TBL_LAPTOP_USER.USER_DEPARTMENT', 'TBL_DISTRIBUTION.USER_DESIGNATION', 'TBL_LAPTOP_USER.USER_TYPE','TBL_LAPTOP_USER.USER_NAME','TBL_LAPTOP_USER.USER_EMAIL'),
			$AdditionalWhere = null,
			$DateBetween = array('CREATED_DATE', 'date_from', 'date_end')
			);
			
			$limit = null;
			$result['where'] ? $where = $result['where'] : $where=$where;
			$result['filter'] ? $filter_by = $result['filter'] : $filter_by=$filter_by;
   	}

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    		
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				$data['filter_by'] = $_SESSION['filter_by'];
			
				$this->load->view('report/print_distribution_report',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    #dd($sql);
    $_SESSION['sql'] = $sql;
    $_SESSION['filter_by'] = $filter_by;
    $result = $this->db->query($sql)->result();
  	
		$data['get_record'] = $result;
		$data['filter_by'] = $filter_by;

		$this->load->view('report/distribution_report', $data);
	}
	
	function distribution_report__(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data = null;

		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'distribution_report');
		$this->webspice->permission_verify('distribution_report');

		$this->load->database();

		if( !$_POST ){
			$this->load->view('report/distribution_report', $data);
			return false;
		}

    # get input post
    $input = $this->webspice->get_input();

  	# calculate date range
    $data['start_date'] = $input->start_date;
    $data['end_date'] = $input->end_date;

    $data['start_year'] = date("Y", strtotime($data['start_date']));
    $data['start_month'] = date("m", strtotime($data['start_date']));
    $data['month_count'] = $this->webspice->calculate_months_between_two_dates($data['start_date'], $data['end_date']);
    if( $data['month_count'] < 1 ){
    	$this->webspice->message_board('Date range is invalid!');
    	$this->load->view('report/distribution_report', $data);
    	return false;
    }
	
    # get bundle sale
    $sql_distribution = "
		SELECT TBL_DISTRIBUTION.*, 
		TBL_LAPTOP_USER.USER_NAME, TBL_STOCK.MODEL, TBL_STOCK.LAPTOP_SR_NO, TBL_STOCK.BRAND, TBL_STOCK.EOL_DATE, TBL_STOCK.PO_NO, TBL_STOCK.PURCHASE_DATE
		FROM TBL_DISTRIBUTION
		INNER JOIN TBL_LAPTOP_USER ON TBL_DISTRIBUTION.USER_ID = TBL_LAPTOP_USER.USER_ID
		INNER JOIN TBL_STOCK ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
		WHERE TBL_DISTRIBUTION.STATUS = ? AND TBL_DISTRIBUTION.USER_DESIGNATION = ?
    ";
	
	$where = array();
	if(isset($input->STATUS) && $input->STATUS != ""){
		$where[] = $input->STATUS;
	}
	if(isset($input->USER_DESIGNATION) && $input->USER_DESIGNATION != ""){
		$where[] = $input->USER_DESIGNATION;
	}
	if(isset($input->USER_DEPARTMENT) && $input->USER_DEPARTMENT != ""){
		$where[] = $input->USER_DEPARTMENT;
	}
	
    $data['get_distribution'] = $this->db->query($sql_distribution, $where)->result();
    if( !$data['get_distribution'] ){
    	$this->webspice->message_board('There is no data found!');
    	$this->load->view('report/distribution_report', $data);
    	return false;
    }

		$data['filter_by'] = ' &raquo; Date Range: '.$data['start_date'].' to '.$data['end_date'];
		
		$data['action_type'] = 'view';
  	if( $this->input->post('print') ){
  		$data['action_type'] = 'print';
  	}elseif( $this->input->post('export') ){
  		$data['action_type'] = 'csv';
  	}
  	
		$value = $this->load->view('report/print_distribution_report', $data, true);

		echo $value;
	}
	
	function report_service_laptop(){
		#$this->db->query("TRUNCATE TABLE TBL_STOCK");
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_service_laptop');
		$this->webspice->permission_verify('report_service_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.STOCK_ID ASC ';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 17 ';
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

		$initialSQL = "
		SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
		TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
		TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE 
		FROM TBL_STOCK 
		INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID 
		";

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    	
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				
				$this->load->view('report/print_report_service_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    

    
    $_SESSION['sql'] = $sql;
    
    $result = $this->db->query($sql)->result();
  	
		$data['get_record'] = $result;
		$this->load->view('report/report_service_laptop', $data);
	}

	function stock_report(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'stock_report');
		$this->webspice->permission_verify('stock_report');

		$this->load->database();
    $orderby = null;
    $groupby = null;
    $where = ' WHERE STATUS = 11 ';
    $page_index = 0;
    $no_of_record = 50;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

		$initialSQL = "
		SELECT * FROM(
			SELECT TBL_SUB.*, ROWNUM RNUM
			FROM(
				SELECT TBL_STOCK.*
				FROM TBL_STOCK
				ORDER BY TBL_STOCK.STOCK_ID
			)TBL_SUB
		) TBL_RESULT
		";

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_RESULT', 
			$InputField = array('status'),
			$Keyword = array('LAPTOP_SR_NO','BRAND','MODEL','PURCHASE_DATE'),
			$AdditionalWhere = null,
			$DateBetween = array('PURCHASE_DATE', 'date_from', 'date_end')
			);
			
			# maximum 1000 row will show
			#$result['where'] ? $where = $result['where'].' AND RNUM < 1000' : $where=$where; 
			$result['where'] ? $where = $result['where'] : $where=$where; 
			$result['filter'] ? $filter_by = $result['filter'] : $filter_by=$filter_by;
   	}

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    		
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				$data['filter_by'] = $_SESSION['filter_by'];
			
				$this->load->view('report/print_stock_report',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;

		# only for pager
		if( $criteria == 'page' && !$this->input->post('filter') ){
			$where = ' WHERE RNUM BETWEEN '.($page_index+1). ' AND '.($page_index+$no_of_record). ' AND STATUS = 11';
			$sql = $initialSQL . $where . $groupby . $orderby . $limit;
		}

		# load all records
		if( !$this->input->post('filter') ){
			$count_data = $this->db->query( $initialSQL .$groupby );
			$count_data = $count_data->result();
			$data['pager'] = $this->webspice->pager( count($count_data), $no_of_record, $page_index, $url_prefix.'stock_report/page/', 10 );
		}

    $_SESSION['sql'] = $sql;
    $_SESSION['filter_by'] = $filter_by;
    $result = $this->db->query($sql)->result();
  	
		$data['get_record'] = $result;
		$data['filter_by'] = $filter_by;

		$this->load->view('report/stock_report', $data);
	}
		
	function po_details(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_purchase');
		$this->webspice->permission_verify('manage_purchase');

		$this->load->database();
    $orderby = 'GROUP BY TBL_STOCK.PO_NO, TBL_STOCK.MODEL, TBL_STOCK.BRAND, TBL_STOCK.PURCHASE_DATE';
    $groupby = null;
    $where = null;
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }
		$initialSQL = "
		SELECT TBL_STOCK.PO_NO, TBL_STOCK.MODEL, TBL_STOCK.BRAND, TBL_STOCK.PURCHASE_DATE AS GR_DATE, COUNT(TBL_STOCK.STOCK_ID)  AS STOCK FROM TBL_STOCK 
		";

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array(), 
			$Keyword = array('LAPTOP_SR_NO','BRAND','MODEL','PURCHASE_DATE','PO_NO'),
			$AdditionalWhere = null,
			$DateBetween = array('PURCHASE_DATE', 'date_from', 'date_end')
			);
			
			$limit = null;
			$result['where'] ? $where = $result['where'] : $where=$where;
			$result['filter'] ? $filter_by = $result['filter'] : $filter_by=$filter_by;
   	}

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    		
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				$data['filter_by'] = $_SESSION['filter_by'];
			
				$this->load->view('report/print_po_details',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;

    $_SESSION['sql'] = $sql;
    $_SESSION['filter_by'] = $filter_by;
    $result = $this->db->query($sql)->result();
  	
		$data['get_record'] = $result;
		$data['filter_by'] = $filter_by;

		$this->load->view('report/po_details', $data);
	}
		
	function report_eol_of_laptop(){
	#report_eol_of_available_laptop
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_eol_of_laptop');
		$this->webspice->permission_verify('report_eol_of_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.CREATED_DATE ';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 11 ';
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

		$initialSQL = "
		SELECT TBL_STOCK.* FROM TBL_STOCK
		";

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array('status'),
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL'),
			$AdditionalWhere = null,
			$DateBetween = array('EOL_DATE', 'date_from', 'date_end')
			);
			
			$limit = null;
			$result['where'] ? $where = $result['where'] : $where=$where;
			$result['filter'] ? $filter_by = $result['filter'] : $filter_by=$filter_by;
   	}

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    		
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				$data['filter_by'] = $_SESSION['filter_by'];
			
				$this->load->view('report/print_report_eol_of_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    #dd($sql);
    $_SESSION['sql'] = $sql;
    $_SESSION['filter_by'] = $filter_by;
    $result = $this->db->query($sql)->result();
  	
		$data['get_record'] = $result;
		$data['filter_by'] = $filter_by;

		$this->load->view('report/report_eol_of_laptop', $data);
	}
	
	function report_eol_of_assign_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'report_assign_laptop');
		$this->webspice->permission_verify('report_assign_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.UPDATED_DATE ';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 12 ';
    $page_index = 0;
    $no_of_record = 20;
    $limit = null;
    $filter_by = 'Last Created';
    $data['pager'] = null;
    $criteria = $this->uri->segment(2);
    $key = $this->uri->segment(3);
    if ($criteria == 'page') {
    	$page_index = (int)$key; 
    	$page_index < 0 ? $page_index=0 : $page_index=$page_index;
    }

		$initialSQL = "
			SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
			TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
			TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE 
			FROM TBL_STOCK 
			INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID 
		";

    # action area
    switch ($criteria) {
      case 'print':
      case 'csv':
        if( !isset($_SESSION['sql']) || !$_SESSION['sql'] ){
					$_SESSION['sql'] = $initialSQL . $where . $orderby;
					$_SESSION['filter_by'] = $filter_by;
    		}
    		$record = $this->db->query( $_SESSION['sql'] );										 		
				$data['get_record'] = $record->result();
				$this->load->view('report/print_report_eol_of_assign_laptop',$data);
				return false;
        break;       
    }
    
    # default
    $sql = $initialSQL . $where . $groupby . $orderby . $limit;
    
    $_SESSION['sql'] = $sql;
    $result = $this->db->query($sql)->result();
		$data['get_record'] = $result;
		$this->load->view('report/report_eol_of_assign_laptop', $data);
	}
	
	function confirmation($message){
		$_SESSION['confirmation'] = $message;
		$this->webspice->force_redirect($this->webspice->settings()->site_url_prefix.'confirmation');
	}
	
	function show_confirmation(){
		if( !isset($_SESSION['confirmation']) ){
			$_SESSION['confirmation'] = array();	
		}
		$data = $_SESSION['confirmation'];
		$this->load->view('view_message',$data);
	}
	
}

/* End of file */
/* Location: ./application/controllers/ */