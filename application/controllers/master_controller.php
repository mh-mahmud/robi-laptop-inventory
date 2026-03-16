<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Master_controller extends CI_Controller {
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
	
	
	function create_user($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;

		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'create_user');
		$this->webspice->permission_verify('create_user');
		
		if( !isset($data['edit']) ){
			$data['edit'] = array(
				'USER_ID'=>null,
				'EMPLOYEE_ID'=>null,
				'USER_NAME'=>null,  
				'USER_EMAIL'=>null,
				'USER_PHONE'=>null,
				'USER_ROLE'=>null
			);
		}
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('employee_id','employee_id','required|trim|xss_clean');
		$this->form_validation->set_rules('register_name','register_name','required|trim|xss_clean');
		$this->form_validation->set_rules('register_email','register_email','required|valid_email|trim|xss_clean');
		$this->form_validation->set_rules('register_phone','register_phone','required|trim|xss_clean');
		$this->form_validation->set_rules('user_role','user_role','required|trim|xss_clean');

		if( !$this->form_validation->run() ){ 
			$this->load->view('user/create_user', $data);
			return FALSE;
		}
		
		# get input post
		$input = $this->webspice->get_input('user_id');
		
		#duplicate test
		$this->webspice->db_field_duplicate_test("SELECT * FROM TBL_USER WHERE USER_EMAIL=?", array( $input->register_email), 'You are not allowed to enter duplicate email', 'USER_ID', $input->user_id, $data, 'user/create_user');
		
		# remove cache
		$this->webspice->remove_cache('user');
		
		# update process
		if( $input->user_id ){
			#update query
			$sql = "
			UPDATE TBL_USER SET EMPLOYEE_ID=?,  USER_NAME=?, USER_EMAIL=?, USER_PHONE=?, ROLE_ID=?, UPDATED_BY=?, UPDATED_DATE=TO_DATE(?,'yyyy-mm-dd hh-mi-ss') 
			WHERE USER_ID=?";
			$this->db->query($sql, array($input->employee_id, $input->employee_id, $input->register_name, $input->register_email, $input->register_phone, $input->user_role, $this->webspice->get_user_id(), $this->webspice->now(), $input->user_id)); 

			$this->webspice->message_board('Record has been updated!');
			$this->webspice->log_me('user_updated - '.$input->register_email); # log activities
			$this->webspice->force_redirect($url_prefix.'manage_user');
			return false;
		}
		
		#create user
		$new_squence = $this->webspice->getLastInserted('TBL_USER','USER_ID');
		$random_password = 'Robi@123';
		$sql = "
		INSERT INTO TBL_USER
		(USER_ID, EMPLOYEE_ID, USER_NAME, USER_EMAIL, USER_PHONE, USER_PASSWORD, ROLE_ID, CREATED_BY, CREATED_DATE, STATUS)
		VALUES
		(?, ?, ?, ?, ?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh-mi-ss'), 8)";
		$result = $this->db->query($sql, array(($new_squence+1), $input->employee_id, $input->register_name, $input->register_email, $input->register_phone, 
		$this->webspice->encrypt_decrypt($random_password, 'encrypt'), $input->user_role, $this->webspice->get_user_id(), $this->webspice->now()));
		
		if( !$result ){
			$this->webspice->message_board('We could not execute your request. Please tray again later or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
		}
		
		# send verification email
		#$this->load->library('email_template');
		#$this->email_template->send_new_user_password_change_email($input->register_name, $input->register_email);
		
		#$this->webspice->message_board('An account has been created and sent an email to the user.');
		$this->webspice->message_board('An account has been created.');
		$this->webspice->force_redirect($url_prefix);
		 
	}
	function manage_user(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_user');
		$this->webspice->permission_verify('manage_user');

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

		$initialSQL = "
		SELECT TBL_USER.*,
		TBL_ROLE.ROLE_NAME
		FROM TBL_USER
		LEFT JOIN TBL_ROLE ON TBL_ROLE.ROLE_ID = TBL_USER.ROLE_ID
		";
    
   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_USER', 
			$InputField = array(), 
			$Keyword = array('USER_ID', 'EMPLOYEE_ID', 'USER_NAME','USER_EMAIL','USER_PHONE'),
			$AdditionalWhere = null,
			$DateBetween = array('CREATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('user/print_user',$data);
				return false;
        break;

			case 'edit':
          $this->webspice->edit_generator($TableName='TBL_USER', $KeyField='USER_ID', $key, $RedirectController='master_controller', $RedirectFunction='create_user', $PermissionName='manage_user', $StatusCheck=null, $Log='edit_user');          
					return false;
          break;
          
 			case 'inactive':
      		$this->webspice->action_executer($TableName='TBL_USER', $KeyField='USER_ID', $key, $RedirectURL='manage_user', $PermissionName='manage_user', $StatusCheck=7, $ChangeStatus=-7, $RemoveCache='user', $Log='inactive_user');
					return false;	
          break; 

			case 'active':
					$this->webspice->action_executer($TableName='TBL_USER', $KeyField='USER_ID', $key, $RedirectURL='manage_user', $PermissionName='manage_user', $StatusCheck=-7, $ChangeStatus=7, $RemoveCache='user', $Log='active_user');
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

		$this->load->view('user/manage_user', $data);
	}
	
	function create_role($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;

		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'create_user');
		$this->webspice->permission_verify('create_role,manage_role');
		
		if( !isset($data['edit']) ){
			$data['edit'] = array(
			'ROLE_ID'=>null,
			'ROLE_NAME'=>null,  
			'PERMISSION_NAME'=>null
			);
		}
		
		# get permission name
		$sql = "SELECT TBL_PERMISSION.* 
			FROM TBL_PERMISSION 
			where TBL_PERMISSION.STATUS = 7 
			and TBL_PERMISSION.GROUP_NAME IN(
				SELECT GROUP_NAME FROM TBL_PERMISSION GROUP BY GROUP_NAME
			)
			AND TBL_PERMISSION.PERMISSION_NAME IN(
			SELECT PERMISSION_NAME FROM TBL_PERMISSION GROUP BY PERMISSION_NAME
			) ORDER BY TBL_PERMISSION.GROUP_NAME
		";
		$data['get_permission_with_group'] = $this->db->query($sql)->result();

		$this->load->library('form_validation');
		$this->form_validation->set_rules('role_name','role_name','required|trim|xss_clean');

		if( !$this->form_validation->run() ){ 
			$this->load->view('user/create_role', $data);
			return FALSE;
		}

		# get input post
		$input = $this->webspice->get_input('ROLE_ID');

		#duplicate test
		$this->webspice->db_field_duplicate_test("SELECT * FROM TBL_ROLE WHERE ROLE_NAME=?", array( $input->role_name), 'You are not allowed to enter duplicate role name.', 'ROLE_ID', $input->ROLE_ID, $data, 'user/create_role');
		
		# remove cache
		$this->webspice->remove_cache('role');
		
		# update data
		if( $input->ROLE_ID ){
			#update query
			$sql = "
			UPDATE TBL_ROLE SET ROLE_NAME=?, PERMISSION_NAME=?, UPDATED_BY=?, UPDATED_DATE=TO_DATE(?,'yyyy-mm-dd hh-mi-ss') 
			WHERE ROLE_ID=?";
			$this->db->query($sql, array($input->role_name, implode(',',$input->permission), $this->webspice->get_user_id(), $this->webspice->now(), $input->ROLE_ID)); 

			$this->webspice->message_board('Record has been updated!');
			$this->webspice->log_me('role_updated - '.$input->role_name); # log activities
			$this->webspice->force_redirect($url_prefix.'manage_role');
			return false;
		}
		
		# insert data
		$new_squence = $this->webspice->getLastInserted('TBL_ROLE','ROLE_ID');
		$sql = "
		INSERT INTO TBL_ROLE
		(ROLE_ID, ROLE_NAME, PERMISSION_NAME, CREATED_BY, CREATED_DATE, STATUS)
		VALUES
		(?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh-mi-ss'), 7)";
		$result = $this->db->query($sql, array(($new_squence+1), $input->role_name, implode(',',$input->permission), $this->webspice->get_user_id(), $this->webspice->now()));
		
		if( !$result ){
			$this->webspice->message_board('We could not execute your request. Please tray again later or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
		}
		
		$this->webspice->message_board('New Role has been created.');
		if( $this->webspice->permission_verify('manage_role', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_role');
		}
		
		$this->webspice->force_redirect($url_prefix);
	}
	function manage_role(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_user');
		$this->webspice->permission_verify('manage_role');

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

		$initialSQL = "
		SELECT TBL_ROLE.*
		FROM TBL_ROLE
		";
    
   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_ROLE', 
			$InputField = array(),
			$Keyword = array('ROLE_ID','ROLE_NAME','PERMISSION_NAME'),
			$AdditionalWhere = null,
			$DateBetween = array('CREATED_DATE', 'date_from', 'date_end')
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
			
				$this->load->view('user/print_role',$data);
				return false;
        break;

			case 'edit':
          $this->webspice->edit_generator($TableName='TBL_ROLE', $KeyField='ROLE_ID', $key, $RedirectController='master_controller', $RedirectFunction='create_role', $PermissionName='create_role', $StatusCheck=null, $Log='edit_role');          
					return false;
          break; 
          
 			case 'inactive':
      		$this->webspice->action_executer($TableName='TBL_ROLE', $KeyField='ROLE_ID', $key, $RedirectURL='manage_role', $PermissionName='manage_role', $StatusCheck=7, $ChangeStatus=-7, $RemoveCache='role', $Log='inactive_role');
					return false;	
          break; 

			case 'active':
					$this->webspice->action_executer($TableName='TBL_ROLE', $KeyField='ROLE_ID', $key, $RedirectURL='manage_role', $PermissionName='manage_role', $StatusCheck=-7, $ChangeStatus=7, $RemoveCache='role', $Log='active_role');
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

		$this->load->view('user/manage_role', $data);
	}
	
	function create_option($data=null){
		$url_prefix = $this->webspice->settings()->site_url_prefix;

		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'create_option');
		$this->webspice->permission_verify('create_option,manage_option');
		
		if( !isset($data['edit']) ){
			$data['edit'] = array(
			'OPTION_ID'=>null,
			'OPTION_VALUE'=>null,  
			'GROUP_NAME'=>null,
			'PARENT_ID'=>null,
			'STATUS'=>null
			);
		}
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('option_value','option_value','required|trim|xss_clean');
		$this->form_validation->set_rules('group_name','group_name','required|trim|xss_clean');

		if( !$this->form_validation->run() ){ 
			$this->load->view('master/create_option', $data);
			return FALSE;
		}
		
		# get input post
		$input = $this->webspice->get_input('key');
		
		#duplicate test
		$this->webspice->db_field_duplicate_test("SELECT * FROM TBL_OPTION WHERE OPTION_VALUE=? AND GROUP_NAME=?", array( $input->option_value, $input->group_name), 'You are not allowed to enter duplicate option value', 'OPTION_ID', $input->key, $data, 'master/create_option');
		
		# remove cache
		$this->webspice->remove_cache('option');
		
		# update process
		if( $input->key ){
			#update query
			$sql = "
			UPDATE TBL_OPTION SET OPTION_VALUE=?, UPDATED_BY=?, UPDATED_DATE=TO_DATE(?,'yyyy-mm-dd hh-mi-ss')
			WHERE OPTION_ID=?";
			$this->db->query($sql, array($input->option_value, $this->webspice->get_user_id(), $this->webspice->now(), $input->key)); 
	#dd( $this->db->last_query() );	
			$this->webspice->message_board('Record has been updated!');
			$this->webspice->log_me('option_updated - '.$input->option_value); # log activities
			$this->webspice->force_redirect($url_prefix.'manage_option');
			return false;
		}
		
		#create user
		$new_squence = $this->webspice->getLastInserted('TBL_OPTION','OPTION_ID');
		$sql = "
		INSERT INTO TBL_OPTION
		(OPTION_ID, OPTION_VALUE, GROUP_NAME, CREATED_BY, CREATED_DATE, STATUS)
		VALUES
		(?, ?, ?, ?, TO_DATE(?,'yyyy-mm-dd hh-mi-ss'), 7)";
		$result = $this->db->query($sql, array(($new_squence+1), $input->option_value, $input->group_name, $this->webspice->get_user_id(), $this->webspice->now()));

		if( !$result ){
			$this->webspice->message_board('We could not execute your request. Please tray again later or report to authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
		}

		$this->webspice->message_board('Record has been inserted successfully.');
		if( $this->webspice->permission_verify('manage_option', true) ){
			$this->webspice->force_redirect($url_prefix.'manage_option');
		}

		$this->webspice->force_redirect($url_prefix);
	}
	function manage_option(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		
		$this->webspice->user_verify($url_prefix.'login', $url_prefix.'manage_option');
		$this->webspice->permission_verify('manage_option');

		$this->load->database();
    $orderby = ' ORDER BY TBL_OPTION.GROUP_NAME ASC, TBL_OPTION.OPTION_ID DESC';
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

		$initialSQL = "
		SELECT TBL_OPTION.*
		FROM TBL_OPTION
		";
    
   	# filtering records
    if( $this->input->post('filter') ){
			$result = $this->webspice->filter_generator(
			$TableName = 'TBL_OPTION', 
			$InputField = array(), 
			$Keyword = array('OPTION_ID','OPTION_VALUE','GROUP_NAME'),
			$AdditionalWhere = null,
			$DateBetween = array()
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
			
				$this->load->view('master/print_option',$data);
				return false;
        break;

			case 'edit':
          $this->webspice->edit_generator($TableName='TBL_OPTION', $KeyField='OPTION_ID', $key, $RedirectController='master_controller', $RedirectFunction='create_option', $PermissionName='create_option,manage_option', $StatusCheck=null, $Log='edit_user');          
					return false;
          break;
          
 			case 'inactive':
      		$this->webspice->action_executer($TableName='TBL_OPTION', $KeyField='OPTION_ID', $key, $RedirectURL='manage_option', $PermissionName='manage_option', $StatusCheck=7, $ChangeStatus=-7, $RemoveCache='option', $Log='inactive_option');
					return false;	
          break; 

			case 'active':
					$this->webspice->action_executer($TableName='TBL_OPTION', $KeyField='OPTION_ID', $key, $RedirectURL='manage_option', $PermissionName='manage_option', $StatusCheck=-7, $ChangeStatus=7, $RemoveCache='option', $Log='active_option');
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

		$this->load->view('master/manage_option', $data);
	}
	
	
	# call confirmation for redirect another url with message
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