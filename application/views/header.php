<style>
/* Ensure that the demo table scrolls */
	th, td { white-space: nowrap; }
	div#example_wrapper {
		overflow: auto;
		margin: 0 auto;
	}
</style>
<div id="header_container">
	<div class="header_bg">
		<div class="my_container">
			<div class="left_part">
				<div class="caption fsecond"><img src="<?php echo $url_prefix; ?>global/img/Robi_Axiata_logo.png" width="40" /><font color="#ED1B24"><?php echo $this->webspice->settings()->site_title; ?></font></div>
			</div>
			
			<div class="right_part">
				<ul>
					<?php if( $this->webspice->get_user_id() ): ?>
					<li class="mnu"><a href="<?php echo $url_prefix; ?>logout">&nbsp;Logout</a></li>
					<!-- <li class="mnu"><a href="<?php echo $url_prefix; ?>user_guide" target="_blank">User Guide | </a></li> -->
					<?php else: ?>
					<li class="mnu"><a href="<?php echo $url_prefix; ?>login">Login</a></li>
					<?php endif; ?>
					<li class="float_clear_full">&nbsp;</li>
				</ul>
				
			</div>
			<div class="float_clear_full">&nbsp;</div>
		</div>
	</div><!--end .header_bg-->
	
	<div class="my_container" style="position:relative; z-index:9000;">
		<div class="menu_panel">
			<div id="menu-collapse-button" class="text-center">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu_bar_collapse" aria-expanded="false" aria-controls="navbar" style="float:none; margin:auto;">
		      <span class="sr-only">Toggle navigation</span>
		      <span class="icon-bar"></span>
		      <span class="icon-bar"></span>
		      <span class="icon-bar"></span>
		    </button>
		  </div>
		  
			<div id="menu_header" class="row" style="margin:0px;">
				<div id="menu_bar_collapse" class="navbar-collapse collapse" style="padding-left:0px; padding-right:0px;">
					<ul class="col-md-12 nav nav-pills custom_box_shadow br_7_full navbar-nav" style="padding-left:15px; padding-right:15px;">
						<li><a href="<?php echo $url_prefix; ?>"><span class="glyphicon glyphicon-home"></span>Home</a></li>
						<?php
						# get distinct group name
						$get_permission_group = $this->db->query("
						SELECT GROUP_NAME 
						FROM TBL_PERMISSION
						WHERE STATUS=7
						AND GROUP_NAME != 'dashboard' 
						GROUP BY GROUP_NAME 
						ORDER BY GROUP_NAME
						")->result();

						foreach($get_permission_group as $gk=>$gv){
							$get_permission = $this->db->query("
							SELECT * 
							FROM TBL_PERMISSION 
							WHERE STATUS=7 
							AND GROUP_NAME='".$gv->GROUP_NAME."' 
							ORDER BY MENU_NAME
							")->result();
				
							# find out that; at least one permission has or not according to the group name
							$is_permitted = false;
							foreach($get_permission as $pk=>$pv){
								if( $this->webspice->permission_verify($pv->PERMISSION_NAME, true) ){
									$is_permitted = true; break;
								}
							}
		
							# create main menu
							# x for history report
							$test= array('1','2','3','4','5','6','7','8','9','_','x');
							if( $is_permitted ){
								echo '<li class="dropdown">';
									echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.ucwords(str_replace($test," ",$gv->GROUP_NAME)).'<span class="caret"></span></a>';
									echo '<ul class="dropdown-menu">';
									
								# generate sub menu
								$menu_name = null;
								foreach($get_permission as $pk1=>$pv1){
									if( $this->webspice->permission_verify($pv1->PERMISSION_NAME, true) && $pv1->MENU_NAME != $menu_name ){
										$menu_name = $pv1->MENU_NAME;
										
										echo '<li><a href="'.$url_prefix.$pv1->ROUTE_NAME.'">'.ucwords(str_replace($test,' ',$pv1->MENU_NAME)).'</a></li>';
										//echo '<li class="divider"></li>';
									}
								}
								
								# end main menu and sub menu
								echo '</ul></li>';
							}
		
						}
						?>
											
						<?php if( $this->webspice->get_user_id() ): ?>
						<li class="dropdown pull-right">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#">My Account<span class="caret"></span></a>
							<ul class="dropdown-menu">
							  <li><a href="<?php echo $url_prefix; ?>change_password/<?php echo $this->webspice->encrypt_decrypt( $this->webspice->get_user('USER_EMAIL').'|'.date("Y-m-d"),'encrypt' ); ?>">Change Password</a></li>
							</ul>
						</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
		
		<?php if( $this->webspice->message_board(null, 'get') ): ?>
			<div id="message_board">
				<?php echo $this->webspice->message_board(null,'get_and_destroy'); ?>
			</div>
		<?php endif; ?>
</div>


<script type="text/javascript">
/*
	$(document).ready(function(){
		var count = 0;
		setInterval(function(){ 
			count++; 
			if(count > 600){
				count = 0;
				location.href = '/signout';
			}
			
		}, 10000);
		
		$(window).mousemove(function(){
  		count = 0;
		});
	});
*/
</script>


<?php $get_route = $this->uri->segment(1); ?>
<script type="text/javascript">
/*
	$(document).ready(function(){
		switch('<?php echo $get_route; ?>'){
			case '':
				identify_current_place_menu('<span class="glyphicon glyphicon-home"></span>Home'); break;
			case 'create_option':
			case 'manage_option':
			case 'create_bank':
			case 'manage_bank':
			case 'create_wallet':
			case 'manage_wallet':
			case 'create_sms_format':
			case 'manage_sms_format':
			case 'create_signatory':
			case 'manage_signatory':
			case 'create_signatory_group':
			case 'manage_signatory_group':
			case 'create_opening_balance':
			case 'manage_opening_balance':
				identify_current_place_menu('Master Data'); break;
			case 'wallet_balance_summery':
			case 'wallet_balance_details':
			case 'instruction_letter':
			case 'received_sms':
				identify_current_place_menu('Report'); break;
			case 'create_transaction':
			case 'manage_transaction': 
				identify_current_place_menu('Transaction'); break;
			case 'create_user':
			case 'manage_user':
			case 'create_role':
			case 'manage_role': 
				identify_current_place_menu('User Management'); break;
		}
		
		function identify_current_place_menu(param_html){
			$('#menu_header .nav li a').each(function(){
				if( $(this).html()==param_html ){
					$(this).css("background-color", "#EEEEEE");
				}
			});
		}
	});
*/
</script>

<script type="text/javascript">
	$(document).ready(function(){
		var count = 0;
		setInterval(function(){ 
			count++; 
			if(count > 90){
				count = 0;
				location.href = '/robi.lims/logout';
			}
			/*count 6 times at each minute*/
			
		}, 10000);
		
		$(window).mousemove(function(){
  		count = 0;
		});
	
		var user_pock = setInterval(function(){ 
			var ajax_call = $.ajax({
			 type: "GET",
			 url: url_prefix + "user_login_time_update",
			 async: false
			}).done(function(msg){
			 //do nothing
			 
			}).fail(function() {
			 //do nothing
			});
		}, 300000);
		
	});
</script>