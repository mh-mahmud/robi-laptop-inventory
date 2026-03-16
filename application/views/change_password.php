<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $this->webspice->settings()->domain_name; ?>: Welcome</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<style>
	.right_section ul li{color:red}
	</style>
	<?php include("global.php"); ?>
</head>

<body>
	<div id="wrapper">
		<div><?php include("header.php"); ?></div>
		
		<div id="page_login" class="main_container page_identifier">
			<div class="page_caption">
				Change Password
			</div>
			<div class="page_body">
				<!-- show validation error message -->
				<?php if( isset($errors) && $errors ): ?>
					<?php foreach ($errors as $k=>$v): ?>
						<div class="message_board"><?php echo $v; ?><br /></div>
					<?php endforeach; ?>
				<?php endif; ?>
				<!-- end error message -->
				
				<div class="left_section">
					<fieldset class="divider"><legend>Please enter your credential</legend></fieldset>
					
					<form id="frm_change_password" action="<?php echo $url_prefix; ?>change_password/<?php echo $this->uri->segment(2); ?>" method="post" data-parsley-validate>
						<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
						
						<table>
							<tr>
								<td>
									<div class="form_label">New Password*</div>
									<div><input type="password" class="input_full input_style" id="new_password" name="new_password" value="" required data-parsley-minlength="8" /></div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form_label">Repeat Password*</div>
									<div><input type="password" class="input_full input_style" id="repeat_password" name="repeat_password" value=""  data-parsley-equalto="#new_password" required /></div>
								</td>
							</tr>
							<tr>
								<td>
									<div><input type="submit" class="btn_gray" value="Change Password" /></div>
								</td>
							</tr>
						</table>
					</form>
				</div>
				
				<div class="right_section">
					<fieldset class="divider"><legend>Please follow the password policy</legend></fieldset>
					<ul>
						<li class="tick_small">Password must be minimum 8 characters</li>
						<li class="tick_small">Password must have at least one Capital Letter</li>
						<li class="tick_small">Password must have at least one Small Letter</li>
						<li class="tick_small">Password must have at least one Digit</li>
						<li class="tick_small">Password must have at least one Special Character</li>
						<li class="tick_small">You are not allowed to use your last 2 password</li>
					</ul>
				</div>
				<div class="float_clear_full">&nbsp;</div>
							
			</div><!--end .page_body-->

		</div>
		
		<div id="footer_container"><?php include("footer.php"); ?></div>
	</div>
</body>
</html>