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
		
		<div id="page_manage_employee" class="main_container page_identifier">
			<div class="page_caption">Manage Employee</div>
			<div class="page_body table-responsive">
				<!--filter section-->
				<form id="frm_filter" method="post" action="" data-parsley-validate>
					<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
					
					<table style="width:auto;">
						<tr>
							<td>Keyword</td>
							<td>Division</td>
							<td>Department</td>
							<td>Designation</td>
							<td>Employee Type</td>
						</tr>
						<tr>
							<td>
	              <input type="text" name="SearchKeyword" class="input_style input_full" />
							</td>
							<td>
								<select name="USER_DIVISION" class="input_style input_full">
									<option value="">Select One</option>
									<?php echo $this->customcache->get_division(); ?>
								</select>
							</td>
							<td>
								<select name="USER_DEPARTMENT" class="input_style input_full">
									<option value="">Select One</option>
									<?php echo $this->customcache->get_department(); ?>
								</select>
							</td>
							<td>
								<select name="USER_DESIGNATION" class="input_style input_full">
									<option value="">Select One</option>
									<?php echo $this->customcache->get_designation(); ?>
								</select>
							</td>
							<td>
								<select name="USER_TYPE" class="input_style input_full">
									<option value="">Select One</option>
									<?php echo $this->customcache->get_employee_type(); ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="10">
								<input type="submit" name="filter" class="btn_gray" value="Filter Data" />
								<a class="btn_gray" href="<?php echo $url_prefix; ?>manage_employee">Refresh</a>
								<a class="btn_gray" href="<?php echo $url_prefix; ?>manage_employee/print" target="_blank">Print</a>
								<a class="btn_gray" href="<?php echo $url_prefix; ?>manage_employee/csv" target="_blank">Export</a>
							</td>
						</tr>
					</table>
          
				</form>
				
				<br />
				<?php if( !isset($filter_by) || !$filter_by ){$filter_by = 'All Data';} ?>
				<div class="breadcrumb">Filter By: <?php echo $filter_by; ?></div>
				<div style="overflow:auto">
				<table class="table table-bordered table-striped new_table">
					<tr>
						<th>SL No</th>
						<th>Employee ID</th>
						<th>User Name</th>
						<th>User Email</th>
						<th>User Phone</th>
						<th>Division</th>
						<th>Department</th>
						<th>Designation</th>
						<th>User Type</th>
						<th>Created Date</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
					<?php $i=1; foreach($get_record as $k=>$v): ?>
					<tr>
						<td><?php echo $i; ?></td>
						<td><?php echo $v->EMPLOYEE_ID; ?></td>
						<td><?php echo $v->USER_NAME; ?></td>
						<td><?php echo $v->USER_EMAIL; ?></td>
						<td><?php echo $v->USER_PHONE; ?></td>
						<td><?php echo $v->USER_DIVISION; ?></td>
						<td><?php echo $v->USER_DEPARTMENT; ?></td>
						<td><?php echo $v->USER_DESIGNATION; ?></td>
						<td><?php echo $this->customcache->employee_type_maker($v->USER_TYPE,'OPTION_VALUE'); ?></td>
						<td><?php echo $this->webspice->formatted_date($v->CREATED_DATE); ?></td>
						<td><?php echo $this->webspice->static_status($v->STATUS); ?></td>
						<td class="field_button">
							<?php if( $this->webspice->permission_verify('manage_employee',true) && $v->STATUS!=9 ): ?>
							<a href="<?php echo $url_prefix; ?>manage_employee/edit/<?php echo $this->webspice->encrypt_decrypt($v->USER_ID,'encrypt'); ?>" class="btn_orange">Edit</a>
							<?php endif; ?>
							
							<?php if( $this->webspice->permission_verify('manage_employee',true) && $v->STATUS==7 ): ?>
							<a href="<?php echo $url_prefix; ?>manage_employee/inactive/<?php echo $this->webspice->encrypt_decrypt($v->USER_ID,'encrypt'); ?>" class="btn_orange">Inactive</a>
							<?php endif; ?>
							
							<?php if( $this->webspice->permission_verify('manage_employee',true) && $v->STATUS==-7 ): ?>
							<a href="<?php echo $url_prefix; ?>manage_employee/active/<?php echo $this->webspice->encrypt_decrypt($v->USER_ID,'encrypt'); ?>" class="btn_orange">Active</a>
							<?php endif; ?>
						</td>
					</tr>
					<?php $i++; endforeach; ?>
				</table>
				</div>
				<div id="pagination"><?php echo $pager; ?><div class="float_clear_full">&nbsp;</div></div>
				
			</div><!--end .page_body-->

		</div>
		
		<div id="footer_container"><?php include(APPPATH."views/footer.php"); ?></div>
	</div>
</body>
</html>