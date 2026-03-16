<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Temp_controller extends CI_Controller {
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

	function lost_laptop_batch($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data_batch = 50; # how much row(s) inserted once
		ini_set('MAX_EXECUTION_TIME', 300);
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'lost_laptop_batch');
		$this->webspice->permission_verify('lost_laptop');
		
		if( !$_FILES || !$_FILES['attached_file']['tmp_name'] ){
			$this->load->view('laptop_operation/lost_laptop_batch', $data);
			return FALSE;
		}

		# verify file type
		if( $_FILES['attached_file']['tmp_name'] ){
			$this->webspice->check_file_type(array('csv','xls'), 'attached_file', $data, 'laptop_operation/lost_laptop_batch');   
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
				$this->webspice->force_redirect($url_prefix.'lost_laptop_batch');
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
			$this->webspice->force_redirect($url_prefix.'lost_laptop_batch');
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
			$Serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper($data_list[9])));
			$Porpuse = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[10]));
			$Issue_date = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[11]));
		
			# must have column value - column offset started from 1
			if( !isset($Employee_id) || !isset($User_name) || !isset($Division) ||  !isset($Department) ||  !isset($Designation) ||  !isset($User_type) || !isset($Serial_no) ||  !isset($Porpuse) || !isset($Issue_date) ){
				$data_error .= 'Row #'.$k.' is incomplete.<br />';
			}
			
		
/*			# verify employee
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
			if( isset($Serial_no) && $this->customcache->laptop_maker($Serial_no,'STATUS') != 12  ){
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
/*						#get AD user
						$search_filter = 'mail='.$Employee_id;
						$user = $this->webspice->get_ad_user('Administrators', 'administrator', 'nns@1212', $ldap_host='192.168.10.10', $ldap_dn='DC=nns-solution,DC=net', $base_dn='DC=nns-solution,DC=net', $ldap_user_domain='@nns-solution.net', $search_filter);
						
						$user=$user[0];*/
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
					UPDATE TBL_STOCK SET USER_ID=?, STATUS=?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss') 
					WHERE LAPTOP_SR_NO=?", array($new_squence_user,14,$this->webspice->get_user_id(),$this->webspice->now(),$Serial_no));	
					
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
					TO_DATE('".$Issue_date."','dd-mm-yyyy hh:mi:ss'),14)";

					$this->db->query("
					INSERT INTO TBL_DISTRIBUTION(ID, USER_ID, STOCK_ID, PURPOSE_ID, USER_DESIGNATION, CREATED_BY, CREATED_DATE, STATUS) 
					VALUES ".$data_query_distribution_insert);


		}

		# insert file name to stop duplicate upload
		#$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION_FILE','FILE_ID');
		#$this->db->query("INSERT INTO TBL_DISTRIBUTION_FILE(FILE_ID, FILE_NAME, UPLOAD_BY, UPLOAD_DATE) VALUES(?,?,?,TO_DATE(?,'yyyy-mm-dd hh:mi:ss'))", array($new_squence+1,$_FILES['attached_file']['name'], $this->webspice->get_user_id(), $this->webspice->now()));

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$this->webspice->message_board('We could not execute your request. Please try again or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else{
			$this->db->trans_commit();
		}
		$this->db->trans_off();
		$this->webspice->log_me('uploaded_lost_laptop_batch'); # log
		
		# remove cache
		$this->webspice->remove_cache('laptop');
		$this->webspice->remove_cache('laptop_user');
		
		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_lost_laptop', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_lost_laptop');
		}

		$this->webspice->force_redirect($url_prefix);
	}


	function noc_with_laptop_batch($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data_batch = 50; # how much row(s) inserted once
		ini_set('MAX_EXECUTION_TIME', 300);
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'noc_with_laptop_batch');
		$this->webspice->permission_verify('lost_laptop');
		
		if( !$_FILES || !$_FILES['attached_file']['tmp_name'] ){
			$this->load->view('laptop_operation/noc_with_laptop_batch', $data);
			return FALSE;
		}

		# verify file type
		if( $_FILES['attached_file']['tmp_name'] ){
			$this->webspice->check_file_type(array('csv','xls'), 'attached_file', $data, 'laptop_operation/noc_with_laptop_batch');   
		}
		
		# verify duplicate file
		$get_bundle_file_sql = "SELECT * FROM TBL_DISTRIBUTION_FILE WHERE FILE_NAME = ?";
		$get_bundle_file = $this->db->query($get_bundle_file_sql, array($_FILES['attached_file']['name']))->result();
		if($get_bundle_file){
			$this->webspice->message_board('This file uploaded once. Please upload the correct file.');
			$this->webspice->force_redirect($url_prefix.'noc_with_laptop_batch');
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
				$this->webspice->force_redirect($url_prefix.'noc_with_laptop_batch');
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
			$this->webspice->force_redirect($url_prefix.'noc_with_laptop_batch');
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
			$Serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper($data_list[9])));
			$Porpuse = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[10]));
			$Issue_date = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[11]));
		
			# must have column value - column offset started from 1
			if( !isset($Employee_id) || !isset($User_name) || !isset($Division) ||  !isset($Department) ||  !isset($Designation) ||  !isset($User_type) || !isset($Serial_no) ||  !isset($Porpuse) || !isset($Issue_date) ){
				$data_error .= 'Row #'.$k.' is incomplete.<br />';
			}
			
		
/*			# verify employee
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
			if( isset($Serial_no) && $this->customcache->laptop_maker($Serial_no,'STATUS') != 12  ){
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
/*						#get AD user
						$search_filter = 'mail='.$Employee_id;
						$user = $this->webspice->get_ad_user('Administrators', 'administrator', 'nns@1212', $ldap_host='192.168.10.10', $ldap_dn='DC=nns-solution,DC=net', $base_dn='DC=nns-solution,DC=net', $ldap_user_domain='@nns-solution.net', $search_filter);
						
						$user=$user[0];*/
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
					UPDATE TBL_STOCK SET USER_ID=?, STATUS=?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss') 
					WHERE LAPTOP_SR_NO=?", array($new_squence_user,15,$this->webspice->get_user_id(),$this->webspice->now(),$Serial_no));	
					
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
					TO_DATE('".$Issue_date."','dd-mm-yyyy hh:mi:ss'),15)";

					$this->db->query("
					INSERT INTO TBL_DISTRIBUTION(ID, USER_ID, STOCK_ID, PURPOSE_ID, USER_DESIGNATION, CREATED_BY, CREATED_DATE, STATUS) 
					VALUES ".$data_query_distribution_insert);


		}

		# insert file name to stop duplicate upload
		#$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION_FILE','FILE_ID');
		#$this->db->query("INSERT INTO TBL_DISTRIBUTION_FILE(FILE_ID, FILE_NAME, UPLOAD_BY, UPLOAD_DATE) VALUES(?,?,?,TO_DATE(?,'yyyy-mm-dd hh:mi:ss'))", array($new_squence+1,$_FILES['attached_file']['name'], $this->webspice->get_user_id(), $this->webspice->now()));

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$this->webspice->message_board('We could not execute your request. Please try again or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else{
			$this->db->trans_commit();
		}
		$this->db->trans_off();
		$this->webspice->log_me('uploaded_noc_with_laptop_batch'); # log
		
		# remove cache
		$this->webspice->remove_cache('laptop');
		$this->webspice->remove_cache('laptop_user');
		
		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_noc_with_laptop', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_noc_with_laptop');
		}

		$this->webspice->force_redirect($url_prefix);
	}
	
	
	function noc_without_laptop_batch($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data_batch = 50; # how much row(s) inserted once
		ini_set('MAX_EXECUTION_TIME', 300);
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'noc_with_laptop_batch');
		$this->webspice->permission_verify('lost_laptop');
		
		if( !$_FILES || !$_FILES['attached_file']['tmp_name'] ){
			$this->load->view('laptop_operation/noc_with_laptop_batch', $data);
			return FALSE;
		}

		# verify file type
		if( $_FILES['attached_file']['tmp_name'] ){
			$this->webspice->check_file_type(array('csv','xls'), 'attached_file', $data, 'laptop_operation/noc_with_laptop_batch');   
		}
		
		# verify duplicate file
		$get_bundle_file_sql = "SELECT * FROM TBL_DISTRIBUTION_FILE WHERE FILE_NAME = ?";
		$get_bundle_file = $this->db->query($get_bundle_file_sql, array($_FILES['attached_file']['name']))->result();
		if($get_bundle_file){
			$this->webspice->message_board('This file uploaded once. Please upload the correct file.');
			$this->webspice->force_redirect($url_prefix.'noc_with_laptop_batch');
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
				$this->webspice->force_redirect($url_prefix.'noc_with_laptop_batch');
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
			$this->webspice->force_redirect($url_prefix.'noc_with_laptop_batch');
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
			$Serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper($data_list[9])));
			$Porpuse = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[10]));
			$Issue_date = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[11]));
		
			# must have column value - column offset started from 1
			if( !isset($Employee_id) || !isset($User_name) || !isset($Division) ||  !isset($Department) ||  !isset($Designation) ||  !isset($User_type) || !isset($Serial_no) ||  !isset($Porpuse) || !isset($Issue_date) ){
				$data_error .= 'Row #'.$k.' is incomplete.<br />';
			}
			
		
/*			# verify employee
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
			if( isset($Serial_no) && $this->customcache->laptop_maker($Serial_no,'STATUS') != 12  ){
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
/*						#get AD user
						$search_filter = 'mail='.$Employee_id;
						$user = $this->webspice->get_ad_user('Administrators', 'administrator', 'nns@1212', $ldap_host='192.168.10.10', $ldap_dn='DC=nns-solution,DC=net', $base_dn='DC=nns-solution,DC=net', $ldap_user_domain='@nns-solution.net', $search_filter);
						
						$user=$user[0];*/
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
					UPDATE TBL_STOCK SET USER_ID=?, STATUS=?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss') 
					WHERE LAPTOP_SR_NO=?", array('',11,$this->webspice->get_user_id(),$this->webspice->now(),$Serial_no));	
					
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
					TO_DATE('".$Issue_date."','dd-mm-yyyy hh:mi:ss'),16)";

					$this->db->query("
					INSERT INTO TBL_DISTRIBUTION(ID, USER_ID, STOCK_ID, PURPOSE_ID, USER_DESIGNATION, CREATED_BY, CREATED_DATE, STATUS) 
					VALUES ".$data_query_distribution_insert);


		}

		# insert file name to stop duplicate upload
		#$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION_FILE','FILE_ID');
		#$this->db->query("INSERT INTO TBL_DISTRIBUTION_FILE(FILE_ID, FILE_NAME, UPLOAD_BY, UPLOAD_DATE) VALUES(?,?,?,TO_DATE(?,'yyyy-mm-dd hh:mi:ss'))", array($new_squence+1,$_FILES['attached_file']['name'], $this->webspice->get_user_id(), $this->webspice->now()));

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$this->webspice->message_board('We could not execute your request. Please try again or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else{
			$this->db->trans_commit();
		}
		$this->db->trans_off();
		$this->webspice->log_me('uploaded_noc_with_laptop_batch'); # log
		
		# remove cache
		$this->webspice->remove_cache('laptop');
		$this->webspice->remove_cache('laptop_user');
		
		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_noc_with_laptop', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_noc_without_personalized');
		}

		$this->webspice->force_redirect($url_prefix);
	}
	
	
	function faulty_laptop_batch($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data_batch = 50; # how much row(s) inserted once
		ini_set('MAX_EXECUTION_TIME', 300);
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'faulty_laptop_batch');
		$this->webspice->permission_verify('lost_laptop');
		
		if( !$_FILES || !$_FILES['attached_file']['tmp_name'] ){
			$this->load->view('laptop_operation/faulty_laptop_batch', $data);
			return FALSE;
		}

		# verify file type
		if( $_FILES['attached_file']['tmp_name'] ){
			$this->webspice->check_file_type(array('csv','xls'), 'attached_file', $data, 'laptop_operation/faulty_laptop_batch');   
		}
		
		# verify duplicate file
		$get_bundle_file_sql = "SELECT * FROM TBL_DISTRIBUTION_FILE WHERE FILE_NAME = ?";
		$get_bundle_file = $this->db->query($get_bundle_file_sql, array($_FILES['attached_file']['name']))->result();
		if($get_bundle_file){
			$this->webspice->message_board('This file uploaded once. Please upload the correct file.');
			$this->webspice->force_redirect($url_prefix.'noc_with_laptop_batch');
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
				$this->webspice->force_redirect($url_prefix.'noc_with_laptop_batch');
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
			$this->webspice->force_redirect($url_prefix.'noc_with_laptop_batch');
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
			$Serial_no = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input(strtoupper($data_list[9])));
			$Porpuse = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[10]));
			$Issue_date = str_replace(array("'",'"',','), array('','',''), $this->webspice->clean_input($data_list[11]));
		
			# must have column value - column offset started from 1
			if( !isset($Employee_id) || !isset($User_name) || !isset($Division) ||  !isset($Department) ||  !isset($Designation) ||  !isset($User_type) || !isset($Serial_no) ||  !isset($Porpuse) || !isset($Issue_date) ){
				$data_error .= 'Row #'.$k.' is incomplete.<br />';
			}
			
		
/*			# verify employee
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
			if( isset($Serial_no) && $this->customcache->laptop_maker($Serial_no,'STATUS') != 12  ){
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
/*						#get AD user
						$search_filter = 'mail='.$Employee_id;
						$user = $this->webspice->get_ad_user('Administrators', 'administrator', 'nns@1212', $ldap_host='192.168.10.10', $ldap_dn='DC=nns-solution,DC=net', $base_dn='DC=nns-solution,DC=net', $ldap_user_domain='@nns-solution.net', $search_filter);
						
						$user=$user[0];*/
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
					UPDATE TBL_STOCK SET USER_ID=?, STATUS=?, UPDATED_BY = ?, UPDATED_DATE = TO_DATE(?,'yyyy-mm-dd hh:mi:ss') 
					WHERE LAPTOP_SR_NO=?", array($new_squence_user,13,$this->webspice->get_user_id(),$this->webspice->now(),$Serial_no));	
					
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
					TO_DATE('".$Issue_date."','dd-mm-yyyy hh:mi:ss'),13)";

					$this->db->query("
					INSERT INTO TBL_DISTRIBUTION(ID, USER_ID, STOCK_ID, PURPOSE_ID, USER_DESIGNATION, CREATED_BY, CREATED_DATE, STATUS) 
					VALUES ".$data_query_distribution_insert);


		}

		# insert file name to stop duplicate upload
		#$new_squence = $this->webspice->getLastInserted('TBL_DISTRIBUTION_FILE','FILE_ID');
		#$this->db->query("INSERT INTO TBL_DISTRIBUTION_FILE(FILE_ID, FILE_NAME, UPLOAD_BY, UPLOAD_DATE) VALUES(?,?,?,TO_DATE(?,'yyyy-mm-dd hh:mi:ss'))", array($new_squence+1,$_FILES['attached_file']['name'], $this->webspice->get_user_id(), $this->webspice->now()));

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$this->webspice->message_board('We could not execute your request. Please try again or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else{
			$this->db->trans_commit();
		}
		$this->db->trans_off();
		$this->webspice->log_me('uploaded_noc_with_laptop_batch'); # log
		
		# remove cache
		$this->webspice->remove_cache('laptop');
		$this->webspice->remove_cache('laptop_user');
		
		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_noc_with_laptop', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_broken_fault_laptop');
		}

		$this->webspice->force_redirect($url_prefix);
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