<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Parent_controller extends CI_Controller {
	function __construct(){
		parent::__construct();
	}
	
	/*
		>> Error log should be added prefix Error:
		Log Prefix:
		login_attempt - Login Ateempt
		login_success
		unauthorized_access
		password_retrieve_request
		password_changed
	*/
	function index(){
		#echo $this->customcache->option_id_maker('Software','OPTION_ID');
		#$this->db->query("UPDATE TBL_LAPTOP_USER SET USER_TYPE=15 WHERE TBL_LAPTOP_USER.USER_ID=5");
		#$this->db->query("DELETE FROM TBL_STOCK WHERE PO_NO = '1'");
		#$dd = $this->db->query("SELECT * FROM TBL_DISTRIBUTION")->result();
		#dd($dd);
		
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data = null;
		
		// total laptop based on brand and model
		$total_laptop = $this->db->query("SELECT TBL_STOCK.BRAND, TBL_STOCK.MODEL, COUNT(TBL_STOCK.STOCK_ID) AS STOCK
																			FROM TBL_STOCK
																			GROUP BY TBL_STOCK.BRAND, TBL_STOCK.MODEL")->result();
																			
		// laptop is available to assign based on brand and model
		$available_laptop = $this->db->query("SELECT TBL_STOCK.BRAND, TBL_STOCK.MODEL, COUNT(TBL_STOCK.STOCK_ID)  AS STOCK
																					FROM TBL_STOCK
																					WHERE TBL_STOCK.STATUS = 11
																					GROUP BY TBL_STOCK.BRAND, TBL_STOCK.MODEL")->result();
																					
																					
		$year_wise_data = $this->db->query("
																		SELECT BRAND, MODEL, SUM(Y0) AS S1, SUM(Y1) AS S2, SUM(Y2) AS S3, SUM(Y3) AS S4, SUM(Y4) AS S5
																		FROM(
																		SELECT BRAND, MODEL,
																		(CASE P_YEAR WHEN TO_CHAR((EXTRACT(YEAR FROM sysdate))) THEN SUM(STOCK) END) Y0, /*current year*/ /* for oracle - EXTRACT(YEAR FROM sysdate)*/
																		(CASE P_YEAR WHEN TO_CHAR(((EXTRACT(YEAR FROM sysdate))-1)) THEN SUM(STOCK) END) Y1, /*current year - 1*/
																		(CASE P_YEAR WHEN TO_CHAR(((EXTRACT(YEAR FROM sysdate))-2)) THEN SUM(STOCK) END) Y2,
																		(CASE P_YEAR WHEN TO_CHAR(((EXTRACT(YEAR FROM sysdate))-3)) THEN SUM(STOCK) END) Y3,
																		(CASE P_YEAR WHEN TO_CHAR(((EXTRACT(YEAR FROM sysdate))-4)) THEN SUM(STOCK) END) Y4
																		FROM(
																		SELECT BRAND, MODEL, P_YEAR, SUM(STOCK) AS STOCK
																		FROM(
																		    SELECT TBL_STOCK.BRAND, TBL_STOCK.MODEL,
																		    TO_CHAR(TBL_STOCK.PURCHASE_DATE,'yyyy') P_YEAR,
																		    /*for oracle - TO_CHAR(TBL_STOCK.PURCHASE_DATE,'yyyy') AS P_YEAR,*/
																		    COUNT(TBL_STOCK.STOCK_ID) AS STOCK
																		    FROM TBL_STOCK
																		    GROUP BY TBL_STOCK.BRAND, TBL_STOCK.MODEL, TBL_STOCK.PURCHASE_DATE
																		) TBL_RESULT
																		GROUP BY TBL_RESULT.BRAND, TBL_RESULT.MODEL, TBL_RESULT.P_YEAR
																		) TBL_YEAR
																		GROUP BY BRAND, MODEL, P_YEAR
																		) TBL_FINAL
																		GROUP BY BRAND, MODEL
		")->result();
		
		
		// Distributed laptop based on brand and model
		$distribution_laptop = $this->db->query("
			SELECT TBL_STOCK.BRAND, TBL_STOCK.MODEL, COUNT(TBL_STOCK.STOCK_ID)  AS STOCK 
			FROM TBL_STOCK WHERE TBL_STOCK.STATUS = 12 GROUP BY TBL_STOCK.BRAND, TBL_STOCK.MODEL
		")->result();
						
		$laptop_based_eol = $this->db->query("
		SELECT BRAND, MODEL, SUM(Y0) AS S1, SUM(Y1) AS S2, SUM(Y2) AS S3, SUM(Y3) AS S4, SUM(Y4) AS S5 FROM(
			SELECT BRAND, MODEL,
			(CASE P_YEAR WHEN TO_CHAR((EXTRACT(YEAR FROM sysdate))) THEN SUM(STOCK) END) Y0,
			(CASE P_YEAR WHEN TO_CHAR(((EXTRACT(YEAR FROM sysdate))+1)) THEN SUM(STOCK) END) Y1,
			(CASE P_YEAR WHEN TO_CHAR(((EXTRACT(YEAR FROM sysdate))+2)) THEN SUM(STOCK) END) Y2,
			(CASE P_YEAR WHEN TO_CHAR(((EXTRACT(YEAR FROM sysdate))+3)) THEN SUM(STOCK) END) Y3,
			(CASE P_YEAR WHEN TO_CHAR(((EXTRACT(YEAR FROM sysdate))+4)) THEN SUM(STOCK) END) Y4
			FROM(
			SELECT BRAND, MODEL, P_YEAR, SUM(STOCK) AS STOCK
			FROM(
				SELECT TBL_STOCK.BRAND, TBL_STOCK.MODEL,
				TO_CHAR(TBL_STOCK.EOL_DATE,'yyyy') P_YEAR,
				COUNT(TBL_STOCK.STOCK_ID) AS STOCK
				FROM TBL_STOCK
				GROUP BY TBL_STOCK.BRAND, TBL_STOCK.MODEL, TBL_STOCK.EOL_DATE
			) TBL_RESULT
			GROUP BY TBL_RESULT.BRAND, TBL_RESULT.MODEL, TBL_RESULT.P_YEAR
			) TBL_YEAR
			GROUP BY BRAND, MODEL, P_YEAR
			) TBL_FINAL
			GROUP BY BRAND, MODEL
		")->result();

						
		$data = array();
		$data['total_laptop'] = $total_laptop;
		$data['available_laptop'] = $available_laptop;
		$data['year_wise_data'] = $year_wise_data;
		$data['distribution_laptop'] = $distribution_laptop;
		$data['laptop_based_eol'] = $laptop_based_eol;
		
		#dd($laptop_based_eol);
		

		if( !$this->webspice->get_user_id() ){
			$this->webspice->force_redirect($url_prefix.'login');
			return false;
		}

		$this->load->view('index', $data);
	}
	
	function login(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$data = null;
		$callback = $url_prefix;
		
		# verify user logged or not
		if( $this->webspice->get_user_id() ){
			$this->webspice->message_board('Dear '.$this->webspice->get_user("USER_NAME").', you are already Logged In. Thank you.');
			$this->webspice->force_redirect($url_prefix);
			return false;
		}
 
		if( $this->webspice->login_callback(null,'get') ){ 
			$callback = $this->webspice->login_callback(null,'get');
		}
		#dd($_POST);
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');
		$this->form_validation->set_rules('user_email','user_email','required|trim');
		$this->form_validation->set_rules('user_password','user_password','required|trim');

		if( !$this->form_validation->run() ){
			$this->load->view('login', $data);
			return FALSE;
		}
		
		# get input post
		$input = $this->webspice->get_input($key = null);
		
		# more than 5 attempts - lock the last email address with remarks
		if( !isset($_SESSION['auth']['attempt']) ){
			$_SESSION['auth']['attempt'] = 1;
			
		}else{
			$_SESSION['auth']['attempt']++;
			
			if( $_SESSION['auth']['attempt'] >50 ){
				$data['title'] = 'Warning!';
				$data['body'] = 'We have identified that; you are trying to access this application illegally. Please stop the process immediately. We like to remind you that; we are tracing your IP address. So, if you try again, we will bound to take a legal action against you.';
				$data['footer'] = $this->webspice->settings()->site_title.' Authority';
				
				# $this->db->query("UPDATE user SET STATUS=-3, remarks=? WHERE user_email=? AND user_role!=1 LIMIT 1", array('Illegal Attempt ('.$this->webspice->now().'): '.$this->webspice->who_is() , $login_email));
				
				# log
				$this->webspice->log_me('illegal_attempt~'.$this->webspice->who_is().'~'.$input->user_email);
				$this->confirmation($data);
				return false;
			}
		}

		$user = $this->db->query("
		SELECT TBL_USER.*, TO_CHAR(TBL_USER.LOGIN_UPDATE_TIME,'yyyy-mm-dd hh:mi:ss') LOGIN_TIME, 
		TBL_ROLE.PERMISSION_NAME 
		FROM TBL_USER
		LEFT JOIN TBL_ROLE ON TBL_ROLE.ROLE_ID=TBL_USER.ROLE_ID
		WHERE TBL_USER.USER_EMAIL ='".$input->user_email."'
		AND TBL_USER.USER_PASSWORD=?",
		array($this->webspice->encrypt_decrypt($input->user_password, 'encrypt')) 
		);
		$user = $user->result_array();
		
		if( !$user ){
			$this->webspice->log_me('unauthorized_access'); # log
		
			$this->webspice->message_board('User ID or password is incorrect. Please try again.');
			$this->webspice->force_redirect($url_prefix.'login');
			return false;
		}

		#check new user
		if( $user[0]['STATUS'] < 1 ){
			$this->webspice->message_board('Your account is temporarily inactive! Please contact with authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			
		}else if( $user[0]['STATUS'] == 6 ){
			$this->webspice->message_board('You must verify your Email Address. We sent you a verification email. Please check your email inbox/spam folder.');
			$this->webspice->force_redirect($url_prefix);
			return false;
			 
		}else if( $user[0]['STATUS'] == 8 ){
			$verification_code = $this->webspice->encrypt_decrypt($user[0]['USER_EMAIL'].'|'.date("Y-m-d"), 'encrypt');
			$this->webspice->message_board('You must change your password.');
			$this->webspice->force_redirect($url_prefix.'change_password/'.$verification_code);
			return false;
		}else if( $user[0]['IS_LOGGED'] == 1 && 10 > $this->webspice->calculate_minutes_between_two_dates($this->webspice->now(), date("Y-m-d h:i:s", strtotime($user[0]['LOGIN_TIME']))) ){
			$this->webspice->message_board('The user has already Logged In!');
			$this->webspice->force_redirect($url_prefix);
			return false;
		}
		
		# verify password policy
		$this->verify_password_policy($user[0], 'login');

		# create user session
		$this->db->query("UPDATE TBL_USER SET IS_LOGGED=1, SESSION_ID=(SESSION_ID+1), LOGIN_UPDATE_TIME=TO_DATE(?,'yyyy-mm-dd hh:mi:ss') WHERE USER_EMAIL=?", array($this->webspice->now(), $user[0]['USER_EMAIL']));
		
		$this->webspice->create_user_session($user[0]);
		$_SESSION['auth']['attempt'] = 0;
		$this->webspice->message_board('Welcome to '.$this->webspice->settings()->domain_name.': '.$this->webspice->settings()->site_slogan);
		
		# log
		$this->webspice->log_me('login_success');

		$this->webspice->force_redirect($callback);
	}
	
	function forgot_password(){
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$this->load->database();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');
		$this->form_validation->set_rules('user_email','user_email','required|valid_email|trim|xss_clean');
		
		if( !$this->form_validation->run() ){
			$this->load->view('login', $data);
			return FALSE;
		}
		
		$input = $this->webspice->get_input();
		
		$get_record = $this->db->query("SELECT * FROM TBL_USER WHERE USER_EMAIL=?", array($input->user_email));
		$get_record = $get_record->result();
		if( !$get_record ){
			$this->webspice->message_board('The email address you entered is invalid! Please enter your email address.');
			$this->load->view('login', $data);
			return false;
		}
		
		$get_record = $get_record[0];

		$this->load->library('email_template');
		$this->email_template->send_retrieve_password_email1($get_record->USER_ID, $get_record->USER_NAME, $get_record->USER_EMAIL);
		
		$data['title'] = 'Request Accepted!!';
		$data['body'] = 'Your request has been accepted! The system sent you an email with a link. Please check your email Inbox or Spam folder. Using the link, you can reset your Password. <br /><br />Please note that; the link will <strong>valid only for following 3 days</strong>. So, please use the link before it will being useless.';
		$data['footer'] = $this->webspice->settings()->site_title.' Authority';
		
		# log
		$this->webspice->log_me('password_retrieve_request - '.$get_record->USER_EMAIL);
			
		$this->confirmation($data);

	}
	
	function change_password($param_user_id=null){		
		# $param_user_id -> when user's password has been expired
		
		$url_prefix = $this->webspice->settings()->site_url_prefix;
		$user_id = null;
		$data = null;
		$this->load->database();

		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');
		$this->form_validation->set_rules('new_password','new_password','required|trim|xss_clean');
		$this->form_validation->set_rules('repeat_password','repeat_password','required|trim|xss_clean');
		
		# verify access request through 'Forgot Password' - email URL
		$get_uri = $this->webspice->encrypt_decrypt($this->uri->segment(2), 'decrypt');
		$get_link = explode('|', $get_uri);

		# verify access request for password expiration
		if( !$this->uri->segment(2) ){
			$param_user_id ? $user_id = $this->webspice->encrypt_decrypt($param_user_id, 'encrypt') : $user_id = $this->input->post('user_id');
		}
		
		# verify the request
		if( isset($get_link[0]) && isset($get_link[1]) && $get_link[0] ){
			$user_id = $get_link[0];
		
			# the link is valid for only 3 days
			if( ((strtotime(date("Y-m-d"))-strtotime($get_link[1]))/86400) >3 ){
				$this->webspice->message_board('Sorry! Invalid link. Your link has been expired. Please send us your request again.');
				
				$this->webspice->force_redirect($url_prefix);
				return false;
			}
			
		}elseif( $user_id ){
			$data['user_id'] = $user_id;
			$user_id = $this->webspice->encrypt_decrypt($user_id, 'decrypt');
		}else{
			# log
			$this->webspice->log_me('unauthorized_access');
			$this->webspice->page_not_found();
			return false;
		}
		if( !$this->form_validation->run() ){
			$view = $this->load->view('change_password', $data, true);
			echo $view;
			exit;
		}
			
		# get User and verify the user
		$get_user = $this->db->query("SELECT * FROM TBL_USER WHERE USER_EMAIL=?", array($user_id))->result();
		if( !$get_user ){
			$this->webspice->page_not_found();
			return false;
		}

		# call verify_password_policy
		$this->verify_password_policy($get_user[0], 'change_password');

		# encrypt password
		$new_password = $this->webspice->encrypt_decrypt($this->input->post('new_password'), 'encrypt');
		
		# generate password history - last 2 password does not allowed as a new password
		$previous_history = array();
		if($get_user[0]->USER_PASSWORD_HISTORY){
			$previous_history = explode(',', $get_user[0]->USER_PASSWORD_HISTORY);
		}
		
		array_unshift($previous_history, $new_password);
		if(count($previous_history) > 2){
			#last 2 password does not allowed as a new password
			array_pop($previous_history);
		}
		
		$password_history = implode(',', $previous_history);
		
		#change status for New user
		$STATUS=$get_user[0]->STATUS;
		if( $STATUS == 6 || $STATUS == 8 ){
			$STATUS = 7;
		}

		# update password
		$update = $this->db->query("UPDATE TBL_USER SET USER_PASSWORD=?, UPDATED_DATE=TO_DATE(?,'yyyy-mm-dd hh:mi:ss'), USER_PASSWORD_HISTORY=?, STATUS=? WHERE USER_EMAIL=?", array($new_password, $this->webspice->now(), $password_history, $STATUS, $user_id));
		if( !$update ){
			# log
			$this->webspice->log_me('error:password_changed');
			$this->webspice->message_board('We could not reset your Password. Please try again later or report to Authority.');
			$this->webspice->force_redirect($url_prefix);
			return false;
		}
		
		# log
		$this->webspice->log_me('password_changed');
		
		# user session destroy
		session_destroy();
		session_start();
		
		$this->webspice->message_board('Your password has been changed! Please login using your new password.');
		$this->webspice->force_redirect($url_prefix.'login');
		
	}
	
	function user_login_time_update(){
		if( $this->webspice->get_user_id() ){
			$this->db->query("UPDATE TBL_USER SET LOGIN_UPDATE_TIME=TO_DATE(?,'yyyy-mm-dd hh:mi:ss') WHERE USER_ID=?", array($this->webspice->now(), $this->webspice->get_user_id()));
			return 'updated';
		}
		
		return 'no_user';
	}
	
	function logout(){
		$this->webspice->log_me('signed_out'); # log
		
		# remove user session
		if( $this->webspice->get_user_id() ){
			$this->db->query("UPDATE TBL_USER SET IS_LOGGED = 0, LOGIN_UPDATE_TIME=NULL WHERE USER_ID=?", array($this->webspice->get_user_id()));
		}
		
		session_destroy();
		session_start();
		$data['title'] = 'You have been signed out of this account.';
		$data['body'] = 'You have been signed out of this account. To continue using this account, you will need to sign in again.  This is done to protect your account and to ensure the privacy of your information. We hope that, you will come back soon.';
		$data['footer'] = $this->webspice->settings()->domain_name;

		$this->confirmation($data);

		$this->webspice->force_redirect($this->webspice->settings()->site_url_prefix);
	}
	
	function verify_password_policy($user, $type){
		# $type can be login or change_password
		$user = (object)$user;
		$exipiry_period = 45;

		if( $type=='login' ){
			$pwd_change_duration = strtotime(date("Y-m-d")) - strtotime($user->UPDATED_DATE);
			$pwd_change_duration = round($pwd_change_duration / ( 3600 * 24 ));

			if( $user->UPDATED_DATE && $pwd_change_duration >= $exipiry_period ){
				$this->webspice->message_board("Your password is too old. Please change your password!");
				$this->change_password($user->USER_ID);
			}
			
		}elseif( $type=='change_password' ){
			$password = $this->input->post('new_password');
			$message = null;
			
			# minimum 8 charecters
			if( strlen($password) < 8 ){
				$message .= '- Password must be minimum 8 characters<br />';
			}
			
			# must have at least one capital letter, one small letter, one digit and one special character
			$containsCapitalLetter  = preg_match('/[A-Z]/', $password);
			$containsSmallLetter  = preg_match('/[a-z]/', $password);
			$containsDigit   = preg_match('/\d/', $password);
			$containsSpecial = preg_match('/[^a-zA-Z\d]/', $password);
			
			$containsAll = $containsCapitalLetter && $containsSmallLetter && $containsDigit && $containsSpecial;
			if( !$containsAll ){
				$message .= '- Password must have at least one Capital Letter<br />- Password must have at least one Small Letter<br />- Password must have at least one Digit<br />- Password must have at least one Special Character';
			}
			
			# password history verify - not allowed last 2 password
			$password_history = $user->USER_PASSWORD_HISTORY;
			if($password_history){
				$password_history = explode(',', $password_history);
				foreach($password_history as $k=>$v){
					if( $password == $this->webspice->encrypt_decrypt($v,'decrypt') ){ 
						$message .= '- You are not allowed to use your last 2 password'; 
					}
				}
				
			}
			
			# if policy breaks
			if( $message ){
				$this->webspice->message_board('<span class="stitle"><strong>You must maintain the following password policy(s):</strong><br />'.$message.'</span>');
				
				$data['user_id'] = $this->webspice->encrypt_decrypt($user->USER_EMAIL, 'encrypt');
				
				$view = $this->load->view('change_password', $data, true);
				echo $view;	
				exit;
			}

			return true;
			
		} # end if
		
	}

	//call confirmation for redirect another url with message
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

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */