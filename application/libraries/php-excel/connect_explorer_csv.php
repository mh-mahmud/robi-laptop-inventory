<?php
//dd($file_name);
error_reporting(E_ALL ^ E_NOTICE);

$data = file($file_name);
$count_update = $count_insert =  0;


$get_user = DB::select("SELECT users.*, 
												branch.id as 'branch_branch_id', branch.name as 'branch_name' 
												FROM users 
												LEFT JOIN branch ON branch.id=users.branch_id");
												
if( $data && isset($data[1]) ){
	for($row=1; $row < count($data); $row++){
		$is_exist = null;
		$cols = explode(',', $data[$row]);

		if( isset($cols[0]) && isset($cols[1]) && isset($cols[2]) && isset($cols[3]) ){
			
			foreach($get_user as $kUser=>$vUser){
				# user id matched and user information has been changed
				if( trim($cols[0])==$vUser->user_id && (trim($cols[1])!=$vUser->name || trim($cols[2])!=$vUser->email || trim($cols[3])!=$vUser->branch_name) ){
					# get branch info and check existence - if not found; insert new one 
					$branch_id = null;
					$get_branch = DB::select( "SELECT * FROM branch WHERE branch.name=?", array(trim($cols[3])) );
					if( !$get_branch ){
						try{
							DB::insert("INSERT INTO branch(name, address, contact, email, status, created_by, created_at) 
													VALUES(?, 'n/a', 'n/a', 'n/a', 1, 1, ?)",
													array(trim($cols[3]), date('Y-m-d')));
							$branch_id = DB::getPdo()->lastInsertId();
						}catch(Exception $e){}
						
					}else{
						$branch_id = $get_branch[0]->id;
					}
					
					# update user information
					try{
						$count_update += 1;
						DB::update("UPDATE users SET name=?, email=?, branch_id=? WHERE user_id=? LIMIT 1", 
												array(trim($cols[1]), trim($cols[2]), $branch_id, $vUser->user_id));
					}catch(Exception $e){}
					
					$is_exist = 'yes';
					break;
					
				}else if( trim($cols[0])==$vUser->user_id ){
					# user id matched and user information has not changed
					$is_exist = 'yes';
					break;
				}
				
			}
			
			if( !$is_exist ){
				$branch_id = null;

				# get branch and check existence - if not found; insert new one
				$get_branch = DB::select( "SELECT * FROM branch WHERE branch.name=?", array(trim($cols[3])) );
				if( !$get_branch ){
					try{
						DB::insert("INSERT INTO branch(name, address, contact, email, status, created_by, created_at) VALUES(?, 'n/a', 'n/a', 'n/a', 1, 1, ?)",
												array(trim($cols[3]), date('Y-m-d')));
						$branch_id = DB::getPdo()->lastInsertId();
					}catch(Exception $e){}
					
				}else{
					$branch_id = $get_branch[0]->id;
				}
				
				# insert user
				try{
					# echo trim($cols[0]).'-'.$branch_id.'-'.trim($cols[1]).'-'.trim($cols[2]).'-'.date('Y-m-d');
					# 1234 = $2y$10$ALWKiA8J6pBv6EyYRRWOtutVrUZh1zcavVCeACKFfRT87RZYVRJ7e
					# 1234 - default password for a new user; after login using default password - he/she must change his/her password
					$count_insert += 1;
					DB::insert("INSERT INTO users(user_id, branch_id, mobile_no, name, email, password, status, user_status, created_by, created_at)
											VALUES(?, ?, NULL, ?, ?, ?, 1, 9, 1, ?)", 
											array(trim($cols[0]), $branch_id, trim($cols[1]), trim($cols[2]), '$2y$10$ALWKiA8J6pBv6EyYRRWOtutVrUZh1zcavVCeACKFfRT87RZYVRJ7e', date('Y-m-d')));
				}catch(Exception $e){
					echo 'Error ('.trim($cols[0]).')<br />';
				}
			}
		}
		
	}
}
MyLibrary::message_board('File succesfully uploaded.<br />Insert Record:'.$count_insert.'<br />Update Record:'.$count_update);
MyLibrary::force_redirect($url_prefix.'_dashboard');
return false;
?>