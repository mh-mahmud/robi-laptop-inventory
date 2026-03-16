<?php
class CustomCache{
	
	# starts session
	function CustomCache(){
		if(!isset($_SESSION)){
			session_start();
		}
	}

	# get service information by service id
	function option_id_maker($key, $output_filed){
		# $output_filed - get db field name
		# key must be ID

		$CI =&get_instance();
		$group_name = 'option';
		$cache_name = 'option_id_maker';
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		#$CI->cache->remove_group($group_name);
		
		$html = null;
		
		if( !$html = $CI->cache->get($cache_name, $group_name) ){
			$html = array();
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_OPTION");
			$get_record = $get_record->result();
			
			foreach( $get_record as $k=>$v ){
				$html[$v->OPTION_ID] = $v->OPTION_VALUE;
			}

			$CI->cache->save($cache_name, $html, $group_name, 604800);		
		}
		
		if( !$html ){ $html = array(); }
	
		foreach($html as $k=>$v){
			#$Value = explode('|', $v);
			#$key_field = $Value[1]; # default

			if( $v == $key ){
				return $k;

			}
		}
		#dd($Value[1]);
		return false;
	}

	# get division information by division id
	function division_maker($key, $output_filed){
		# $output_filed - get db field name
		# key must be ID

		$CI =&get_instance();
		$group_name = 'option';
		$cache_name = 'division_maker';
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		
		$html = null;
		
		if( !$html = $CI->cache->get($cache_name, $group_name) ){
			$html = array();
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_OPTION WHERE GROUP_NAME='division'");
			$get_record = $get_record->result();
			
			foreach( $get_record as $k=>$v ){
				$html[] = $v->OPTION_ID.'|'.$v->OPTION_VALUE.'|'.$v->STATUS;
			}

			$CI->cache->save($cache_name, $html, $group_name, 604800);		
		}
		
		if( !$html ){ $html = array(); }
	
		foreach($html as $k=>$v){
			$Value = explode('|', $v);
			$key_field = $Value[0]; # default

			if( $key_field == $key ){
				switch($output_filed){
					case 'OPTION_ID': return $Value[0]; break;
					case 'OPTION_VALUE': return $Value[1]; break;
					case 'STATUS': return $Value[2]; break;
				}
				
			}

		}

		return false;
	}

	# get department information by department id
	function department_maker($key, $output_filed){
		# $output_filed - get db field name
		# key must be ID

		$CI =&get_instance();
		$group_name = 'option';
		$cache_name = 'department_maker';
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		
		$html = null;
		
		if( !$html = $CI->cache->get($cache_name, $group_name) ){
			$html = array();
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_OPTION WHERE GROUP_NAME='department'");
			$get_record = $get_record->result();
			
			foreach( $get_record as $k=>$v ){
				$html[] = $v->OPTION_ID.'|'.$v->OPTION_VALUE.'|'.$v->STATUS;
			}

			$CI->cache->save($cache_name, $html, $group_name, 604800);		
		}
		
		if( !$html ){ $html = array(); }
	
		foreach($html as $k=>$v){
			$Value = explode('|', $v);
			$key_field = $Value[0]; # default

			if( $key_field == $key ){
				switch($output_filed){
					case 'OPTION_ID': return $Value[0]; break;
					case 'OPTION_VALUE': return $Value[1]; break;
					case 'STATUS': return $Value[2]; break;
				}
				
			}

		}

		return false;
	}
	
	# get service information by service id
	function designation_maker($key, $output_filed){
		# $output_filed - get db field name
		# key must be ID

		$CI =&get_instance();
		$group_name = 'option';
		$cache_name = 'designation_maker';
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		
		$html = null;
		
		if( !$html = $CI->cache->get($cache_name, $group_name) ){
			$html = array();
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_OPTION WHERE GROUP_NAME='designation'");
			$get_record = $get_record->result();
			
			foreach( $get_record as $k=>$v ){
				$html[] = $v->OPTION_ID.'|'.$v->OPTION_VALUE.'|'.$v->STATUS;
			}

			$CI->cache->save($cache_name, $html, $group_name, 604800);		
		}
		
		if( !$html ){ $html = array(); }
	
		foreach($html as $k=>$v){
			$Value = explode('|', $v);
			$key_field = $Value[0]; # default

			if( $key_field == $key ){
				switch($output_filed){
					case 'OPTION_ID': return $Value[0]; break;
					case 'OPTION_VALUE': return $Value[1]; break;
					case 'STATUS': return $Value[2]; break;
				}
				
			}

		}

		return false;
	}
	
	# get employee type information by service id
	function employee_type_maker($key, $output_filed){
		# $output_filed - get db field name
		# key must be ID

		$CI =&get_instance();
		$group_name = 'option';
		$cache_name = 'user_type_maker';
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		
		$html = null;
		
		if( !$html = $CI->cache->get($cache_name, $group_name) ){
			$html = array();
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_OPTION WHERE GROUP_NAME='employee_type'");
			$get_record = $get_record->result();
			
			foreach( $get_record as $k=>$v ){
				$html[] = $v->OPTION_ID.'|'.$v->OPTION_VALUE.'|'.$v->STATUS;
			}

			$CI->cache->save($cache_name, $html, $group_name, 604800);		
		}
		
		if( !$html ){ $html = array(); }
	
		foreach($html as $k=>$v){
			$Value = explode('|', $v);
			$key_field = $Value[0]; # default

			if( $key_field == $key ){
				switch($output_filed){
					case 'OPTION_ID': return $Value[0]; break;
					case 'OPTION_VALUE': return $Value[1]; break;
					case 'STATUS': return $Value[2]; break;
				}
				
			}

		}

		return false;
	}
	
	function purpose_maker($key, $output_filed ){
		# $output_filed - get db field name
		# key must be ID

		$CI =&get_instance();
		$group_name = 'option';
		$cache_name = 'purpose_maker';

		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		
		$html = null;
		
		if( !$html = $CI->cache->get($cache_name, $group_name) ){
			$html = array();
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_OPTION WHERE GROUP_NAME='purpose_name'");
			$get_record = $get_record->result();
			
			foreach( $get_record as $k=>$v ){
				$html[] = $v->OPTION_ID.'|'.$v->OPTION_VALUE.'|'.$v->STATUS;
			}

			$CI->cache->save($cache_name, $html, $group_name, 604800);		
		}
		
		if( !$html ){ $html = array(); }
	
		foreach($html as $k=>$v){
			$Value = explode('|', $v);
			
			$key_field = $Value[0]; # default

			if( $key_field == $key ){
				switch($output_filed){
					case 'OPTION_ID': return $Value[0]; break;
					case 'OPTION_VALUE': return $Value[1]; break;
					case 'STATUS': return $Value[2]; break;
				}
				
			}

		}

		return false;
	}
	
	# get laptop information by stock id
	function laptop_maker($key, $output_filed){
	  # $output_filed - get db field name
	
	  $CI =&get_instance();
	  $group_name = 'laptop';
	  $cache_name = 'laptop_maker';
	  
	  # to delete cache use: $this->cache->remove_group('laptop');
	  $CI->load->library('cache');
	  $html = null;
	  if( !$html = $CI->cache->get($cache_name, $group_name) ){
		 $html = array();
	   $CI->load->database();
	   $get_record = $CI->db->query("SELECT * FROM TBL_STOCK ORDER BY STOCK_ID DESC");
	   $get_record = $get_record->result();
	   
	   foreach( $get_record as $k=>$v ){
	    $html[] = $v->STOCK_ID.'|'.$v->LAPTOP_SR_NO.'|'.$v->BRAND.'|'.$v->MODEL.'|'.$v->PO_NO.'|'.$v->LAPTOP_PREVIOUS_SR_NO.'|'.
	         $v->PURCHASE_DATE.'|'.$v->EOL_DATE.'|'.$v->REPLACE_DATE.'|'.$v->USER_ID.'|'.$v->STATUS;
	   }
	   $CI->cache->save($cache_name, $html, $group_name, 604800);  
	  }
	  
		if( !$html ){ $html = array(); }
	  
	  foreach($html as $k=>$v){
	   $Value = explode('|', $v);
	   if( $Value[1]==$key ){
	    switch($output_filed){
	     case 'STOCK_ID': return $Value[0]; break;
	     case 'LAPTOP_SR_NO': return $Value[1]; break;
	     case 'BRAND': return $Value[2]; break;
	     case 'MODEL': return $Value[3]; break;
	     case 'PO_NO': return $Value[4]; break;
	     case 'LAPTOP_PREVIOUS_SR_NO': return $Value[5]; break;
	     case 'PURCHASE_DATE': return $Value[6]; break;
	     case 'EOL_DATE': return $Value[7]; break;
	     case 'REPLACE_DATE': return $Value[8]; break;
	     case 'USER_ID': return $Value[9]; break;
	     case 'STATUS': return $Value[10]; break;
	    }
	   }
	  }
	  return false;
	 }
	 
	 
	# get laptop user information by stock id
	function laptop_user_maker($key, $output_filed){
	  # $output_filed - get db field name
	
	  $CI =&get_instance();
	  $group_name = 'laptop_user';
	  $cache_name = 'laptop_user_maker';
	  
	  # to delete cache use: $this->cache->remove_group('group_name');
	  $CI->load->library('cache');
	  $html = null;
	  if( !$html = $CI->cache->get($cache_name, $group_name) ){
	   $html = array();
	   $CI->load->database();
	   $get_record = $CI->db->query("SELECT * FROM TBL_LAPTOP_USER ORDER BY USER_ID DESC");
	   $get_record = $get_record->result();
	   
	   foreach( $get_record as $k=>$v ){
	    $html[] = $v->USER_ID.'|'.$v->USER_NAME.'|'.$v->USER_EMAIL.'|'.$v->USER_PHONE.'|'.$v->USER_DIVISION.'|'.$v->USER_DEPARTMENT.'|'.
	         $v->USER_DESIGNATION.'|'.$v->USER_TYPE.'|'.$v->JOINING_DATE.'|'.$v->STATUS.'|'.$v->EMPLOYEE_ID;
	   }
	
	   $CI->cache->save($cache_name, $html, $group_name, 604800);  
	  }
	  
		if( !$html ){ $html = array(); }
	  
	  foreach($html as $k=>$v){
	   $Value = explode('|', $v);
	   if( $Value[10]==$key ){
	    switch($output_filed){
	     case 'USER_ID': return $Value[0]; break;
	     case 'USER_NAME': return $Value[1]; break;
	     case 'USER_EMAIL': return $Value[2]; break;
	     case 'USER_PHONE': return $Value[3]; break;
	     case 'USER_DIVISION': return $Value[4]; break;
	     case 'USER_DEPARTMENT': return $Value[5]; break;
	     case 'USER_DESIGNATION': return $Value[6]; break;
	     case 'USER_TYPE': return $Value[7]; break;
	     case 'JOINING_DATE': return $Value[8]; break;
	     case 'STATUS': return $Value[9]; break;
	     case 'EMPLOYEE_ID': return $Value[10]; break;
	    }
	    
	   }
	
	  }
	
	  return false;
	 }
	
	function get_division($type='option'){
		# type = option/option_mix/list
		$CI =&get_instance();
		$group_name = 'option';
		$temp_cache_name = 'get_division';
		
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		switch($type){
			case 'option': $cache_name = $temp_cache_name.'_option'; break;
			default: $cache_name = $temp_cache_name.'_option'; break;
		}
		
		if( !$data['html'] = $CI->cache->get($cache_name, $group_name) ){
			$data['html'] = null;
			
			$CI->load->database();
			$get_record = $CI->db->query("SELECT DISTINCT USER_DIVISION FROM TBL_LAPTOP_USER");
			$get_record = $get_record->result();

			foreach( $get_record as $k=>$v ){
				switch($type){
					case 'option':
						$data['html'] .= '<option value="'.$v->USER_DIVISION.'">'.ucwords($v->USER_DIVISION).'</option>';
						break;
				}
			}

			$CI->cache->save($cache_name, $data['html'], $group_name, 604800);		
		}
		
		return $data['html'];
	}
	function get_department($type='option'){
		# type = option/option_mix/list
		$CI =&get_instance();
		$group_name = 'option';
		$temp_cache_name = 'get_department';
		
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		switch($type){
			case 'option': $cache_name = $temp_cache_name.'_option'; break;
			default: $cache_name = $temp_cache_name.'_option'; break;
		}
		
		if( !$data['html'] = $CI->cache->get($cache_name, $group_name) ){
			$data['html'] = null;
			
			$CI->load->database();
			$get_record = $CI->db->query("SELECT DISTINCT USER_DEPARTMENT FROM TBL_LAPTOP_USER");
			$get_record = $get_record->result();

			foreach( $get_record as $k=>$v ){
				switch($type){
					case 'option':
						$data['html'] .= '<option value="'.$v->USER_DEPARTMENT.'">'.ucwords($v->USER_DEPARTMENT).'</option>';
						break;
				}
			}

			$CI->cache->save($cache_name, $data['html'], $group_name, 604800);		
		}
		
		return $data['html'];
	}
	function get_designation($type='option'){
		# type = option/option_mix/list
		$CI =&get_instance();
		$group_name = 'option';
		$temp_cache_name = 'get_designation';
		
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		switch($type){
			case 'option': $cache_name = $temp_cache_name.'_option'; break;
			default: $cache_name = $temp_cache_name.'_option'; break;
		}
		
		if( !$data['html'] = $CI->cache->get($cache_name, $group_name) ){
			$data['html'] = null;
			
			$CI->load->database();
			$get_record = $CI->db->query("SELECT DISTINCT USER_DESIGNATION FROM TBL_LAPTOP_USER");
			$get_record = $get_record->result();

			foreach( $get_record as $k=>$v ){
				switch($type){
					case 'option':
						$data['html'] .= '<option value="'.$v->USER_DESIGNATION.'">'.ucwords($v->USER_DESIGNATION).'</option>';
						break;
				}
			}

			$CI->cache->save($cache_name, $data['html'], $group_name, 604800);		
		}
		
		return $data['html'];
	}
	function get_employee_type($type='option'){
		# type = option/option_mix/list
		$CI =&get_instance();
		$group_name = 'option';
		$temp_cache_name = 'get_employee_type';
		
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		switch($type){
			case 'option': $cache_name = $temp_cache_name.'_option'; break;
			case 'option_mix': $cache_name = $temp_cache_name.'_option_mix'; break;
			case 'option_name': $cache_name = $temp_cache_name.'_option_name'; break;
			case 'list': $cache_name = $temp_cache_name.'_list'; break;
			default: $cache_name = $temp_cache_name.'_option'; break;
		}
		
		if( !$data['html'] = $CI->cache->get($cache_name, $group_name) ){
			$data['html'] = null;
			
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_OPTION WHERE STATUS=7 AND GROUP_NAME='employee_type' ORDER BY GROUP_NAME");
			$get_record = $get_record->result();

			foreach( $get_record as $k=>$v ){
				switch($type){
					case 'option':
						$data['html'] .= '<option value="'.$v->OPTION_ID.'">'.ucwords($v->OPTION_VALUE).'</option>';
						break;
					case 'option_mix':
						$data['html'] .= '<option value="'.$v->OPTION_ID.'|'.$v->OPTION_VALUE.'">'.ucwords($v->OPTION_VALUE).'</option>';
						break;
					case 'option_name':
						$data['html'] .= '<option value="'.$v->OPTION_VALUE.'">'.ucwords($v->OPTION_VALUE).'</option>';
						break;
					case 'list':
						$data['html'] .= '<li class="list_item" data-id="'.$v->OPTION_ID.'">'.ucwords($v->OPTION_VALUE).'</li>';
						break;
				}
			}

			$CI->cache->save($cache_name, $data['html'], $group_name, 604800);		
		}
		
		return $data['html'];
	}
	
	function get_purpose($type='option'){
		# type = option/option_mix/list
		$CI =&get_instance();
		$group_name = 'option';
		$temp_cache_name = 'get_purpose';
		
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		switch($type){
			case 'option': $cache_name = $temp_cache_name.'_option'; break;
			case 'option_mix': $cache_name = $temp_cache_name.'_option_mix'; break;
			case 'option_name': $cache_name = $temp_cache_name.'_option_name'; break;
			case 'list': $cache_name = $temp_cache_name.'_list'; break;
			default: $cache_name = $temp_cache_name.'_option'; break;
		}
		
		if( !$data['html'] = $CI->cache->get($cache_name, $group_name) ){
			$data['html'] = null;
			
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_OPTION WHERE STATUS=7 AND GROUP_NAME='purpose_name' ORDER BY GROUP_NAME");
			$get_record = $get_record->result();

			foreach( $get_record as $k=>$v ){
				switch($type){
					case 'option':
						$data['html'] .= '<option value="'.$v->OPTION_ID.'">'.ucwords($v->OPTION_VALUE).'</option>';
						break;
					case 'option_mix':
						$data['html'] .= '<option value="'.$v->OPTION_ID.'|'.$v->OPTION_VALUE.'">'.ucwords($v->OPTION_VALUE).'</option>';
						break;
					case 'option_name':
						$data['html'] .= '<option value="'.$v->OPTION_VALUE.'">'.ucwords($v->OPTION_VALUE).'</option>';
						break;
					case 'list':
						$data['html'] .= '<li class="list_item" data-id="'.$v->OPTION_ID.'">'.ucwords($v->OPTION_VALUE).'</li>';
						break;
				}
			}

			$CI->cache->save($cache_name, $data['html'], $group_name, 604800);		
		}
		
		return $data['html'];
	}
	
	
	# get user information by user id
	function user_maker($user_id, $output_filed){
		# $output_filed - get db field name

		$CI =&get_instance();
		$cache_name = 'user_maker';
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		
		$html = null;
		
		if( !$html = $CI->cache->get($cache_name, 'user') ){
			$html = array();
			
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_USER ORDER BY USER_ID DESC");
			$get_record = $get_record->result();
			
			foreach( $get_record as $k=>$v ){
				$html[] = $v->USER_ID.'|'.$v->ROLE_ID.'|'.$v->USER_NAME.'|'.$v->USER_EMAIL.'|'.$v->USER_PHONE.'|'.$v->CREATED_DATE.'|'.
									$v->UPDATED_DATE.'|'.$v->STATUS;
			}

			$CI->cache->save($cache_name, $html, 'user', 604800);		
		}
		
		if( !$html ){ $html = array(); }
		
		foreach($html as $k=>$v){
			$Value = explode('|', $v);
			if( $Value[0]==$user_id ){
				switch($output_filed){
					case 'USER_ID': return $Value[0]; break;
					case 'ROLE_ID': return $Value[1]; break;
					case 'USER_NAME': return $Value[2]; break;
					case 'USER_EMAIL': return $Value[3]; break;
					case 'USER_PHONE': return $Value[4]; break;
					case 'USER_CREATED_DATE': return $Value[5]; break;
					case 'USER_UPDATED_DATE': return $Value[6]; break;
					case 'STATUS': return $Value[7]; break;
				}
				
			}

		}

		return false;
	}

	
	# get user role
	function get_user_role($type='option'){
		# type = option/option_mix/list
		$CI =&get_instance();
		$cache_name = 'user_role_option';
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		$type == 'option_mix' ? $cache_name = 'user_role_option_mix' : $cache_name = $cache_name;
		$type == 'list' ? $cache_name = 'user_role_list' : $cache_name = $cache_name;
		
		if( !$data['html'] = $CI->cache->get($cache_name, 'user') ){
			$data['html'] = null;
			
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_ROLE WHERE STATUS=7 ORDER BY ROLE_NAME");
			$get_record = $get_record->result();
		
			foreach( $get_record as $k=>$v ){
				switch($type){
					case 'option':
						$data['html'] .= '<option value="'.$v->ROLE_ID.'">'.ucwords($v->ROLE_NAME).'</option>';
						break;
					case 'option_mix':
						$data['html'] .= '<option value="'.$v->ROLE_ID.'|'.$v->ROLE_NAME.'">'.ucwords($v->ROLE_NAME).'</option>';
						break;
					case 'list':
						$data['html'] .= '<li class="list_item" data-id="'.$v->ROLE_ID.'">'.ucwords($v->ROLE_NAME).'</li>';
						break;
				}
			}

			$CI->cache->save($cache_name, $data['html'], 'user', 604800);		
		}
		
		return $data['html'];
	}
	
	# get unit name
	function get_unit($type='option'){
		# type = option/option_mix/list
		$CI =&get_instance();
		$temp_cache_name = 'unit';
		$group_name = 'option';
		
		# to delete cache use: $this->cache->remove_group('group_name');
		$CI->load->library('cache');
		switch($type){
			case 'option': $cache_name = $temp_cache_name.'_option'; break;
			case 'option_mix': $cache_name = $temp_cache_name.'_option_mix'; break;
			case 'option_name': $cache_name = $temp_cache_name.'_option_name'; break;
			case 'list': $cache_name = $temp_cache_name.'_list'; break;
			default: $cache_name = $temp_cache_name.'_option'; break;
		}
		
		if( !$data['html'] = $CI->cache->get($cache_name, $group_name) ){
			$data['html'] = null;
			
			$CI->load->database();
			$get_record = $CI->db->query("SELECT * FROM TBL_OPTION WHERE STATUS=7 AND GROUP_NAME='unit_name' ORDER BY GROUP_NAME");
			$get_record = $get_record->result();

			foreach( $get_record as $k=>$v ){
				switch($type){
					case 'option':
						$data['html'] .= '<option value="'.$v->OPTION_ID.'">'.ucwords($v->OPTION_VALUE).'</option>';
						break;
					case 'option_mix':
						$data['html'] .= '<option value="'.$v->OPTION_ID.'|'.$v->ROLE_NAME.'">'.ucwords($v->OPTION_VALUE).'</option>';
						break;
					case 'option_name':
						$data['html'] .= '<option value="'.$v->OPTION_VALUE.'">'.ucwords($v->OPTION_VALUE).'</option>';
						break;
					case 'list':
						$data['html'] .= '<li class="list_item" data-id="'.$v->OPTION_ID.'">'.ucwords($v->OPTION_VALUE).'</li>';
						break;
				}
			}

			$CI->cache->save($cache_name, $data['html'], $group_name, 604800);		
		}
		
		return $data['html'];
	}

}