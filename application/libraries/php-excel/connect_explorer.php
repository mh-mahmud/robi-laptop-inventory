<?php
//dd($file_name);
error_reporting(E_ALL ^ E_NOTICE);
require_once 'excel_reader2.php';

$data = new Spreadsheet_Excel_Reader($file_name);

$get_user = DB::select("SELECT users.*, 
												branch.id as 'branch_branch_id', branch.name as 'branch_name' 
												FROM users 
												LEFT JOIN branch ON branch.id=users.branch_id");
									
for($row=2; $row<= $data->rowcount($sheet_index=0); $row++){
//dd($data->val($row,4));
	$is_exist = null;
	if( $data->val($row,1) && $data->val($row,2) && $data->val($row,3) && $data->val($row,4) ){
		
		foreach($get_user as $kUser=>$vUser){
			# user id matched and user information has changed
			if($data->val($row,1)==$vUser->user_id && ($data->val($row,2)!=$vUser->name || $data->val($row,3)!=$vUser->email || $data->val($row,4)!=$vUser->branch_name) ){
				# branch has changed
				$branch_id = null;
				$get_branch = DB::select( "SELECT * FROM branch WHERE branch.name=?", array($data->val($row,4)) );
				if( !$get_branch ){
					try{
						DB::insert("INSERT INTO branch(name, address, contact, email, status, created_by, created_at) 
												VALUES(?, 'n/a', 'n/a', 'n/a', 1, 1, ?)",
												array($data->val($row,4), date('Y-m-d')));
						$branch_id = DB::getPdo()->lastInsertId();
					}catch(Exception $e){}
					
				}else{
					$branch_id = $get_branch[0]->id;
				}
				
				# update user information
				try{
					DB::update("UPDATE users SET name=?, email=?, branch_id=? WHERE user_id=? LIMIT 1", 
											array($data->val($row,2), $data->val($row,3), $branch_id, $vUser->user_id));
				}catch(Exception $e){}
				
				$is_exist = 'yes';
				break;
				
			}else if( $data->val($row,1)==$vUser->user_id ){
				# user id matched and user information has not changed
				$is_exist = 'yes';
				break;
			}
			
		}
		
		if( !$is_exist ){
			$branch_id = null;
			$get_branch = DB::select( "SELECT * FROM branch WHERE branch.name=?", array($data->val($row,4)) );
			if( !$get_branch ){
				try{
					DB::insert("INSERT INTO branch(name, address, contact, email, status, created_by, created_at) VALUES(?, 'n/a', 'n/a', 'n/a', 1, 1, ?)",array($data->val($row,4), date('Y-m-d')));
					$branch_id = DB::getPdo()->lastInsertId();
				}catch(Exception $e){}
				
			}else{
				$branch_id = $get_branch[0]->id;
			}
			
			# insert user
			try{
				# 1234 = $2y$10$ALWKiA8J6pBv6EyYRRWOtutVrUZh1zcavVCeACKFfRT87RZYVRJ7e
				DB::select("INSERT INTO users(user_id, branch_id, mobile_no, name, email, password, status, user_status, created_by, created_at)
										VALUES(?, ?, 'n/a', ?, ?, ?, 1, 9, 1, ?)", 
										array($data->val($row,1), $branch_id, $data->val($row,2), $data->val($row,3), '$2y$10$ALWKiA8J6pBv6EyYRRWOtutVrUZh1zcavVCeACKFfRT87RZYVRJ7e', date('Y-m-d')));
			}catch(Exception $e){}
		}
	}
	
}

dd('done');
?>