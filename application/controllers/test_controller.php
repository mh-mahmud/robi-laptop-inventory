<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test_controller extends CI_Controller {
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

	function test(){
		$search_filter = 'sAMAccountName=golam.mohiuddin';
		$user = $this->webspice->get_ad_user('Administrators', 'administrator', 'nns@1212', $ldap_host='192.168.10.10', $ldap_dn='DC=nns-solution,DC=net', $base_dn='DC=nns-solution,DC=net', $ldap_user_domain='@nns-solution.net', $search_filter);
		dd($user);
		
		
		
	}
	
	
	function test_2(){
			$adServer = "192.168.10.10";
			
			$ldap = ldap_connect($adServer);
			$username = 'golam.mohiuddin';
			$password = '12345';

			$ldaprdn = '192.168.10.10' . "\\" . $username;

			ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

			$bind = @ldap_bind($ldap, $ldaprdn, $password);

dd($bind);
			if ($bind) {
				$filter="(sAMAccountName=$username)";
				$result = ldap_search($ldap,"dc=nns-solution,dc=net",$filter);
				ldap_sort($ldap,$result,"sn");
				$info = ldap_get_entries($ldap, $result);
				for ($i=0; $i<$info["count"]; $i++)
				{
					if($info['count'] > 1)
						break;
					echo "<p>You are accessing <strong> ". $info[$i]["sn"][0] .", " . $info[$i]["givenname"][0] ."</strong><br /> (" . $info[$i]["samaccountname"][0] .")</p>\n";
					echo '<pre>';
					var_dump($info);
					echo '</pre>';
					$userDn = $info[$i]["distinguishedname"][0]; 
				}
				@ldap_close($ldap);
			} else {
				$msg = "Invalid email address / password";
				echo $msg;
			}

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