<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Operation_controller extends CI_Controller {
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
	
	#employee
	function create_employee($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'create_employee');
		$this->webspice->permission_verify('create_employee');
		
		if( !isset($data['edit']) ){
			$data['edit'] = array(
			'USER_ID'=>null,
			'EMPLOYEE_ID'=>null,
			'USER_NAME'=>null,  
			'USER_EMAIL'=>null,
			'USER_PHONE'=>null,
			'USER_DIVISION'=>null,
			'USER_DEPARTMENT'=>null,
			'USER_DESIGNATION'=>null,
			'USER_TYPE'=>null,
			'STATUS'=>null
			);
		}
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('employee_id','EMPLOYEE_ID','required|trim|xss_clean');
		$this->form_validation->set_rules('user_name','USER_NAME','required|trim|xss_clean');
		$this->form_validation->set_rules('user_email','USER_EMAIL','required|valid_email|trim|xss_clean');
		$this->form_validation->set_rules('user_phone','USER_PHONE','required|trim|xss_clean');
		$this->form_validation->set_rules('user_division','USER_DIVISION','required|trim|xss_clean');
		$this->form_validation->set_rules('user_department','USER_DEPARTMENT','required|trim|xss_clean');
		$this->form_validation->set_rules('user_designation','USER_DESIGNATION','required|trim|xss_clean');
		$this->form_validation->set_rules('user_type','USER_TYPE','required|trim|xss_clean');

		if( !$this->form_validation->run() ){ 
			$this->load->view('employee/create_employee', $data);
			return FALSE;
		}
		
		# get input post
		$input = $this->webspice->get_input('user_id');
		
		#duplicate test
		$this->webspice->db_field_duplicate_test("SELECT * FROM TBL_LAPTOP_USER WHERE USER_EMAIL=?", array( $input->user_email), 'You are not allowed to enter duplicate email', 'USER_ID', $input->user_id, $data, 'employee/create_employee');
		
		# remove cache
		$this->webspice->remove_cache('laptop_user');
		
		# update process
		if( $input->user_id ){
			#update query
			$sql = "
			UPDATE TBL_LAPTOP_USER SET EMPLOYEE_ID=?, USER_NAME=?, USER_PHONE=?, 
			USER_DIVISION=?, USER_DEPARTMENT=?, USER_DESIGNATION=?, USER_TYPE=?,
			UPDATED_BY=?, UPDATED_DATE=TO_DATE(?,'yyyy-mm-dd hh:mi:ss') 
			WHERE USER_ID=?";
			$this->db->query($sql, array($input->employee_id, $input->user_name, $input->user_phone, 
			$input->user_division, $input->user_department, $input->user_designation, $input->user_type, 
			$this->webspice->get_user_id(), $this->webspice->now(), $input->user_id)); 

			$this->webspice->message_board('Record has been updated!');
			$this->webspice->log_me('user_updated - '.$input->user_email); # log activities
			$this->webspice->force_redirect($url_prefix.'manage_employee');
			return false;
		}
		
		#create user
		$new_squence = $this->webspice->getLastInserted('TBL_LAPTOP_USER','USER_ID');
		$temp_sequence = $new_squence + 1;
		$sql = "
		INSERT INTO TBL_LAPTOP_USER
		(USER_ID,EMPLOYEE_ID,USER_NAME, USER_EMAIL, USER_PHONE, 
		USER_DIVISION, USER_DEPARTMENT, USER_DESIGNATION, USER_TYPE,
		CREATED_BY, CREATED_DATE, STATUS)
		VALUES
		(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 7)";
		$result = $this->db->query($sql, array($temp_sequence, $input->employee_id, $input->user_name, $input->user_email, $input->user_phone, 
		$input->user_division, $input->user_department, $input->user_designation, $input->user_type, 
		$this->webspice->get_user_id(), $this->webspice->now())
		);
		
		if( !$result ){
			$this->webspice->message_board('We could not execute your request. Please tray again later or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
		}
		
		# remove cache
		$this->webspice->remove_cache('laptop_user');
		$this->webspice->log_me('employee_created'); # log
		$this->webspice->message_board('An employee has been created.');
		if( $this->webspice->permission_verify('create_employee', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_employee');
			return false;
		}
		
		$this->webspice->force_redirect($url_prefix);
	}
	
	function upload_employee_batch($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data_batch = 50; # how much row(s) inserted once
		ini_set('MAX_EXECUTION_TIME', 1200);
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'upload_employee_batch');
		$this->webspice->permission_verify('upload_employee_batch');
		
		if( !$_FILES || !$_FILES['attached_file']['tmp_name'] ){
			$this->load->view('employee/upload_employee_batch', $data);
			return FALSE;
		}

		# verify file type
		if( $_FILES['attached_file']['tmp_name'] ){
			$this->webspice->check_file_type(array('csv','xls'), 'attached_file', $data, 'employee/upload_employee_batch');   
		}

		# verify duplicate file
		#$get_bundle_file_sql = "SELECT * FROM TBL_LAPTOP_USER_FILE WHERE FILE_NAME = ?";
		#$get_bundle_file = $this->db->query($get_bundle_file_sql, array($_FILES['attached_file']['name']))->result();
		#if($get_bundle_file){
			#$this->webspice->message_board('This file uploaded once. Please upload the correct file.');
			#$this->webspice->force_redirect($url_prefix.'upload_employee_batch');
			#return FALSE;
		#}
		
		# verify file type and read accordingly
		$get_data = array();
		if( $_FILES['attached_file']['type'] == 'application/vnd.ms-excel' || $_FILES['attached_file']['type'] == 'application/octet-stream' ){
			$get_data = $this->webspice->excel_reader($_FILES['attached_file']['tmp_name'], 0, array('User Name','User Type','Designation','Employee ID','Division','Department-Unit','Email ID','Contact number'));
			
		}elseif( $_FILES['attached_file']['type'] == 'text/csv' ){
			$get_csv_data = $this->webspice->csv_reader($file_input_name='attached_file', array('Brand','Model','Serial number','PO','GR date'));
			if( !is_array($get_csv_data) ){
				$this->webspice->message_board($get_csv_data.' Please try again.');
				$this->webspice->force_redirect($url_prefix.'upload_employee_batch');
				return FALSE;
			}
		
			# excel reader column offset starts from 1, that is way this has been started from 1
			# because all operations has been done using above offset serial
			$get_data = array();
			foreach($get_csv_data as $key => $value){
				$new_array = array();
				foreach($value as $key1=>$value1){
					$new_array[$key1+1] = trim($value1);
				}
			  $get_data[$key] = $new_array;
			}
			
		}else{
			echo 'File Invalid!';
			exit;
		}
		
		if( !is_array($get_data) ){
			$this->webspice->message_board($get_data.' Please try again.');
			$this->webspice->force_redirect($url_prefix.'upload_employee_batch');
			return FALSE;
		}

		# verify data
		$data_error = null;
		$employee_id_array = array();
		foreach($get_data as $k=>$v){
			$data_list = $v;
			$user_name = trim($data_list[1]);
			$user_type = trim($data_list[2]);
			$designation = trim($data_list[3]);
			$employee_id = trim($data_list[4]);
			$employee_id_array[] = "'".$employee_id."'";
			$divission = trim($data_list[5]);
			$department_unit = trim($data_list[6]);
			$email_id = trim($data_list[7]);
			$contact_number = trim($data_list[8]);
		
			# must have column value - column offset started from 1
			if( !isset($user_name) || !isset($user_type) || !isset($designation) || !isset($employee_id) || !isset($divission) || !isset($department_unit) || !isset($email_id) || !isset($contact_number) ){
				$data_error .= 'Row #'.$k.' is incomplete.<br />';
			}

			# verify joining date
			#if( isset($gr_date) && !$this->webspice->isDate($gr_date,'day','month','year') ){
				#$data_error .= 'Date "'.$gr_date.'" at Row #'.$k.' is invalid.<br />';
			#}
		}
		
		# duplicate data test
		$get_duplicate_data = $this->db->query("SELECT * FROM TBL_LAPTOP_USER WHERE EMPLOYEE_ID IN(".implode(',',$employee_id_array).")");
		$get_duplicate_data = $get_duplicate_data->result();
		if($get_duplicate_data){
			$duplicate_sr_no = array();
			foreach($get_duplicate_data as $duplicateKey=>$duplicateValue){
				$duplicate_employee_id[] = $duplicateValue->EMPLOYEE_ID;
			}
			$data['error'] = '<span class="fred fbold">Duplicate Serial found - '.implode(', ', $duplicate_employee_id).'</span>';
			$this->load->view('employee/upload_employee_batch', $data);
			return FALSE;
		}
		
		if($data_error){
			$data['error'] = $data_error.'<span class="fred fbold">Please update the file and try again.</span>';
			$this->load->view('employee/upload_employee_batch', $data);
			return FALSE;
		}

		# insert data
		$this->db->trans_off();
		$this->db->trans_begin();

		$data_section = 0;
		$data_count = 1; # row offset started from 2
		$data_query = array();
		while( count($get_data) > $data_section ){
			$data_query = array(); # make it empty
			$new_squence = $this->webspice->getLastInserted('TBL_LAPTOP_USER','USER_ID');
			$temp_sequence = $new_squence + 1;
			for($i=1; $i<=$data_batch; $i++){
				$data_count++;
				if( isset($get_data[$data_count]) ){
					$data_sheet = $get_data[$data_count];
					$user_name = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[1]));
					$user_type = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[2]));
					$designation = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[3]));
					$employee_id = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[4]));
					$divission = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[5]));
					$department_unit = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[6]));
					$email_id = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[7]));
					$contact_number = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[8]));
					$data_query[] = "('".($temp_sequence)."','".$user_name."','".$user_type."','".$designation."','".$employee_id."','".$divission."','".$department_unit."','".$email_id."','".$contact_number."','".$this->webspice->get_user_id()."',TO_DATE('".$this->webspice->now()."','yyyy-mm-dd hh:mi:ss'),7)";
					$temp_sequence++;
				}
				
			}

			if($data_query){
				foreach($data_query as $indexKey=>$queryValue){
					$this->db->query("
					INSERT INTO TBL_LAPTOP_USER (USER_ID,USER_NAME,USER_TYPE,USER_DESIGNATION,EMPLOYEE_ID,USER_DIVISION,USER_DEPARTMENT,USER_EMAIL,USER_PHONE,CREATED_BY,CREATED_DATE,STATUS) VALUES ".$queryValue);
				}
			}
			
			$data_section += $data_batch;
		}
		
		# insert file name to stop duplicate upload
		$new_squence = $this->webspice->getLastInserted('TBL_PURCHASE_FILE','FILE_ID');
		$new_squence = (int)$new_squence + 1;
		$this->db->query("INSERT INTO TBL_PURCHASE_FILE(FILE_ID,FILE_NAME, UPLOAD_BY, UPLOAD_DATE) VALUES(?,?,?,TO_DATE(?,'yyyy-mm-dd hh:mi:ss'))", array($new_squence, $_FILES['attached_file']['name'], $this->webspice->get_user_id(), $this->webspice->now()));

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$this->webspice->message_board('We could not execute your request. Please try again or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else{
			$this->db->trans_commit();
		}
		$this->db->trans_off();
		
		# remove cache
		$this->webspice->remove_cache('laptop_user');
		
		$this->webspice->log_me('employee_uploaded_batch'); # log
		
		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_employee', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_employee');
		}

		$this->webspice->force_redirect($url_prefix);
	}
	
	function manage_employee(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_employee');
		$this->webspice->permission_verify('manage_employee');

		$this->load->database();
    $orderby = null;
    $groupby = null;
    $where = ' WHERE ROWNUM <= 50';
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

		$initialSQL = " SELECT TBL_LAPTOP_USER.* FROM TBL_LAPTOP_USER ";

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_LAPTOP_USER', 
			$InputField = array('USER_DIVISION', 'USER_DEPARTMENT', 'USER_DESIGNATION', 'USER_TYPE'), 
			$Keyword = array('USER_ID','USER_NAME','USER_EMAIL','USER_PHONE'),
			$AdditionalWhere = null,
			$DateBetween = array()
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
			
				$this->load->view('employee/print_employee',$data);
				return false;
        break;

			case 'edit':
          $this->webspice->edit_generator($TableName='TBL_LAPTOP_USER', $KeyField='USER_ID', $key, $RedirectController='operation_controller', $RedirectFunction='create_employee', $PermissionName='manage_employee', $StatusCheck=null, $Log='edit_laptop_user');          
					return false;
          break;
          
 			case 'inactive':
      		$this->webspice->action_executer($TableName='TBL_LAPTOP_USER', $KeyField='USER_ID', $key, $RedirectURL='manage_employee', $PermissionName='manage_employee', $StatusCheck=7, $ChangeStatus=-7, $RemoveCache='user', $Log='inactive_laptop_user');
					return false;	
          break; 

			case 'active':
					$this->webspice->action_executer($TableName='TBL_LAPTOP_USER', $KeyField='USER_ID', $key, $RedirectURL='manage_employee', $PermissionName='manage_employee', $StatusCheck=-7, $ChangeStatus=7, $RemoveCache='user', $Log='active_laptop_user');
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

		$this->load->view('employee/manage_employee', $data);
	}
	
	#purchase
	function create_purchase($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'create_purchase');
		$this->webspice->permission_verify('create_purchase');
		
		if( !isset($data['edit']) ){
			$data['edit'] = array(
			'STOCK_ID'=>null,
			'LAPTOP_SR_NO'=>null,  
			'BRAND'=>null,
			'MODEL'=>null,
			'PO_NO'=>null,
			'PURCHASE_DATE'=>null,
			'STATUS'=>null
			);
		}
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('sl_number','LAPTOP_SR_NO','required|trim|xss_clean');
		$this->form_validation->set_rules('laptop_brand','BRAND','required|trim|xss_clean');
		$this->form_validation->set_rules('laptop_model','MODEL','required|trim|xss_clean');
		$this->form_validation->set_rules('po_number','PO_NO','required|trim|xss_clean');
		$this->form_validation->set_rules('gr_date','PURCHASE_DATE','required|trim|xss_clean');

		if( !$this->form_validation->run() ){ 
			$this->load->view('purchase/create_purchase', $data);
			return FALSE;
		}
		
		# get input post
		$input = $this->webspice->get_input('stock_id');
		
		#duplicate test
		$this->webspice->db_field_duplicate_test("SELECT * FROM TBL_STOCK WHERE LAPTOP_SR_NO=?", array( $input->sl_number), 'You are not allowed to enter duplicate Laptop', 'STOCK_ID', $input->stock_id, $data, 'purchase/create_purchase');
		
		# remove cache
		$this->webspice->remove_cache('laptop_purchase');
		
		# update process
		if( $input->stock_id ){
			#update query
			$sql = "
			UPDATE TBL_STOCK SET LAPTOP_SR_NO=?, BRAND=?, 
			MODEL=?, PO_NO=?, PURCHASE_DATE=TO_DATE(?,'yyyy-mm-dd hh:mi:ss'),
			UPDATED_BY=?, UPDATED_DATE=TO_DATE(?,'yyyy-mm-dd hh:mi:ss') 
			WHERE STOCK_ID=?";
			$this->db->query($sql, array($input->sl_number, $input->laptop_brand, 
			$input->laptop_model, $input->po_number, $input->gr_date, 
			$this->webspice->get_user_id(), $this->webspice->now(), $input->stock_id)); 

			$this->webspice->message_board('Record has been updated!');
			$this->webspice->log_me('Create Purchase'); # log activities
			$this->webspice->force_redirect($url_prefix.'manage_employee');
			return false;
		}
		
		#create purchase
		$new_squence = $this->webspice->getLastInserted('TBL_STOCK','STOCK_ID');
		$temp_sequence = $new_squence + 1;
		$sql = "
		INSERT INTO TBL_STOCK
		(STOCK_ID, LAPTOP_SR_NO, BRAND, MODEL, 
		PO_NO, PURCHASE_DATE, 
		CREATED_BY, CREATED_DATE, STATUS)
		VALUES
		(?,?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 12)";
		$result = $this->db->query($sql, array($temp_sequence,$input->sl_number, $input->laptop_brand, $input->laptop_model, 
		$input->po_number, $input->gr_date, 
		$this->webspice->get_user_id(), $this->webspice->now())
		);
		
		if( !$result){
			$this->webspice->message_board('We could not execute your request. Please tray again later or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
		}
		# remove cache
		$this->webspice->remove_cache('laptop');
		
		$this->webspice->log_me('a_purchase_created'); # log
		$this->webspice->message_board('A Laptop has been created.');
		
		if( $this->webspice->permission_verify('create_purchase', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_purchase');
			return false;
		}
		
		$this->webspice->force_redirect($url_prefix);
	}
	
	function upload_purchase_batch($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data_batch = 50; # how much row(s) inserted once
		ini_set('MAX_EXECUTION_TIME', 1200);
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'upload_purchase_batch');
		$this->webspice->permission_verify('upload_purchase_batch');
		
		if( !$_FILES || !$_FILES['attached_file']['tmp_name'] ){
			$this->load->view('purchase/upload_purchase_batch', $data);
			return FALSE;
		}

		# verify file type
		if( $_FILES['attached_file']['tmp_name'] ){
			$this->webspice->check_file_type(array('csv','xls'), 'attached_file', $data, 'purchase/upload_purchase_batch');   
		}

/* 		# verify duplicate file
		$get_bundle_file_sql = "SELECT * FROM TBL_PURCHASE_FILE WHERE FILE_NAME = ?";
		$get_bundle_file = $this->db->query($get_bundle_file_sql, array($_FILES['attached_file']['name']))->result();
		if($get_bundle_file){
			$this->webspice->message_board('This file uploaded once. Please upload the correct file.');
			$this->webspice->force_redirect($url_prefix.'upload_purchase_batch');
			return FALSE;
		} */
		
		# verify file type and read accordingly
		$get_data = array();
		if( $_FILES['attached_file']['type'] == 'application/vnd.ms-excel' || $_FILES['attached_file']['type'] == 'application/octet-stream' ){
			$get_data = $this->webspice->excel_reader($_FILES['attached_file']['tmp_name'], 0, array('Brand','Model','Serial number','PO','GR date'));
			
		}elseif( $_FILES['attached_file']['type'] == 'text/csv' ){
			$get_csv_data = $this->webspice->csv_reader($file_input_name='attached_file', array('Brand','Model','Serial number','PO','GR date'));
			if( !is_array($get_csv_data) ){
				$this->webspice->message_board($get_csv_data.' Please try again.');
				$this->webspice->force_redirect($url_prefix.'upload_purchase_batch');
				return FALSE;
			}
		
			# excel reader column offset starts from 1, that is way this has been started from 1
			# because all operations has been done using above offset serial
			$get_data = array();
			foreach($get_csv_data as $key => $value){
				$new_array = array();
				foreach($value as $key1=>$value1){
					$new_array[$key1+1] = trim($value1);
				}
			  $get_data[$key] = $new_array;
			}
			
		}else{
			echo 'File Invalid!';
			exit;
		}
		
		if( !is_array($get_data) ){
			$this->webspice->message_board($get_data.' Please try again.');
			$this->webspice->force_redirect($url_prefix.'upload_purchase_batch');
			return FALSE;
		}

		# verify data
		$data_error = null;
		$serial_array = array();
		foreach($get_data as $k=>$v){
			$data_list = $v;
			$brand = trim($data_list[1]);
			$model = trim($data_list[2]);
			$serial_no = trim(strtoupper($data_list[3]));
			$serial_array[] = "'".$serial_no."'";
			$po = trim($data_list[4]);
			$gr_date = trim($data_list[5]);

			# must have column value - column offset started from 1
			if( !isset($brand) || !isset($model) || !isset($serial_no) || !isset($po) || !isset($gr_date) ){
				$data_error .= 'Row #'.$k.' is incomplete.<br />';
			}

			# verify date
			if( isset($gr_date) && !$this->webspice->isDate($gr_date,'day','month','year') ){
				$data_error .= 'GR Date "'.$gr_date.'" at Row #'.$k.' is invalid.<br />';
			}
	
			# verify date
			if( isset($gr_date) && strtotime($gr_date) > strtotime(date('d-m-Y')) ){
				$data_error .= 'GR Date "'.$gr_date.'" at Row #'.$k.' is invalid (not in range).<br />';
			}
			
			# verify duplicate laptop
			if( isset($serial_no) && $this->customcache->laptop_maker($serial_no,'LAPTOP_SR_NO') == $serial_no ){
				$data_error .= 'Serial no "'.$serial_no.'" at Row #'.$k.' is duplicate.<br />';
			}			
			
		}
		
		
/*		if($get_duplicate_data){
			$duplicate_sr_no = array();
			foreach($get_duplicate_data as $duplicateKey=>$duplicateValue){
				$duplicate_sr_no[] = $duplicateValue->LAPTOP_SR_NO;
				$data_error .=' Serial no "'.$duplicateValue->LAPTOP_SR_NO.'" at Row #'.$duplicateKey.' is duplicate.<br /> '; 
			}

			$data['error'] = $data_error;
		
			$this->load->view('purchase/upload_purchase_batch', $data);
			return FALSE;
		}*/
		
		if($data_error){
			$data['error'] = $data_error.'<span class="fred fbold">Please update the file and try again.</span>';
			$this->load->view('purchase/upload_purchase_batch', $data);
			return FALSE;
		}

		# insert data
		$this->db->trans_off();
		$this->db->trans_begin();

		$data_section = 0;
		$data_count = 1; # row offset started from 2
		$data_query = array();
		while( count($get_data) > $data_section ){
			$data_query = array(); # make it empty
			$new_squence = $this->webspice->getLastInserted('TBL_STOCK','STOCK_ID');
			$temp_sequence = $new_squence + 1;
			for($i=1; $i<=$data_batch; $i++){
				$data_count++;
				if( isset($get_data[$data_count]) ){
					$data_sheet = $get_data[$data_count];
					$brand = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[1]));
					$model = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[2]));
					$serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper($data_sheet[3])));
					$po = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[4]));
					$gr_date = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[5]));
					$data_query[] = "('".($temp_sequence)."','".$serial_no."','".$brand."','".$model."','".$po."',TO_DATE('".date("Y-m-d",strtotime($gr_date))."','yyyy-mm-dd hh:mi:ss'),TO_DATE('".$this->webspice->addDate(date("Y-m-d",strtotime($gr_date)), 4, 'year')."','yyyy-mm-dd hh:mi:ss'),'".$this->webspice->get_user_id()."',TO_DATE('".$this->webspice->now()."','yyyy-mm-dd hh:mi:ss'),11)";
					$temp_sequence++;
				}
			}

			if($data_query){
				foreach($data_query as $indexKey=>$queryValue){
					$this->db->query("
					INSERT INTO TBL_STOCK
					(STOCK_ID,LAPTOP_SR_NO,BRAND,MODEL,PO_NO,PURCHASE_DATE,EOL_DATE,CREATED_BY,CREATED_DATE,STATUS) 
					VALUES ".$queryValue);
				}
			}
			
			$data_section += $data_batch;
		}
		
		# insert file name to stop duplicate upload
		$new_squence = $this->webspice->getLastInserted('TBL_PURCHASE_FILE','FILE_ID');
		$new_squence = (int)$new_squence + 1;
		$this->db->query("INSERT INTO TBL_PURCHASE_FILE(FILE_ID,FILE_NAME, UPLOAD_BY, UPLOAD_DATE) VALUES(?,?,?,TO_DATE(?,'yyyy-mm-dd hh:mi:ss'))", array($new_squence, $_FILES['attached_file']['name'], $this->webspice->get_user_id(), $this->webspice->now()));

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$this->webspice->message_board('We could not execute your request. Please try again or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else{
			$this->db->trans_commit();
		}
		$this->db->trans_off();
		# remove cache
		$this->webspice->remove_cache('laptop');
		
			$this->webspice->log_me('purchase_batch_puloaded'); # log
		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_purchase', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_purchase');
		}

		$this->webspice->force_redirect($url_prefix);
	}
	
	function upload_purchase_batch_without_gr_date($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data_batch = 50; # how much row(s) inserted once
		ini_set('MAX_EXECUTION_TIME', 1200);
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'upload_purchase_batch_without_gr_date');
		$this->webspice->permission_verify('upload_purchase_batch');
		
		if( !$_FILES || !$_FILES['attached_file']['tmp_name'] ){
			$this->load->view('purchase/upload_purchase_batch_without_gr_date', $data);
			return FALSE;
		}

		# verify file type
		if( $_FILES['attached_file']['tmp_name'] ){
			$this->webspice->check_file_type(array('csv','xls'), 'attached_file', $data, 'purchase/upload_purchase_batch_without_gr_date');   
		}

		# verify duplicate file
		$get_bundle_file_sql = "SELECT * FROM TBL_PURCHASE_FILE WHERE FILE_NAME = ?";
		$get_bundle_file = $this->db->query($get_bundle_file_sql, array($_FILES['attached_file']['name']))->result();
		if($get_bundle_file){
			$this->webspice->message_board('This file uploaded once. Please upload the correct file.');
			$this->webspice->force_redirect($url_prefix.'upload_purchase_batch');
			return FALSE;
		}
		
		# verify file type and read accordingly
		$get_data = array();
		if( $_FILES['attached_file']['type'] == 'application/vnd.ms-excel' || $_FILES['attached_file']['type'] == 'application/octet-stream' ){
			$get_data = $this->webspice->excel_reader($_FILES['attached_file']['tmp_name'], 0, array('Brand','Model','Serial number','PO'));
			
		}elseif( $_FILES['attached_file']['type'] == 'text/csv' ){
			$get_csv_data = $this->webspice->csv_reader($file_input_name='attached_file', array('Brand','Model','Serial number','PO'));
			if( !is_array($get_csv_data) ){
				$this->webspice->message_board($get_csv_data.' Please try again.');
				$this->webspice->force_redirect($url_prefix.'upload_purchase_batch');
				return FALSE;
			}
		
			# excel reader column offset starts from 1, that is way this has been started from 1
			# because all operations has been done using above offset serial
			$get_data = array();
			foreach($get_csv_data as $key => $value){
				$new_array = array();
				foreach($value as $key1=>$value1){
					$new_array[$key1+1] = trim($value1);
				}
			  $get_data[$key] = $new_array;
			}
			
		}else{
			echo 'File Invalid!';
			exit;
		}
		
		if( !is_array($get_data) ){
			$this->webspice->message_board($get_data.' Please try again.');
			$this->webspice->force_redirect($url_prefix.'upload_purchase_batch');
			return FALSE;
		}

		# verify data
		$data_error = null;
		$serial_array = array();
		foreach($get_data as $k=>$v){
			$data_list = $v;
			$brand = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[1]));
			$model = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[2]));
			$serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper($data_list[3])));
			$serial_array[] = "'".$serial_no."'";
			$po = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[4]));
		
			# must have column value - column offset started from 1
			if( !isset($brand) || !isset($model) || !isset($serial_no) || !isset($po) ){
				$data_error .= 'Row #'.$k.' is incomplete.<br />';
			}
			
			# verify duplicate laptop
			if( isset($serial_no) && $this->customcache->laptop_maker($serial_no,'LAPTOP_SR_NO') == $serial_no ){
				$data_error .= 'Serial no "'.$serial_no.'" at Row #'.$k.' is duplicate.<br />';
			}		
			
		}
		
		if($data_error){
			$data['error'] = $data_error.'<span class="fred fbold">Please update the file and try again.</span>';
			$this->load->view('purchase/upload_purchase_batch_without_gr_date', $data);
			return FALSE;
		}

		# insert data
		$this->db->trans_off();
		$this->db->trans_begin();

		$data_section = 0;
		$data_count = 1; # row offset started from 2
		$data_query = array();
		while( count($get_data) > $data_section ){
			$data_query = array(); # make it empty
			$new_squence = $this->webspice->getLastInserted('TBL_STOCK','STOCK_ID');
			$temp_sequence = $new_squence + 1;
			for($i=1; $i<=$data_batch; $i++){
				$data_count++;
				if( isset($get_data[$data_count]) ){
					$data_sheet = $get_data[$data_count];
					
					$brand = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[1]));
					$model = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[2]));
					$serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper($data_sheet[3])));
					$po = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[4]));
					$data_query[] = "('".($temp_sequence)."','".$serial_no."','".$brand."','".$model."','".$po."','".$this->webspice->get_user_id()."',TO_DATE('".$this->webspice->now()."','yyyy-mm-dd hh:mi:ss'),11)";
					$temp_sequence++;
				}
				
			}

			if($data_query){
				foreach($data_query as $indexKey=>$queryValue){
					$this->db->query("
					INSERT INTO TBL_STOCK
					(STOCK_ID,LAPTOP_SR_NO,BRAND,MODEL,PO_NO,CREATED_BY,CREATED_DATE,STATUS) 
					VALUES ".$queryValue);
				}
			}
			
			$data_section += $data_batch;
		}
		
		# insert file name to stop duplicate upload
		$new_squence = $this->webspice->getLastInserted('TBL_PURCHASE_FILE','FILE_ID');
		$new_squence = (int)$new_squence + 1;
		$this->db->query("INSERT INTO TBL_PURCHASE_FILE(FILE_ID,FILE_NAME, UPLOAD_BY, UPLOAD_DATE) VALUES(?,?,?,TO_DATE(?,'yyyy-mm-dd hh:mi:ss'))", array($new_squence, $_FILES['attached_file']['name'], $this->webspice->get_user_id(), $this->webspice->now()));

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$this->webspice->message_board('We could not execute your request. Please try again or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else{
			$this->db->trans_commit();
		}
		$this->db->trans_off();
		
		# remove cache
		$this->webspice->remove_cache('laptop');
		
		$this->webspice->log_me('batch_uploaded_without_gr_date'); # log
		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_purchase', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_purchase');
		}

		$this->webspice->force_redirect($url_prefix);
	}
	
	function upload_purchase_gr_date($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data_batch = 50; # how much row(s) inserted once
		ini_set('MAX_EXECUTION_TIME', 1200);
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'upload_purchase_gr_date');
		$this->webspice->permission_verify('upload_purchase_batch');
		
		if( !$_FILES || !$_FILES['attached_file']['tmp_name'] ){
			$this->load->view('purchase/upload_purchase_gr_date', $data);
			return FALSE;
		}

		# verify file type
		if( $_FILES['attached_file']['tmp_name'] ){
			$this->webspice->check_file_type(array('csv','xls'), 'attached_file', $data, 'purchase/upload_purchase_gr_date');   
		}

		# verify duplicate file
		$get_bundle_file_sql = "SELECT * FROM TBL_PURCHASE_FILE WHERE FILE_NAME = ?";
		$get_bundle_file = $this->db->query($get_bundle_file_sql, array($_FILES['attached_file']['name']))->result();
		if($get_bundle_file){
			$this->webspice->message_board('This file uploaded once. Please upload the correct file.');
			$this->webspice->force_redirect($url_prefix.'upload_purchase_gr_date');
			return FALSE;
		}
		
		# verify file type and read accordingly
		$get_data = array();
		if( $_FILES['attached_file']['type'] == 'application/vnd.ms-excel' || $_FILES['attached_file']['type'] == 'application/octet-stream' ){
			$get_data = $this->webspice->excel_reader($_FILES['attached_file']['tmp_name'], 0, array('Serial number','GR date'));
		}elseif( $_FILES['attached_file']['type'] == 'text/csv' ){
			$get_csv_data = $this->webspice->csv_reader($file_input_name='attached_file', array('Serial number','GR date'));
			if( !is_array($get_csv_data) ){
				$this->webspice->message_board($get_csv_data.' Please try again.');
				$this->webspice->force_redirect($url_prefix.'upload_purchase_gr_date');
				return FALSE;
			}
		
			# excel reader column offset starts from 1, that is way this has been started from 1
			# because all operations has been done using above offset serial
			$get_data = array();
			foreach($get_csv_data as $key => $value){
				$new_array = array();
				foreach($value as $key1=>$value1){
					$new_array[$key1+1] = trim($value1);
				}
			  $get_data[$key] = $new_array;
			}
			
		}else{
			echo 'File Invalid!';
			exit;
		}
		
		if( !is_array($get_data) ){
			$this->webspice->message_board($get_data.' Please try again.');
			$this->webspice->force_redirect($url_prefix.'upload_purchase_gr_date');
			return FALSE;
		}

		# verify data
		$data_error = null;
		$serial_array = array();

		foreach($get_data as $k=>$v){
			$data_list = $v;
			$serial_no = trim(strtoupper($data_list[1]));
			$gr_date = trim($data_list[2]);
			# must have column value - column offset started from 1
			if( !isset($serial_no) || !isset($gr_date) ){
				$data_error .= 'Row #'.$k.' is incomplete.<br />';
			}
			
			
			# verify serial no
			if( isset($serial_no) && !$this->customcache->laptop_maker($serial_no,'LAPTOP_SR_NO') ){
				$data_error .= 'Serial No "'.$serial_no.'" at Row #'.$k.' is invalid.<br />';
			}
			
			# verify date
			if( isset($gr_date) && !$this->webspice->isDate($gr_date,'day','month','year') ){
				$data_error .= 'GR Date "'.$gr_date.'" at Row #'.$k.' is invalid.<br />';
			}
			
			# verify date
			if( isset($gr_date) && strtotime($gr_date) > strtotime(date('d-m-Y')) ){
				$data_error .= 'GR Date "'.$gr_date.'" at Row #'.$k.' is invalid (not in range).<br />';
			}
			
		}
			
		if($data_error){
			$data['error'] = $data_error.'<span class="fred fbold">Please update the file and try again.</span>';
			$this->load->view('purchase/upload_purchase_gr_date', $data);
			return FALSE;
		}

		# insert data
		$this->db->trans_off();
		$this->db->trans_begin();

		$data_section = 0;
		$data_count = 1; # row offset started from 2
		$data_query = array();
		while( count($get_data) > $data_section ){
			$data_query = array(); # make it empty
			for($i=1; $i<=$data_batch; $i++){
				$data_count++;
				if( isset($get_data[$data_count]) ){
					$data_sheet = $get_data[$data_count];
					$serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper($data_sheet[1])));
					$gr_date = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_sheet[2]));
					#dd($gr_date);
					$this->db->query("UPDATE TBL_STOCK SET PURCHASE_DATE=TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), EOL_DATE=TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss') WHERE LAPTOP_SR_NO=?", array(date("Y-m-d",strtotime($gr_date)),$this->webspice->addDate(date("Y-m-d",strtotime($gr_date)), 4, 'year'),$this->webspice->get_user_id(),$this->webspice->now(),strtoupper($serial_no)));
				}
				
			}

			$data_section += $data_batch;
		}
		
		# insert file name to stop duplicate upload
		$new_squence = $this->webspice->getLastInserted('TBL_PURCHASE_FILE','FILE_ID');
		$new_squence = (int)$new_squence + 1;
		$this->db->query("INSERT INTO TBL_PURCHASE_FILE(FILE_ID,FILE_NAME, UPLOAD_BY, UPLOAD_DATE) VALUES(?,?,?,TO_DATE(?,'yyyy-mm-dd hh:mi:ss'))", array($new_squence, $_FILES['attached_file']['name'], $this->webspice->get_user_id(), $this->webspice->now()));

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$this->webspice->message_board('We could not execute your request. Please try again or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else{
			$this->db->trans_commit();
		}
		$this->db->trans_off();

		# remove cache
		$this->webspice->remove_cache('laptop');

		$this->webspice->log_me('uploaded_only_gr_date'); # log
		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_purchase', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_purchase');
		}

		$this->webspice->force_redirect($url_prefix);
	}


	
	function manage_purchase(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_purchase');
		$this->webspice->permission_verify('manage_purchase');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.STOCK_ID ';
    $groupby = null;
    $where = ' WHERE ROWNUM <= 5000';
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

		$initialSQL = " SELECT * FROM TBL_STOCK ";

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array('status'),
			$Keyword = array('LAPTOP_SR_NO','BRAND','MODEL','PURCHASE_DATE','PO_NO'),
			$AdditionalWhere = null,
			$DateBetween = array('PURCHASE_DATE', 'date_from', 'date_end'),
			$DateBetween2 = array('EOL_DATE', 'date_from2', 'date_end2')
			);
			
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
			
				$this->load->view('purchase/print_purchase',$data);
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


		$this->load->view('purchase/manage_purchase', $data);
	}
	
	#assign Laptop
	function assign_laptop($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'assign_laptop');
		$this->webspice->permission_verify('assign_laptop');
		
		# get input post
		$input = $this->webspice->get_input();
		
		# ajax call
		if( isset($input->ajax_call) && $input->ajax_call ){
			$html = 'no_data';
			switch( $input->action_type ){
				case 'get_user':
					$data_id = $input->data_id;
					
					#get AD user
					$search_filter = 'mail='.$data_id;
					$user = $this->webspice->get_ad_user('Administrators', 'administrator', 'nns@1212', $ldap_host='192.168.10.10', $ldap_dn='DC=nns-solution,DC=net', $base_dn='DC=nns-solution,DC=net', $ldap_user_domain='@nns-solution.net', $search_filter);
					
					if(!$user){
						$html = 'no_data';
						echo $html;
						exit;
					}

					#check user to tbl_laptop_user
					$user=$user[0];
					$result = $this->db->query("
					SELECT TBL_LAPTOP_USER.*
					FROM TBL_LAPTOP_USER
					WHERE TBL_LAPTOP_USER.USER_EMAIL = ?
					", array($user['mail']))->result();
					
					#cache remove
					$this->webspice->remove_cache('laptop_user');
					if( !$result ){	
						#insert user
						$new_squence = $this->webspice->getLastInserted('TBL_LAPTOP_USER','USER_ID');
						$temp_sequence = $new_squence + 1;
						
						$department = $user['department'];
						$dep=null;
						$div=null;
						if(strpos($department,'|')!=false){
							$depdiv = explode('|',$department);
							$dep = trim($depdiv[0]);
							$div = trim($depdiv[1]);
						}else{
							$dep = null;
							$div = $department;
						}
						
						$sql = "
						INSERT INTO TBL_LAPTOP_USER
						(USER_ID,USER_NAME, USER_EMAIL, USER_PHONE, 
						USER_DIVISION, USER_DEPARTMENT, USER_DESIGNATION, USER_TYPE,
						CREATED_BY, CREATED_DATE,EMPLOYEE_ID, STATUS)
						VALUES
						(?, ?, ?, ?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'),?, 7)";
						$result = $this->db->query($sql, array($temp_sequence, $user['givenname']." ".$user['sn'], $user['mail'], $user['mobile'],  $div, $dep, $user['title'], '', $this->webspice->get_user_id(), $this->webspice->now(), $user['pager'])
						);
					}else{
						if($this->customcache->designation_maker($result[0]->USER_DESIGNATION,'OPTION_VALUE') != $user['title']){
						
						$department = $user['department'];
						$dep=null;
						$div=null;
						if(strpos($department,'|')!=false){
							$depdiv = explode('|',$department);
							$dep = trim($depdiv[0]);
							$div = trim($depdiv[1]);
						}else{
							$dep = null;
							$div = $department;
						}
							# update user
							$this->db->query("UPDATE TBL_LAPTOP_USER SET
							USER_DIVISION=?, USER_DEPARTMENT=?, USER_DESIGNATION=?, 
							UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
							WHERE EMPLOYEE_ID=?",
							array($div,$dep,$user['title'], 
							$this->webspice->get_user_id() , $this->webspice->now(),$user['pager'])
							);
						}
					}
					
					$result = $this->db->query("
					SELECT TBL_LAPTOP_USER.*
					FROM TBL_LAPTOP_USER
					WHERE TBL_LAPTOP_USER.USER_EMAIL = ?
					", array($user['mail']))->result();	

					$html = '<h2>User Information</h2>';
					$html .= '<table class="table table-bordered table-striped">';
						$html .= '<tr><td>User Name</td><td width="10" align="center">:</td><td><input type="text" class="input_style" id="user_name" value="'.$result[0]->USER_NAME.'" /></td></tr>';
						$html .= '<tr><td>User Email</td><td width="10" align="center">:</td><td>'.$result[0]->USER_EMAIL.'</td></tr>';
						$html .= '<tr><td>User Phone</td><td width="10" align="center">:</td><td><input type="text" class="input_style" id="user_phone" value="'.$result[0]->USER_PHONE.'" /></td></tr>';
						$html .= '<tr><td>Division</td><td width="10" align="center">:</td><td><input type="text"  class="input_style" id="user_division" value="'.$result[0]->USER_DIVISION.'" /></td></tr>';
						$html .= '<tr><td>Department</td><td width="10" align="center">:</td><td><input type="text" class="input_style" id="user_department" value="'.$result[0]->USER_DEPARTMENT.'" /></td></tr>';
						$html .= '<tr><td>Designation</td><td width="10" align="center">:</td><td><input type="text" class="input_style" id="user_designation" value="'.$result[0]->USER_DESIGNATION.'" /></td></tr>';
						$html .= '<tr><td>Employee Type</td><td width="10" align="center">:</td><td><select class="input_style" id="user_type"><option value="">Select One</option>'.str_replace('value="'.$result[0]->USER_TYPE.'"','value="'.$result[0]->USER_TYPE.'" selected="selected"', $this->customcache->get_employee_type()).'"  /></td></tr>';
					$html .= '</table>';
					break;
					
				case 'get_laptop':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.*
					FROM TBL_STOCK
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 11 ){
						# STATUS 11 = AVAILABLE
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$html = '<h2>Laptop Information</h2>';
					$html .= '<table class="table table-bordered table-striped">';
						$html .= '<tr><td>Brand</td><td width="10" align="center">:</td><td>'.$result->BRAND.'</td></tr>';
						$html .= '<tr><td>Model</td><td width="10" align="center">:</td><td>'.$result->MODEL.'</td></tr>';
						$html .= '<tr><td>PO NO.</td><td width="10" align="center">:</td><td>'.$result->PO_NO.'</td></tr>';
						$html .= '<tr><td>Purchase Date</td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->PURCHASE_DATE).'</td></tr>';
						$html .= '<tr><td>EOL Date</td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->EOL_DATE).'</td></tr>';
					$html .= '</table>';
					break;
			}
			
			echo $html;
			exit;
		}
		
		# ajax call and assign laptop
		if( isset($input->ajax_call_for_assign) && $input->ajax_call_for_assign ){
			# verify user
			$get_user = $this->db->query("
			SELECT TBL_LAPTOP_USER.*
			FROM TBL_LAPTOP_USER
			WHERE TBL_LAPTOP_USER.USER_EMAIL = ?
			", array($input->user_id))->result();
			
			$get_laptop = $this->db->query("
			SELECT TBL_STOCK.*
			FROM TBL_STOCK
			WHERE TBL_STOCK.LAPTOP_SR_NO = ?
			", array($input->laptop_sr))->result();
			
			if( !$get_user || !$get_laptop || $get_laptop[0]->STATUS != 11 ){
				# STATUS 11 = AVAILABLE
				$html = 'not_available';
				echo $html;
				exit;
			}
			
			# start transaction
			$this->db->trans_off();
			$this->db->trans_begin();
			
			#cache remove
			$this->webspice->remove_cache('laptop_user');
			
			# update user
			$this->db->query("UPDATE TBL_LAPTOP_USER SET
			USER_NAME=?, USER_PHONE=?, USER_DIVISION=?, USER_DEPARTMENT=?, USER_DESIGNATION=?, 
			USER_TYPE=?, UPDATED_BY=?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
			WHERE USER_EMAIL=?",
			array($input->user_name, $input->user_phone, $input->user_division, $input->user_department, $input->user_designation, 
			$input->user_type, $this->webspice->get_user_id() , $this->webspice->now(),
			$input->user_id)
			);
			#cache remove
			$this->webspice->remove_cache('laptop');

			# update stock
			# STATUS 12 = Assigned
			$this->db->query("UPDATE TBL_STOCK SET
			USER_ID=?, PURPOSE_ID=?, STATUS=?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
			WHERE LAPTOP_SR_NO=?",
			array($get_user[0]->USER_ID, $input->purpose_id, 12, $this->webspice->get_user_id() , $this->webspice->now(),
			$input->laptop_sr)
			);
			
			# insert distribution
			$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
			$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID, USER_DESIGNATION, 
			PURPOSE_ID, CREATED_BY, CREATED_DATE, STATUS) VALUES(
			?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 12)", 
			array($new_squence+1,$get_user[0]->USER_ID, $get_laptop[0]->STOCK_ID, $input->user_designation, 
			$input->purpose_id, $this->webspice->get_user_id(), $this->webspice->now())
			);
			
			if ($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				echo 'failed';
				exit;
				
			}else{
				$this->db->trans_commit();
			}
			$this->db->trans_off();
			$this->webspice->log_me('laptop_assigned'); # log
			echo 'success';
			exit;
		}
		
		# default
		$this->load->view('laptop_operation/assign_laptop', $data);
	}
	
	function assign_laptop_batch($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data_batch = 50; # how much row(s) inserted once
		ini_set('MAX_EXECUTION_TIME', 300);
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'assign_laptop_batch');
		$this->webspice->permission_verify('assign_laptop');
		
		if( !$_FILES || !$_FILES['attached_file']['tmp_name'] ){
			$this->load->view('laptop_operation/assign_laptop_batch', $data);
			return FALSE;
		}

		# verify file type
		if( $_FILES['attached_file']['tmp_name'] ){
			$this->webspice->check_file_type(array('csv','xls'), 'attached_file', $data, 'laptop_operation/assign_laptop_batch');   
		}
		
		# verify duplicate file
		$get_bundle_file_sql = "SELECT * FROM TBL_DISTRIBUTION_FILE WHERE FILE_NAME = ?";
		$get_bundle_file = $this->db->query($get_bundle_file_sql, array($_FILES['attached_file']['name']))->result();
		if($get_bundle_file){
			$this->webspice->message_board('This file uploaded once. Please upload the correct file.');
			$this->webspice->force_redirect($url_prefix.'assign_laptop_batch');
			return FALSE;
		}

		# verify file type and read accordingly
		$get_data = array();
		if( $_FILES['attached_file']['type'] == 'application/vnd.ms-excel' || $_FILES['attached_file']['type'] == 'application/octet-stream' ){
			$get_data = $this->webspice->excel_reader($_FILES['attached_file']['tmp_name'], 0, array('Employee ID', 'User name', 'Email', 'Contact', 'Division', 'Department', 'Designation', 'User Type', 'Serial No', 'Purpose','Issue Date'));
		}elseif( $_FILES['attached_file']['type'] == 'text/csv' || $_FILES['attached_file']['type'] == 'text/comma-separated-values' ){
			
			$get_csv_data = $this->webspice->csv_reader($file_input_name='attached_file', array('Employee ID', 'User name', 'Email', 'Contact', 'Division', 'Department', 'Designation', 'User Type', 'Serial No', 'Purpose','Issue Date' ));
				
			if( !is_array($get_csv_data) ){
				$this->webspice->message_board($get_csv_data.' Please try again.');
				$this->webspice->force_redirect($url_prefix.'assign_laptop_batch');
				return FALSE;
			}
		
			# excel reader column offset starts from 1, that is way this has been started from 1
			# because all operations has been done using above offset serial
			$get_data = array();
			foreach($get_csv_data as $key => $value){
				$new_array = array();
				foreach($value as $key1=>$value1){
					$new_array[$key1+1] = trim($value1);
				}
			  $get_data[$key] = $new_array;
			}
			
		}else{
			echo 'File Invalid!';
			exit;
		}
		
		if( !is_array($get_data) ){
			$this->webspice->message_board($get_data.' Please try again.');
			$this->webspice->force_redirect($url_prefix.'assign_laptop_batch');
			return FALSE;
		}
		
		# verify data
		$data_error = null;
		foreach($get_data as $k=>$v){
			$data_list = $v;
			$Employee_id = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[1]));
			$User_name = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[2]));
			$Email = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[3]));
			$Contact = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[4]));
			$Division = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[5]));
			$Department = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[6]));
			$Designation = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[7]));
			$User_type = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[8]));
			$Serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper(strtoupper($data_list[9]))));
			$Porpuse = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[10]));
			$Issue_date = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[11]));
		
			# must have column value - column offset started from 1
			if( !isset($Employee_id) || !isset($User_name) || !isset($Division) ||  !isset($Department) ||  !isset($Designation) ||  !isset($User_type) || !isset($Serial_no) ||  !isset($Porpuse) || !isset($Issue_date) ){
				$data_error .= 'Row #'.$k.' is incomplete.<br />';
			}
			/*						
			#get AD user
			$search_filter = 'pager='.$Employee_id;
			$user = $this->webspice->get_ad_user('Administrators', 'administrator', 'nns@1212', $ldap_host='192.168.10.10', $ldap_dn='DC=nns-solution,DC=net', $base_dn='DC=nns-solution,DC=net', $ldap_user_domain='@nns-solution.net', $search_filter);
			*/	
			
		
			/*			
			# verify employee
			if( isset($Employee_id) && !$this->customcache->laptop_user_maker($Employee_id,'EMPLOYEE_ID') ){
				$data_error .= 'Employee ID "'.$Employee_id.'" at Row #'.$k.' is invalid.<br />';
			}


			# verify division
			if( isset($Division) && !$this->customcache->option_id_maker($Division,'OPTION_VALUE') ){
				$data_error .= 'Division "'.$Division.'" at Row #'.$k.' is invalid.<br />';
			}
			
			# verify department
			if( isset($Department) && !$this->customcache->option_id_maker($Department,'OPTION_VALUE') ){
				$data_error .= 'Department "'.$Department.'" at Row #'.$k.' is invalid.<br />';
			}
			
			# verify designation
			if( isset($Designation) && !$this->customcache->option_id_maker($Designation,'OPTION_VALUE') ){
				$data_error .= 'Designation "'.$Designation.'" at Row #'.$k.' is invalid.<br />';
			}
*/
			# verify user_type
			if( isset($User_type) && !$this->customcache->option_id_maker($User_type,'OPTION_VALUE') ){
				$data_error .= 'User Type "'.$User_type.'" at Row #'.$k.' is invalid ( new user type should be added to system ).<br />';
			}
			
			# verify laptop
			if( isset($Serial_no) && !$this->customcache->laptop_maker($Serial_no,'LAPTOP_SR_NO') ){
				$data_error .= 'Laptop Serial no "'.$Serial_no.'" at Row #'.$k.' is invalid (not in stock).<br />';
			}
			
			
			# verify laptop available
			if( isset($Serial_no) && $this->customcache->laptop_maker($Serial_no,'STATUS') != 11  ){
				$data_error .= 'Laptop Serial no "'.$Serial_no.'" at Row #'.$k.' is invalid (not available).<br />';
			}

			# verify purpose
			if( isset($Porpuse) && !$this->customcache->option_id_maker($Porpuse,'OPTION_VALUE') ){
				$data_error .= 'Purpose "'.$Porpuse.'" at Row #'.$k.' is invalid ( new porpose should be added to system ).<br />';
			}
			
			# verify issue date
			 if( isset($Issue_date) && !$this->webspice->isDate($Issue_date,'day', 'month', 'year') ){ 
			 	$data_error .= 'Issue Date "'.$Issue_date.'" at Row #'.$k.' is invalid.<br />'; 
			 } 
		}
		
		if($data_error){
			$data['error'] = $data_error.'<span class="fred fbold">Please update the file and try again.</span>';
			$this->load->view('laptop_operation/assign_laptop_batch', $data);
			return FALSE;
		}

		# insert data
		$this->db->trans_off();
		$this->db->trans_begin();
		
		$data_section = 0;
		$data_count = 1; # row offset started from 2
		$data_query_distribution_insert = array();
		
		foreach($get_data as $key=>$data_list){
			
			$new_squence_distribution = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
			$new_squence_distribution = $new_squence_distribution + 1;
			
			$new_squence_user = $this->webspice->getLastInserted('TBL_LAPTOP_USER','USER_ID');
			$new_squence_user = $new_squence_user + 1;

			$data_sheet = $get_data[$data_count];
			$Employee_id = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[1]));
			$User_name = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[2]));
			$Email = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[3]));
			$Contact = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[4]));
			$Division = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[5]));
			$Department = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[6]));
			$Designation = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[7]));
			$User_type = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[8]));
			$Serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper($data_list[9])));
			$Porpuse = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[10]));
			$Issue_date = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[11]));
	
					
					$user_result = $this->db->query("
						SELECT * FROM TBL_LAPTOP_USER
						WHERE EMPLOYEE_ID = '".$Employee_id."'
					")->result();
					
					if( !$user_result ){
						/*						
						#get AD user
						$search_filter = 'pager='.$Employee_id;
						$user = $this->webspice->get_ad_user('Administrators', 'administrator', 'nns@1212', $ldap_host='192.168.10.10', $ldap_dn='DC=nns-solution,DC=net', $base_dn='DC=nns-solution,DC=net', $ldap_user_domain='@nns-solution.net', $search_filter);
						
						$user=$user[0];
*/
						$sql = "
						INSERT INTO TBL_LAPTOP_USER
						(USER_ID, USER_NAME, USER_EMAIL, USER_PHONE, 
						USER_DIVISION, USER_DEPARTMENT, USER_DESIGNATION, USER_TYPE,
						CREATED_BY, CREATED_DATE, EMPLOYEE_ID, STATUS)
						VALUES
						(?, ?, ?, ?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'),?, 7)";
						$result = $this->db->query($sql, array($new_squence_user, $User_name, $Email, $Contact, $Division, $Department, $Designation, 
						$this->customcache->option_id_maker($User_type,'OPTION_ID'), 
						$this->webspice->get_user_id(), $this->webspice->now(), $Employee_id )
						);

				}else{
						$new_squence_user = $user_result[0]->USER_ID;
				}

					
					$this->db->query("
					UPDATE TBL_STOCK SET USER_ID=?, PURPOSE_ID=?, STATUS=?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss') 
					WHERE LAPTOP_SR_NO=?", array($new_squence_user, $this->customcache->option_id_maker($Porpuse,'OPTION_ID'), 12,$this->webspice->get_user_id(),$this->webspice->now(),$Serial_no));	
					
					$user_result = $this->db->query("
						SELECT * FROM TBL_STOCK
						WHERE LAPTOP_SR_NO = '".$Serial_no."'
					")->result();
					$user_result = $user_result[0];
					#dd($user_result);
					
					#dd($user_result);
					#array for distribution table insert
					$data_query_distribution_insert = "(
					".$new_squence_distribution.",
					".$new_squence_user.",
					".$user_result->STOCK_ID.",
					".$this->customcache->option_id_maker($Porpuse,'OPTION_ID').",
					'".$Designation."',
					".$this->webspice->get_user_id().",
					TO_DATE('".$Issue_date."','dd-mm-yyyy hh:mi:ss'),12)";

					$this->db->query("
					INSERT INTO TBL_DISTRIBUTION(ID, USER_ID, STOCK_ID, PURPOSE_ID, USER_DESIGNATION, CREATED_BY, CREATED_DATE, STATUS) 
					VALUES ".$data_query_distribution_insert);


		}

		# insert file name to stop duplicate upload
		$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION_FILE','FILE_ID');
		$this->db->query("INSERT INTO TBL_DISTRIBUTION_FILE(FILE_ID, FILE_NAME, UPLOAD_BY, UPLOAD_DATE) VALUES(?,?,?,TO_DATE(?,'yyyy-mm-dd hh:mi:ss'))", array($new_squence+1,$_FILES['attached_file']['name'], $this->webspice->get_user_id(), $this->webspice->now()));

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$this->webspice->message_board('We could not execute your request. Please try again or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else{
			$this->db->trans_commit();
		}
		$this->db->trans_off();
		$this->webspice->log_me('uploaded_assign_laptop_batch'); # log
		
		# remove cache
		$this->webspice->remove_cache('laptop');
		$this->webspice->remove_cache('laptop_user');
		
		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_assign_laptop', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_assign_laptop');
		}

		$this->webspice->force_redirect($url_prefix);
	}
	
	function manage_assign_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_assign_laptop');
		$this->webspice->permission_verify('assign_laptop');

		$this->load->database();
    $orderby = 'ORDER BY TBL_STOCK.UPDATED_DATE';
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
			TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE, TBL_DISTRIBUTION.CREATED_DATE AS ASSIGN_DATE 
			FROM TBL_STOCK 
			INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID 
			INNER JOIN TBL_DISTRIBUTION ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
		";

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array('TBL_LAPTOP_USER.USER_DESIGNATION', 'TBL_LAPTOP_USER.USER_TYPE'), 
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL','TBL_STOCK.PURCHASE_DATE','TBL_LAPTOP_USER.EMPLOYEE_ID','TBL_LAPTOP_USER.USER_NAME', 'TBL_LAPTOP_USER.USER_EMAIL','TBL_LAPTOP_USER.USER_PHONE', 'TBL_LAPTOP_USER.USER_DEPARTMENT', 'TBL_LAPTOP_USER.USER_DESIGNATION'),
			$AdditionalWhere = ' TBL_STOCK.STATUS = 12 ',
			$DateBetween = array('TBL_STOCK.UPDATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('laptop_operation/print_assign_laptop',$data);
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

		$this->load->view('laptop_operation/manage_assign_laptop', $data);
	}
	
	#lost Laptop
	function lost_laptop($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'lost_laptop');
		$this->webspice->permission_verify('lost_laptop');

		# get input post
		$input = $this->webspice->get_input();
		
		# ajax call
		if( isset($input->ajax_call) && $input->ajax_call ){
			$html = 'no_data';
			switch( $input->action_type ){
				case 'get_laptop':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
					TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
					TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE, TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID
					INNER JOIN TBL_DISTRIBUTION ON TBL_STOCK.USER_ID=TBL_DISTRIBUTION.USER_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 12 ){
						# STATUS 12 = ASSIGN
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$html = '';
					$html .= '<table class="table table-bordered table-striped">';
						$html .= '<tr><th colspan=3><h4>Employee Information</h4></th><th colspan=3><h4>Laptop Information</h4></th></tr>';
						$html .= '<tr><td><b>Employee ID</b></td><td width="10" align="center">:</td><td>'.$result->EMPLOYEE_ID.'</td><td><b>Brand</b></td><td width="10" align="center">:</td><td>'.$result->BRAND.'</td></tr>';
						$html .= '<tr><td><b>User Name</b></td><td width="10" align="center">:</td><td>'.$result->USER_NAME.'</td><td><b>Model</b></td><td width="10" align="center">:</td><td>'.$result->MODEL.'</td></tr>';
						$html .= '<tr><td><b>User Division</b></td><td width="10" align="center">:</td><td>'.$result->USER_DIVISION.'</td><td><b>PO NO</b></td><td width="10" align="center">:</td><td>'.$result->PO_NO.'</td></tr>';
						$html .= '<tr><td><b>User Department</b></td><td width="10" align="center">:</td><td>'.$result->USER_DEPARTMENT.'</td><td><b>Purchase Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->PURCHASE_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Designation</b></td><td width="10" align="center">:</td><td>'.$result->USER_DESIGNATION.'</td><td><b>EOL Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->EOL_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Type</b></td><td width="10" align="center">:</td><td>'.$this->customcache->employee_type_maker($result->USER_TYPE,'OPTION_VALUE').'</td><td><b>Issue Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->UPDATED_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Email</b></td><td width="10" align="center">:</td><td>'.$result->USER_EMAIL.'</td><td><b>Assigned By</b></td><td width="10" align="center">:</td><td>'.$this->customcache->user_maker($result->UPDATED_BY,'USER_NAME').'</td></tr>';
						$html .= '<tr><td><b>User Phone</b></td><td width="10" align="center">:</td><td>'.$result->USER_PHONE.'</td><td><b>Purpose</b></td><td width="10" align="center">:</td><td>'.$this->customcache->purpose_maker($result->PURPOSE_ID,'OPTION_VALUE').'</td></tr>';
					$html .= '</table>';
					break;
					
				case 'change_laptop_status':
					
					$data_id = $input->data_id;
					
					$result = $this->db->query("
					SELECT TBL_STOCK.*, TBL_DISTRIBUTION.USER_DESIGNATION,TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_DISTRIBUTION ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 12 ){
						# STATUS 12 = ASSIGN
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$sql = "
					UPDATE TBL_STOCK
					SET TBL_STOCK.STATUS	=	?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?";
					$update = $this->db->query($sql,array(14, $this->webspice->get_user_id() , $this->webspice->now(), $input->data_id));
					
					# insert distribution
					$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
					$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID, USER_DESIGNATION, 
					PURPOSE_ID, CREATED_BY, CREATED_DATE, STATUS) VALUES(
					?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 14)", 
					array($new_squence+1,$result->USER_ID, $result->STOCK_ID, $result->USER_DESIGNATION, 
					$result->PURPOSE_ID, $this->webspice->get_user_id(), $this->webspice->now())
					);
					
					if ($this->db->trans_status() === FALSE){
						$this->db->trans_rollback();
						echo 'failed';
						exit;
						
					}else{
						$this->db->trans_commit();
					}
					$this->db->trans_off();
					
					if( $update ){
						$this->webspice->log_me('a_lost_laptop_added'); # log
						$html = 'update_success';
						echo $html;
						exit;
					}
					
					echo $html;
					break;
			}
			
			echo $html;
			exit;
		}

		# default
		$this->load->view('laptop_operation/lost_laptop', $data);
	}
	
	function manage_lost_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_lost_laptop');
		$this->webspice->permission_verify('lost_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.UPDATED_DATE';
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

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array(), 
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL','TBL_STOCK.PURCHASE_DATE','TBL_LAPTOP_USER.USER_NAME','TBL_LAPTOP_USER.EMPLOYEE_ID','TBL_LAPTOP_USER.USER_EMAIL'),
			$AdditionalWhere = ' TBL_STOCK.STATUS = 14 ',
			$DateBetween = array('TBL_STOCK.UPDATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('laptop_operation/print_lost_laptop',$data);
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

		$this->load->view('laptop_operation/manage_lost_laptop', $data);
	}
	
	#NOC with personalized
	function noc_with_personalized($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;

		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'noc_with_personalised');
		$this->webspice->permission_verify('noc_with_personalised');

		# get input post
		$input = $this->webspice->get_input();
		
		# ajax call
		if( isset($input->ajax_call) && $input->ajax_call ){
			$html = 'no_data';
			switch( $input->action_type ){
			
				case 'get_laptop':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
					TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
					TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE, TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID
					INNER JOIN TBL_DISTRIBUTION ON TBL_STOCK.USER_ID=TBL_DISTRIBUTION.USER_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 12 ){
						# STATUS 12 = ASSIGN
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$html = '';
					$html .= '<table class="table table-bordered table-striped">';
						$html .= '<tr><th colspan=3><h4>Employee Information</h4></th><th colspan=3><h4>Laptop Information</h4></th></tr>';
						$html .= '<tr><td><b>Employee ID</b></td><td width="10" align="center">:</td><td>'.$result->EMPLOYEE_ID.'</td><td><b>Brand</b></td><td width="10" align="center">:</td><td>'.$result->BRAND.'</td></tr>';
						$html .= '<tr><td><b>User Name</b></td><td width="10" align="center">:</td><td>'.$result->USER_NAME.'</td><td><b>Model</b></td><td width="10" align="center">:</td><td>'.$result->MODEL.'</td></tr>';
						$html .= '<tr><td><b>User Division</b></td><td width="10" align="center">:</td><td>'.$result->USER_DIVISION.'</td><td><b>PO NO</b></td><td width="10" align="center">:</td><td>'.$result->PO_NO.'</td></tr>';
						$html .= '<tr><td><b>User Department</b></td><td width="10" align="center">:</td><td>'.$result->USER_DEPARTMENT.'</td><td><b>Purchase Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->PURCHASE_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Designation</b></td><td width="10" align="center">:</td><td>'.$result->USER_DESIGNATION.'</td><td><b>EOL Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->EOL_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Type</b></td><td width="10" align="center">:</td><td>'.$this->customcache->employee_type_maker($result->USER_TYPE,'OPTION_VALUE').'</td><td><b>Issue Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->UPDATED_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Email</b></td><td width="10" align="center">:</td><td>'.$result->USER_EMAIL.'</td><td><b>Assigned By</b></td><td width="10" align="center">:</td><td>'.$this->customcache->user_maker($result->UPDATED_BY,'USER_NAME').'</td></tr>';
						$html .= '<tr><td><b>User Phone</b></td><td width="10" align="center">:</td><td>'.$result->USER_PHONE.'</td><td><b>Purpose</b></td><td width="10" align="center">:</td><td>'.$this->customcache->purpose_maker($result->PURPOSE_ID,'OPTION_VALUE').'</td></tr>';
					$html .= '</table>';
					break;
					
				case 'change_laptop_status':
					$data_id = $input->data_id;
					
					$result = $this->db->query("
					SELECT TBL_STOCK.*,TBL_DISTRIBUTION.USER_DESIGNATION,TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_DISTRIBUTION ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 12 ){
						# STATUS 12 = ASSIGN
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$sql = "
					UPDATE TBL_STOCK
					SET TBL_STOCK.STATUS	=	?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?";
					$update = $this->db->query($sql,array(15, $this->webspice->get_user_id() , $this->webspice->now(), $input->data_id));
					
					# insert distribution
					$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
					$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID, USER_DESIGNATION, 
					PURPOSE_ID, CREATED_BY, CREATED_DATE, STATUS) VALUES(
					?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 15)", 
					array($new_squence+1,$result->USER_ID, $result->STOCK_ID, $result->USER_DESIGNATION, 
					$result->PURPOSE_ID, $this->webspice->get_user_id(), $this->webspice->now())
					);
					
					if ($this->db->trans_status() === FALSE){
						$this->db->trans_rollback();
						echo 'failed';
						exit;
						
					}else{
						$this->db->trans_commit();
					}
					$this->db->trans_off();
		
					if( $update ){
						$this->webspice->log_me('noc_with_laptop_added'); # log
						$html = 'update_success';
						echo $html;
						exit;
					}
					
					echo $html;
					break;
			}
			
			echo $html;
			exit;
		}

		# default
		$this->load->view('laptop_operation/noc_with_personalized', $data);
	}
	
	function manage_noc_with_personalized(){
		#$this->db->query("TRUNCATE TABLE TBL_STOCK");
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_noc_with_personalized');
		$this->webspice->permission_verify('noc_with_personalized');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.UPDATED_DATE ';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 15 ';
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

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array(), 
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL','TBL_STOCK.PURCHASE_DATE','TBL_LAPTOP_USER.USER_NAME','TBL_LAPTOP_USER.EMPLOYEE_ID','TBL_LAPTOP_USER.USER_EMAIL'),
			$AdditionalWhere = ' TBL_STOCK.STATUS = 15 ',
			$DateBetween = array('TBL_STOCK.UPDATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('laptop_operation/print_noc_with_personalized',$data);
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

		$this->load->view('laptop_operation/manage_noc_with_personalized', $data);
	}
	
	#NOC without personalized
	function noc_without_personalized($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'noc_without_personalised');
		$this->webspice->permission_verify('noc_without_personalised');

		# get input post
		$input = $this->webspice->get_input();
		
		# ajax call
		if( isset($input->ajax_call) && $input->ajax_call ){
			$html = 'no_data';
			switch( $input->action_type ){
			
				case 'get_laptop':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
					TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
					TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE, TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID
					INNER JOIN TBL_DISTRIBUTION ON TBL_STOCK.USER_ID=TBL_DISTRIBUTION.USER_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 12 ){
						# STATUS 12 = ASSIGN
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$html = '';
					$html .= '<table class="table table-bordered table-striped">';
						$html .= '<tr><th colspan=3><h4>Employee Information</h4></th><th colspan=3><h4>Laptop Information</h4></th></tr>';
						$html .= '<tr><td><b>Employee ID</b></td><td width="10" align="center">:</td><td>'.$result->EMPLOYEE_ID.'</td><td><b>Brand</b></td><td width="10" align="center">:</td><td>'.$result->BRAND.'</td></tr>';
						$html .= '<tr><td><b>User Name</b></td><td width="10" align="center">:</td><td>'.$result->USER_NAME.'</td><td><b>Model</b></td><td width="10" align="center">:</td><td>'.$result->MODEL.'</td></tr>';
						$html .= '<tr><td><b>User Division</b></td><td width="10" align="center">:</td><td>'.$result->USER_DIVISION.'</td><td><b>PO NO</b></td><td width="10" align="center">:</td><td>'.$result->PO_NO.'</td></tr>';
						$html .= '<tr><td><b>User Department</b></td><td width="10" align="center">:</td><td>'.$result->USER_DEPARTMENT.'</td><td><b>Purchase Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->PURCHASE_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Designation</b></td><td width="10" align="center">:</td><td>'.$result->USER_DESIGNATION.'</td><td><b>EOL Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->EOL_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Type</b></td><td width="10" align="center">:</td><td>'.$this->customcache->employee_type_maker($result->USER_TYPE,'OPTION_VALUE').'</td><td><b>Issue Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->UPDATED_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Email</b></td><td width="10" align="center">:</td><td>'.$result->USER_EMAIL.'</td><td><b>Assigned By</b></td><td width="10" align="center">:</td><td>'.$this->customcache->user_maker($result->UPDATED_BY,'USER_NAME').'</td></tr>';
						$html .= '<tr><td><b>User Phone</b></td><td width="10" align="center">:</td><td>'.$result->USER_PHONE.'</td><td><b>Purpose</b></td><td width="10" align="center">:</td><td>'.$this->customcache->purpose_maker($result->PURPOSE_ID,'OPTION_VALUE').'</td></tr>';
					$html .= '</table>';
					break;
					
				case 'change_laptop_status':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.*,TBL_DISTRIBUTION.USER_DESIGNATION,TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_DISTRIBUTION ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 12 ){
						# STATUS 12 = ASSIGN
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$sql = "
					UPDATE TBL_STOCK
					SET TBL_STOCK.STATUS	=	?, USER_ID = ?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?";
					$update = $this->db->query($sql,array(11, '',$this->webspice->get_user_id() , $this->webspice->now(), $input->data_id));
					
					# insert distribution
					$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
					$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID, USER_DESIGNATION, 
					PURPOSE_ID, CREATED_BY, CREATED_DATE, STATUS) VALUES(
					?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 16)", 
					array($new_squence+1,$result->USER_ID, $result->STOCK_ID, $result->USER_DESIGNATION, 
					$result->PURPOSE_ID, $this->webspice->get_user_id(), $this->webspice->now())
					);
					
					if ($this->db->trans_status() === FALSE){
						$this->db->trans_rollback();
						echo 'failed';
						exit;
						
					}else{
						$this->db->trans_commit();
					}
					$this->db->trans_off();

					if( $update ){
						$this->webspice->log_me('noc_without_laptop_added'); # log
						$html = 'update_success';
						echo $html;
						exit;
					}
					
					echo $html;
					break;
			}
			
			echo $html;
			exit;
		}

		# default
		$this->load->view('laptop_operation/noc_without_personalized', $data);
	}
	
	function manage_noc_without_personalized(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_noc_without_personalized');
		$this->webspice->permission_verify('noc_without_personalized');

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

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_DISTRIBUTION', 
			$InputField = array(), 
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL','TBL_STOCK.PURCHASE_DATE','TBL_LAPTOP_USER.USER_NAME','TBL_LAPTOP_USER.EMPLOYEE_ID','TBL_LAPTOP_USER.USER_EMAIL'),
			$AdditionalWhere = 'TBL_DISTRIBUTION.STATUS = 16',
			$DateBetween = array('TBL_DISTRIBUTION.CREATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('laptop_operation/print_noc_without_personalized',$data);
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

		$this->load->view('laptop_operation/manage_noc_without_personalized', $data);
	}
	
	#broken and fault laptop
	function broken_fault_laptop($data=null){

		$url_prefix = $this->webspice->settings()->site_url_prefix;

		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'broken_laptop');
		$this->webspice->permission_verify('broken_laptop');

		# get input post
		$input = $this->webspice->get_input();
		
		# ajax call
		if( isset($input->ajax_call) && $input->ajax_call ){
			$html = 'no_data';
			switch( $input->action_type ){
			
					
				case 'get_laptop':
					
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
					TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
					TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE, TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID
					INNER JOIN TBL_DISTRIBUTION ON TBL_STOCK.USER_ID=TBL_DISTRIBUTION.USER_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 12 ){
						# STATUS 12 = ASSIGN
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$html = '';
					$html .= '<table class="table table-bordered table-striped">';
						$html .= '<tr><th colspan=3><h4>Employee Information</h4></th><th colspan=3><h4>Laptop Information</h4></th></tr>';
						$html .= '<tr><td><b>Employee ID</b></td><td width="10" align="center">:</td><td>'.$result->EMPLOYEE_ID.'</td><td><b>Brand</b></td><td width="10" align="center">:</td><td>'.$result->BRAND.'</td></tr>';
						$html .= '<tr><td><b>User Name</b></td><td width="10" align="center">:</td><td>'.$result->USER_NAME.'</td><td><b>Model</b></td><td width="10" align="center">:</td><td>'.$result->MODEL.'</td></tr>';
						$html .= '<tr><td><b>User Division</b></td><td width="10" align="center">:</td><td>'.$result->USER_DIVISION.'</td><td><b>PO NO</b></td><td width="10" align="center">:</td><td>'.$result->PO_NO.'</td></tr>';
						$html .= '<tr><td><b>User Department</b></td><td width="10" align="center">:</td><td>'.$result->USER_DEPARTMENT.'</td><td><b>Purchase Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->PURCHASE_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Designation</b></td><td width="10" align="center">:</td><td>'.$result->USER_DESIGNATION.'</td><td><b>EOL Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->EOL_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Type</b></td><td width="10" align="center">:</td><td>'.$this->customcache->employee_type_maker($result->USER_TYPE,'OPTION_VALUE').'</td><td><b>Issue Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->UPDATED_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Email</b></td><td width="10" align="center">:</td><td>'.$result->USER_EMAIL.'</td><td><b>Assigned By</b></td><td width="10" align="center">:</td><td>'.$this->customcache->user_maker($result->UPDATED_BY,'USER_NAME').'</td></tr>';
						$html .= '<tr><td><b>User Phone</b></td><td width="10" align="center">:</td><td>'.$result->USER_PHONE.'</td><td><b>Purpose</b></td><td width="10" align="center">:</td><td>'.$this->customcache->purpose_maker($result->PURPOSE_ID,'OPTION_VALUE').'</td></tr>';
					$html .= '</table>';
					break;
					
				case 'change_laptop_status':
					
					
					$data_id = $input->data_id;
					
					$result = $this->db->query("
					SELECT TBL_STOCK.*,TBL_DISTRIBUTION.USER_DESIGNATION,TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_DISTRIBUTION ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 12 ){
						# STATUS 12 = ASSIGN
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$sql = "
					UPDATE TBL_STOCK
					SET TBL_STOCK.STATUS	=	?, USER_ID = ?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?";
					$update = $this->db->query($sql,array(13, $result->USER_ID, $this->webspice->get_user_id() , $this->webspice->now(), $input->data_id));
					
					# insert distribution
					$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
					$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID, USER_DESIGNATION, 
					PURPOSE_ID, CREATED_BY, CREATED_DATE, STATUS) VALUES(
					?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 13)", 
					array($new_squence+1,$result->USER_ID, $result->STOCK_ID, $result->USER_DESIGNATION, 
					$result->PURPOSE_ID, $this->webspice->get_user_id(), $this->webspice->now())
					);
					
					if ($this->db->trans_status() === FALSE){
						$this->db->trans_rollback();
						echo 'failed';
						exit;
						
					}else{
						$this->db->trans_commit();
					}
					$this->db->trans_off();

					if( $update ){
						$this->webspice->log_me('added_broken_faulty_laptop'); # log
						$html = 'update_success';
						echo $html;
						exit;
					}
					
					echo $html;
					break;
			}
			
			echo $html;
			exit;
		}
		
		# default
		$this->load->view('laptop_operation/broken_fault_laptop', $data);
	}
	
	function manage_broken_fault_laptop(){
		#$this->db->query("TRUNCATE TABLE TBL_STOCK");
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_broken_fault_laptop');
		$this->webspice->permission_verify('broken_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.UPDATED_DATE ';
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

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array(), 
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL','TBL_STOCK.PURCHASE_DATE','TBL_LAPTOP_USER.USER_NAME','TBL_LAPTOP_USER.EMPLOYEE_ID','TBL_LAPTOP_USER.USER_EMAIL'),
			$AdditionalWhere = ' TBL_STOCK.STATUS = 13 ',
			$DateBetween = array('UPDATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('laptop_operation/print_broken_fault_laptop',$data);
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

		$this->load->view('laptop_operation/manage_broken_fault_laptop', $data);
	}
	
	#service laptop
	function service_laptop($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'service_laptop');
		$this->webspice->permission_verify('service_laptop');

		# get input post
		$input = $this->webspice->get_input();
		
		# ajax call
		if( isset($input->ajax_call) && $input->ajax_call ){
			$html = 'no_data';
			switch( $input->action_type ){
				case 'get_laptop':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME, TBL_LAPTOP_USER.EMPLOYEE_ID, TBL_LAPTOP_USER.USER_EMAIL, 
					TBL_LAPTOP_USER.USER_PHONE, TBL_LAPTOP_USER.USER_DEPARTMENT, TBL_LAPTOP_USER.USER_DESIGNATION, 
					TBL_LAPTOP_USER.USER_DIVISION, TBL_LAPTOP_USER.USER_TYPE, TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID
					INNER JOIN TBL_DISTRIBUTION ON TBL_STOCK.USER_ID=TBL_DISTRIBUTION.USER_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 13 ){
						# STATUS 13 = BROKEN/FAULTY
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$html = '';
					$html .= '<table class="table table-bordered table-striped">';
						$html .= '<tr><th colspan=3><h4>Employee Information</h4></th><th colspan=3><h4>Laptop Information</h4></th></tr>';
						$html .= '<tr><td><b>Employee ID</b></td><td width="10" align="center">:</td><td>'.$result->EMPLOYEE_ID.'</td><td><b>Brand</b></td><td width="10" align="center">:</td><td>'.$result->BRAND.'</td></tr>';
						$html .= '<tr><td><b>User Name</b></td><td width="10" align="center">:</td><td>'.$result->USER_NAME.'</td><td><b>Model</b></td><td width="10" align="center">:</td><td>'.$result->MODEL.'</td></tr>';
						$html .= '<tr><td><b>User Division</b></td><td width="10" align="center">:</td><td>'.$result->USER_DIVISION.'</td><td><b>PO NO</b></td><td width="10" align="center">:</td><td>'.$result->PO_NO.'</td></tr>';
						$html .= '<tr><td><b>User Department</b></td><td width="10" align="center">:</td><td>'.$result->USER_DEPARTMENT.'</td><td><b>Purchase Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->PURCHASE_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Designation</b></td><td width="10" align="center">:</td><td>'.$result->USER_DESIGNATION.'</td><td><b>EOL Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->EOL_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Type</b></td><td width="10" align="center">:</td><td>'.$this->customcache->employee_type_maker($result->USER_TYPE,'OPTION_VALUE').'</td><td><b>Issue Date</b></td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->UPDATED_DATE).'</td></tr>';
						$html .= '<tr><td><b>User Email</b></td><td width="10" align="center">:</td><td>'.$result->USER_EMAIL.'</td><td><b>Assigned By</b></td><td width="10" align="center">:</td><td>'.$this->customcache->user_maker($result->UPDATED_BY,'USER_NAME').'</td></tr>';
						$html .= '<tr><td><b>User Phone</b></td><td width="10" align="center">:</td><td>'.$result->USER_PHONE.'</td><td><b>Purpose</b></td><td width="10" align="center">:</td><td>'.$this->customcache->purpose_maker($result->PURPOSE_ID,'OPTION_VALUE').'</td></tr>';
					$html .= '</table>';
					break;
					
				case 'change_laptop_status':
					
					$data_id = $input->data_id;
					
					$result = $this->db->query("
					SELECT TBL_STOCK.*,TBL_DISTRIBUTION.USER_DESIGNATION,TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_DISTRIBUTION ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 13 ){
						# STATUS 13 = BROKEN/FAULTY
						$html = 'not_available';
						echo $html;
						exit;
					}
							
					$sql = "
					UPDATE TBL_STOCK
					SET TBL_STOCK.STATUS	=	?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?";
					$update = $this->db->query($sql,array(17, $this->webspice->get_user_id() , $this->webspice->now(), $input->data_id));
					
					# insert distribution
					$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
					$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID, USER_DESIGNATION, 
					PURPOSE_ID, CREATED_BY, CREATED_DATE, STATUS) VALUES(
					?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 17)", 
					array($new_squence+1,$result->USER_ID, $result->STOCK_ID, $result->USER_DESIGNATION, 
					$result->PURPOSE_ID, $result->USER_ID, $this->webspice->now())
					);
					
					if ($this->db->trans_status() === FALSE){
						$this->db->trans_rollback();
						echo 'failed';
						exit;
						
					}else{
						$this->db->trans_commit();
					}
					$this->db->trans_off();
		
					if( $update ){
						$this->webspice->log_me('laptop_on_servicing'); # log
						$html = 'update_success';
						echo $html;
						exit;
					}
					
					echo $html;
					break;
			}
			
			echo $html;
			exit;
		}

		# default
		$this->load->view('laptop_operation/service_laptop', $data);
	}
	
	function manage_service_laptop(){
		#$this->db->query("TRUNCATE TABLE TBL_STOCK");
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_service_laptop');
		$this->webspice->permission_verify('service_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.UPDATED_DATE ';
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

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array(), 
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL','TBL_STOCK.PURCHASE_DATE','TBL_LAPTOP_USER.USER_NAME','TBL_LAPTOP_USER.EMPLOYEE_ID','TBL_LAPTOP_USER.USER_EMAIL'),
			$AdditionalWhere = ' TBL_STOCK.STATUS = 17 ',
			$DateBetween = array('TBL_STOCK.UPDATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('laptop_operation/print_service_laptop',$data);
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
		$this->load->view('laptop_operation/manage_service_laptop', $data);
	}
	
	#return Laptop
	function return_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'return_laptop');
		$this->webspice->permission_verify('service_laptop');

		# get input post
		$input = $this->webspice->get_input();

		if( isset($input->hdn_laptop_sr) ){
					$data_id = $input->hdn_laptop_sr;
					$result1 = $this->db->query("
					SELECT TBL_STOCK.*
					FROM TBL_STOCK
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result1 ){
							$this->webspice->message_board('Laptop not found!');
							$this->webspice->force_redirect($url_prefix.'manage_service_laptop');
							return false;
					}
					
					$result1 = $result1[0];
					if( $result1->STATUS != 17 ){
						# STATUS 17 = ON SERVICING
							$this->webspice->message_board('Laptop not found!');
							$this->webspice->force_redirect($url_prefix.'manage_service_laptop');
							return false;
					}

					$data_id = $input->hdn_laptop_sr;
					
					$result2 = $this->db->query("
					SELECT TBL_STOCK.*,TBL_DISTRIBUTION.USER_DESIGNATION,TBL_DISTRIBUTION.PURPOSE_ID
					FROM TBL_STOCK
					INNER JOIN TBL_DISTRIBUTION ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					
					if( !$result2 ){
							$this->webspice->message_board('Laptop not found!');
							$this->webspice->force_redirect($url_prefix.'manage_service_laptop');
							return false;
					}
					
					$result2 = $result2[0];
					if( $result2->STATUS != 17 ){
						# STATUS 17 = ON SERVICING
							$this->webspice->message_board('Laptop not found!');
							$this->webspice->force_redirect($url_prefix.'manage_service_laptop');
							return false;
					}
					
					if( isset($input->status_from_service) && $input->status_from_service == 11 ){
								$sql = "
								UPDATE TBL_STOCK
								SET TBL_STOCK.STATUS	=	?, USER_ID	=	?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
								WHERE TBL_STOCK.LAPTOP_SR_NO = ?";
								$update = $this->db->query($sql,array(11, '', $this->webspice->get_user_id() , $this->webspice->now(), $input->hdn_laptop_sr));
								
								# insert distribution
								$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
								$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID, USER_DESIGNATION, 
								PURPOSE_ID, CREATED_BY, CREATED_DATE, STATUS) VALUES(
								?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 18)", 
								array($new_squence+1,$result2->USER_ID, $result2->STOCK_ID, $result2->USER_DESIGNATION, 
								$result2->PURPOSE_ID, $result2->USER_ID, $this->webspice->now())
								);
								
								$this->webspice->log_me('laptop_return_from_servicing_as_assignable'); # log
	
						}elseif( isset($input->status_from_service) && $input->status_from_service == 13){
								$sql = "
								UPDATE TBL_STOCK
								SET TBL_STOCK.STATUS	=	?, USER_ID	=	?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
								WHERE TBL_STOCK.LAPTOP_SR_NO = ?";
								$update = $this->db->query($sql,array(13, $result2->USER_ID, $this->webspice->get_user_id() , $this->webspice->now(), $input->hdn_laptop_sr));
								
								# insert distribution
								$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
								$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID, USER_DESIGNATION, 
								PURPOSE_ID, CREATED_BY, CREATED_DATE, STATUS) VALUES(
								?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), -18)", 
								array($new_squence+1,$result2->USER_ID, $result2->STOCK_ID, $result2->USER_DESIGNATION, 
								$result2->PURPOSE_ID, $result2->USER_ID, $this->webspice->now())
								);
								
								$this->webspice->log_me('laptop_return_from_servicing_as_broken_and_faulty'); # log
								
						}else{
							$this->webspice->message_board('Laptop not found!');
							$this->webspice->force_redirect($url_prefix.'manage_service_laptop');
							return false;		
						}
						

			}
			$this->webspice->force_redirect($url_prefix.'manage_service_laptop');
	}
	
	function manage_return_laptop(){
		#$this->db->query("TRUNCATE TABLE TBL_STOCK");
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_return_laptop');
		$this->webspice->permission_verify('service_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.STOCK_ID ASC';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 18 AND ROWNUM <= 50';
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
		SELECT TBL_STOCK.*, TBL_LAPTOP_USER.USER_NAME
		FROM TBL_STOCK
		LEFT JOIN TBL_LAPTOP_USER ON TBL_STOCK.USER_ID = TBL_LAPTOP_USER.USER_ID
		";

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array(), 
			$Keyword = array('LAPTOP_SR_NO','BRAND','MODEL','PURCHASE_DATE'),
			$AdditionalWhere = 'TBL_STOCK.STATUS = 18',
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
			
				$this->load->view('laptop_operation/print_service_laptop',$data);
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

		$this->load->view('laptop_operation/manage_service_laptop', $data);
	}
	
	#replace Laptop
	function send_to_replace_laptop($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'send_to_replace_laptop');
		$this->webspice->permission_verify('replace_laptop');

		# get input post
		$input = $this->webspice->get_input();
		
		# ajax call
		if( isset($input->ajax_call) && $input->ajax_call ){
			
			$html = 'no_data';
			switch( $input->action_type ){
				case 'get_laptop':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.*
					FROM TBL_STOCK
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 11 && $result->STATUS != 12 && $result->STATUS != 13 && $result->STATUS != 17 ){
						# STATUS 12 = ASSIGN, STATUS 11 = AVAILABLE, STATUS 13 = BROKEN/FAULTY, STATUS 17 = ON SERVICING
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$html = '<h2>Laptop Information</h2>';
					$html .= '<table class="table table-bordered table-striped">';
						$html .= '<tr><td>Brand</td><td width="10" align="center">:</td><td>'.$result->BRAND.'</td></tr>';
						$html .= '<tr><td>Model</td><td width="10" align="center">:</td><td>'.$result->MODEL.'</td></tr>';
						$html .= '<tr><td>PO NO.</td><td width="10" align="center">:</td><td>'.$result->PO_NO.'</td></tr>';
						$html .= '<tr><td>Purchase Date</td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->PURCHASE_DATE).'</td></tr>';
						$html .= '<tr><td>EOL Date</td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->EOL_DATE).'</td></tr>';
						$html .= '<tr><td>Status</td><td width="10" align="center">:</td><td>'.$this->webspice->static_status($result->STATUS).'</td></tr>';
					$html .= '</table>';
					break;
					
				case 'change_laptop_status':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.* FROM TBL_STOCK WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 11 && $result->STATUS != 12 && $result->STATUS != 13 ){
						# STATUS 12 = ASSIGN, STATUS 11 = AVAILABLE, STATUS 13 = BROKEN/FAULTY, STATUS 17 = ON SERVICING
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$sql = "
					UPDATE TBL_STOCK
					SET TBL_STOCK.STATUS	=	?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?";
					$update = $this->db->query($sql,array(21, $this->webspice->get_user_id() , $this->webspice->now(), $input->data_id));
					
					# insert distribution
					$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
					$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID, CREATED_BY, CREATED_DATE, STATUS) VALUES(
					?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), 21)", 
					array($new_squence+1,$result->USER_ID, $result->STOCK_ID, $this->webspice->get_user_id(), $this->webspice->now())
					);
					
					if ($this->db->trans_status() === FALSE){
						$this->db->trans_rollback();
						echo 'failed';
						exit;
						
					}else{
						$this->db->trans_commit();
					}
					$this->db->trans_off();
					
					if( $update ){
						$this->webspice->log_me('laptop_send_to_replace'); # log
						$html = 'update_success';
						echo $html;
						exit;
					}
					
					echo $html;
					break;
			}
			
			echo $html;
			exit;
		}

		# default
		$this->load->view('laptop_operation/send_to_replace_laptop', $data);
	}
	
	function replace_laptop($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'replace_laptop');
		$this->webspice->permission_verify('replace_laptop');

		# get input post
		$input = $this->webspice->get_input();

		# ajax call
		if( isset($input->ajax_call) && $input->ajax_call ){
			$html = 'no_data';
			switch( $input->action_type ){
				case 'get_laptop':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.*
					FROM TBL_STOCK
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 21 ){
						# STATUS 21 ON REPLACING
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$html = '<h2>Laptop Information</h2>';
					$html .= '<table class="table table-bordered table-striped">';
						$html .= '<tr><td>Brand</td><td width="10" align="center">:</td><td>'.$result->BRAND.'</td></tr>';
						$html .= '<tr><td>Model</td><td width="10" align="center">:</td><td>'.$result->MODEL.'</td></tr>';
						$html .= '<tr><td>PO NO.</td><td width="10" align="center">:</td><td>'.$result->PO_NO.'</td></tr>';
						$html .= '<tr><td>Purchase Date</td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->PURCHASE_DATE).'</td></tr>';
						$html .= '<tr><td>EOL Date</td><td width="10" align="center">:</td><td>'.$this->webspice->formatted_date($result->EOL_DATE).'</td></tr>';
						$html .= '<tr><td>Status</td><td width="10" align="center">:</td><td>'.$this->webspice->static_status($result->STATUS).'</td></tr>';
					$html .= '</table>';
					break;
					
				case 'change_laptop_status':
					$data_id = $input->data_id;
					$result = $this->db->query("
					SELECT TBL_STOCK.* FROM TBL_STOCK WHERE TBL_STOCK.LAPTOP_SR_NO = ?
					", array($data_id))->result();
					if( !$result ){
						$html = 'no_data';
						echo $html;
						exit;
					}
					
					$result = $result[0];
					if( $result->STATUS != 21 ){
						# STATUS 21 ON REPLACING
						$html = 'not_available';
						echo $html;
						exit;
					}
					
					$sql = "
					UPDATE TBL_STOCK
					SET TBL_STOCK.STATUS	=	?, TBL_STOCK.LAPTOP_PREVIOUS_SR_NO = ?, TBL_STOCK.LAPTOP_SR_NO = ?, TBL_STOCK.USER_ID = ?, 
					TBL_STOCK.REPLACE_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss')
					WHERE TBL_STOCK.LAPTOP_SR_NO = ?";
					$update = $this->db->query($sql,array(11,$input->data_id,$input->new_laptop_sr,'', $this->webspice->now(), $this->webspice->get_user_id() , $this->webspice->now(), $input->data_id));
					
					# insert distribution
					$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION','ID');
					$this->db->query("INSERT INTO TBL_DISTRIBUTION(ID,USER_ID, STOCK_ID,
					CREATED_BY, CREATED_DATE, LAPTOP_PREVIOUS_SR_NO, STATUS) VALUES(
					?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh:mi:ss'),?, 19)", 
					array($new_squence+1,$result->USER_ID, $result->STOCK_ID, 
					$this->webspice->get_user_id(), $this->webspice->now(), $result->LAPTOP_SR_NO)
					);
					if ($this->db->trans_status() === FALSE){
						$this->db->trans_rollback();
						echo 'failed';
						exit;
						
					}else{
						$this->db->trans_commit();
					}
					$this->db->trans_off();
					
					if( $update ){
						$this->webspice->log_me('replace_laptop'); # log
						$html = 'update_success';
						echo $html;
						exit;
					}

					echo $html;
					break;
			}
			echo $html;
			exit; 
		}

		# default
		$this->load->view('laptop_operation/replace_laptop', $data);
	}
	
	function manage_on_replacing_laptop(){
		
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_on_replacing_laptop');
		$this->webspice->permission_verify('manage_replace_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_STOCK.CREATED_DATE ';
    $groupby = null;
    $where = ' WHERE TBL_STOCK.STATUS = 21 ';
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

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_STOCK', 
			$InputField = array(), 
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL','TBL_STOCK.PURCHASE_DATE','TBL_LAPTOP_USER.USER_NAME','TBL_LAPTOP_USER.EMPLOYEE_ID','TBL_LAPTOP_USER.USER_EMAIL'),
			$AdditionalWhere = ' TBL_STOCK.STATUS = 21 ',
			$DateBetween = array('TBL_STOCK.UPDATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('laptop_operation/print_manage_on_replacing_laptop',$data);
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

		$this->load->view('laptop_operation/manage_on_replacing_laptop', $data);
	}
	
	function manage_replace_laptop(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_replace_laptop');
		$this->webspice->permission_verify('replace_laptop');

		$this->load->database();
    $orderby = ' ORDER BY TBL_DISTRIBUTION.CREATED_DATE ';
    $groupby = null;
    $where = ' WHERE TBL_DISTRIBUTION.STATUS = 19 AND ROWNUM <= 50';
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
		INNER JOIN TBL_STOCK ON TBL_DISTRIBUTION.STOCK_ID = TBL_STOCK.STOCK_ID
		LEFT JOIN TBL_LAPTOP_USER ON TBL_LAPTOP_USER.USER_ID = TBL_DISTRIBUTION.USER_ID
		";

   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_DISTRIBUTION', 
			$InputField = array(), 
			$Keyword = array('TBL_STOCK.LAPTOP_SR_NO','TBL_STOCK.BRAND','TBL_STOCK.MODEL','TBL_STOCK.PURCHASE_DATE','TBL_LAPTOP_USER.USER_NAME','TBL_LAPTOP_USER.EMPLOYEE_ID','TBL_LAPTOP_USER.USER_EMAIL'),
			$AdditionalWhere = 'TBL_DISTRIBUTION.STATUS = 19',
			$DateBetween = array('TBL_DISTRIBUTION.CREATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('laptop_operation/print_replace_laptop',$data);
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

		$this->load->view('laptop_operation/manage_replace_laptop', $data);
	}
	
	#call confirmation for redirect another url with message
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