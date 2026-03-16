<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $this->webspice->settings()->domain_name; ?>: Welcome</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	
	<?php include(APPPATH."views/global.php"); ?>
</head>

<body>
	<div id="wrapper">
		<div><?php include(APPPATH."views/header.php"); ?></div>
		
		<div id="page_create_employee" class="main_container page_identifier">
			<div class="page_caption">Create User</div>
			<div class="page_body">
				
				<div class="left_section">
					<fieldset class="divider"><legend>Please enter user information</legend></fieldset>
				
					<div class="stitle">* Mandatory Field</div>
					
					<form id="frm_create_user" method="post" action="" data-parsley-validate>
						<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
						<input type="hidden" name="user_id" value="<?php if( isset($edit['USER_ID']) && $edit['USER_ID'] ){echo $this->webspice->encrypt_decrypt($edit['USER_ID'], 'encrypt');} ?>" />
						<table width="100%">
							<tr>
								<td>
									<div class="form_label">User ID*</div>
									<div>
										<input type="text"  class="input_full input_style" id="employee_id" name="employee_id" value="<?php echo set_value('employee_id',$edit['EMPLOYEE_ID']); ?>"  required />
										<span class="fred"><?php echo form_error('employee_id'); ?></span>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form_label">User Name*</div>
									<div>
										<input type="text"  class="input_full input_style" id="user_name" name="user_name" value="<?php echo set_value('user_name',$edit['USER_NAME']); ?>"  required />
										<span class="fred"><?php echo form_error('user_name'); ?></span>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form_label">User Email</div>
									<div>
										<input type="email" class="input_full input_style" id="user_email" name="user_email" value="<?php echo set_value('user_email',$edit['USER_EMAIL']); ?>"  required />
										<span class="fred"><?php echo form_error('user_email'); ?></span>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form_label">User Phone*</div>
									<div>
										<input type="text" class="input_full input_style" id="user_phone" name="user_phone" value="<?php echo set_value('user_phone',$edit['USER_PHONE']); ?>"  required />
										<span class="fred"><?php echo form_error('user_phone'); ?></span>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form_label">User Division*</div>
									<div>
		               <select name="user_division" class="input_full input_style" required>
				              <option value="">Select One</option>
				              <?php if( set_value('user_division', $edit['USER_DIVISION']) ): ?>
				              <?php echo str_replace('value="'.set_value('user_division', $edit['USER_DIVISION']).'"','value="'.set_value('user_division', $edit['USER_DIVISION']).'" selected="selected"', $this->customcache->get_division()); ?>
				              <?php else: ?>
				              <?php echo $this->customcache->get_division(); ?>
				              <?php endif; ?>
	             			</select>
	            			<span class="fred"><?php echo form_error('user_division'); ?></span> 
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form_label">User Department*</div>
									<div>
		               <select name="user_department" class="input_full input_style" required>
				              <option value="">Select One</option>
				              <?php if( set_value('user_department', $edit['USER_DEPARTMENT']) ): ?>
				              <?php echo str_replace('value="'.set_value('user_department', $edit['USER_DEPARTMENT']).'"','value="'.set_value('user_department', $edit['USER_DEPARTMENT']).'" selected="selected"', $this->customcache->get_department()); ?>
				              <?php else: ?>
				              <?php echo $this->customcache->get_department(); ?>
				              <?php endif; ?>
	             			</select>
	            			<span class="fred"><?php echo form_error('user_department'); ?></span> 
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form_label">User Designation*</div>
									<div>
		               <select name="user_designation" class="input_full input_style" required>
				              <option value="">Select One</option>
				              <?php if( set_value('user_designation', $edit['USER_DESIGNATION']) ): ?>
				              <?php echo str_replace('value="'.set_value('user_designation', $edit['USER_DESIGNATION']).'"','value="'.set_value('user_designation', $edit['USER_DESIGNATION']).'" selected="selected"', $this->customcache->get_designation()); ?>
				              <?php else: ?>
				              <?php echo $this->customcache->get_designation(); ?>
				              <?php endif; ?>
	             			</select>
	            			<span class="fred"><?php echo form_error('user_designation'); ?></span> 
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form_label">User Type*</div>
									<div>
		               <select name="user_type" class="input_full input_style" required>
				              <option value="">Select One</option>
				              <?php if( set_value('user_type', $edit['USER_TYPE']) ): ?>
				              <?php echo str_replace('value="'.set_value('user_type', $edit['USER_TYPE']).'"','value="'.set_value('user_type', $edit['USER_TYPE']).'" selected="selected"', $this->customcache->get_employee_type()); ?>
				              <?php else: ?>
				              <?php echo $this->customcache->get_employee_type(); ?>
				              <?php endif; ?>
	             			</select>
	            			<span class="fred"><?php echo form_error('user_type'); ?></span> 
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div><input type="submit" class="btn_gray" value="Submit Data" /></div>
								</td>
							</tr>
						</table>
					</form>
				</div>
				
				<div class="right_section">
					
				</div>
				<div class="float_clear_full">&nbsp;</div>
				
				
			</div>

		</div>
		
		<div id="footer_container"><?php include(APPPATH."views/footer.php"); ?></div>
		
	</div>
</body>
</html>