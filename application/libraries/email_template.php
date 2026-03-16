<?php
class Email_template{
	
	function Email_template(){

	}
	
	function send_verification_email($user_name=null, $user_email){
		$CI = &get_instance();
		$site_url = $CI->webspice->settings()->site_url;
		$domain_name = $CI->webspice->settings()->domain_name;
		$verification_code = $CI->webspice->encrypt_decrypt($user_email.'|'.date("Y-m-d"), 'encrypt');

		$html =<<< HTML_MESSAGE
			<table width="100%" style="background:#eeeeee; font:normal 12px tahoma; padding:15px;">
				<tr>
					<td>
						Dear {$user_name}. Welcome to {$domain_name}.
					</td>
				</tr>
				<tr>
					<td>
						<h2>Verify your account!</h2>
						Click on the verification link below. This link will successfully verify your email address and allow you to sign in to your {$domain_name} Network. If clicking the link does not work, please copy and paste the link from the email into your browser&#39;s navigation bar instead.
						<br /><br />Please note that; the link might be valid for 3 days.
					</td>
				</tr>
				<tr>
					<td>
						<h3><a href="{$site_url}verify/{$verification_code}">{$site_url}verify/{$verification_code}</a></h3>
					</td>
				</tr>
				<tr>
					<td>
						Please feel free to contact us at {$CI->webspice->get_customer_service_contact()} for any questions you may have. Thank you.
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td>
						{$domain_name}
					</td>
				</tr>
			</table>
HTML_MESSAGE;
			
		$message = $this->get_email_template($html);
#echo $message;exit;
		if( ! $CI->webspice->email($user_email, 'Verify Your Account', $message, NULL, 'html') ){
			if( ! $CI->webspice->email($user_email, 'Verify Your Account', $message, NULL, 'html') ){
				$CI->webspice->email($user_email, 'Verify Your Account', $message, NULL, 'html');
			}
		}
	}
	
	function send_new_user_password_change_email($user_name=null, $user_email){
		$CI = &get_instance();
		$site_url = $CI->webspice->settings()->site_url;
		$domain_name = $CI->webspice->settings()->domain_name;
		$verification_code = $CI->webspice->encrypt_decrypt($user_email.'|'.date("Y-m-d"), 'encrypt');

		$html =<<< HTML_MESSAGE
			<table width="100%" style="background:#eeeeee; font:normal 12px tahoma; padding:15px;">
				<tr>
					<td>
						Dear {$user_name}. Welcome to {$domain_name}.
					</td>
				</tr>
				<tr>
					<td>
						<h2>Change Your Default Password!</h2>
						We have created an account for you. Please click on the link below. This link will help you to change your default password. If clicking the link does not work, please copy and paste the link from the email into your browser&#39;s navigation bar instead.
						<br /><br />Please note that; the link might be valid for 3 days.
					</td>
				</tr>
				<tr>
					<td>
						<h3><a href="{$site_url}change_password/{$verification_code}">Change Password</a></h3>
					</td>
				</tr>
				<tr>
					<td>
						Please feel free to contact us at {$CI->webspice->get_customer_service_contact()} for any questions you may have. Thank you.
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td>
						{$domain_name}
					</td>
				</tr>
			</table>
HTML_MESSAGE;
			
		$message = $this->get_email_template($html);
#echo $message;exit;
		if( ! $CI->webspice->email($user_email, 'Change your password!', $message, NULL, 'html') ){
			if( ! $CI->webspice->email($user_email, 'Change your password!', $message, NULL, 'html') ){
				$CI->webspice->email($user_email, 'Change your password!', $message, NULL, 'html');
			}
		}
	}
	
	function send_retrieve_password_email1($UserID, $UserName, $UserEmail){
		$CI =&get_instance();
		$site_title = $CI->webspice->settings()->site_title;
		$contact = $CI->webspice->get_customer_service_contact();
		$site_url = $CI->webspice->settings()->site_url;
		$link = $site_url.'change_password/'.$CI->webspice->encrypt_decrypt($UserID.'|'.date("Y-m-d"), 'encrypt');

		$html =<<< HTML_MESSAGE
			<table style="background:#eeeeee; font:normal 12px tahoma; padding:15px;">
				<tr>
					<td>Dear {$UserName}</td>
				</tr>
				<tr>
					<td>
						Hello and Welcome to the {$site_title}!
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td>
						Your request has been accepted! Please click on the link to reset your password. <br /><br />Please note that; the link will <strong>valid only for following 3 days</strong>. So, please use the link before it will being useless.
						<br /><br />
						<h3>Password Reset Link</h3>
						{$link}
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td>
						Please feel free to contact us at {$contact} for any queries you have. Thank you to being with us. Wish you best luck.
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td>
						Best Regards<br />
						{$site_title} Authority
					</td>
				</tr>
			</table>
HTML_MESSAGE;

		$message = $this->get_email_template($html);
#echo $UserEmail; exit;
		if( !$CI->webspice->email($UserEmail, 'Password retrieval request', $message, NULL, 'html') ){
			if( !$CI->webspice->email($UserEmail, 'Password retrieval request', $message, NULL, 'html') ){
				$CI->webspice->email($UserEmail, 'Password retrieval request', $message, NULL, 'html');
			}
		}
	}
	
	function send_email_newsletter($subscriber, $html, $email_subject, $preview=null){
		$CI = &get_instance();
		$site_url = $CI->webspice->settings()->site_url;
		$side_deal_html = null;
		
		$unsubscribe_code = $CI->webspice->get_verification_key( null, $subscriber,'email' );

		$html .= <<< HTML_TEMPLATE
		<br /><br />
		<div style="color:#9A9A9A;font-size:10px;margin-left:18px;line-height:20px;text-align:center;">
			Copyright &copy; Dlux Group 2012. All rights reserved.
			<br>
			Unsubscribe from email offers: <a href="{$site_url}subscribe/{$unsubscribe_code}">unsubscribe</a>
		</div>
HTML_TEMPLATE;

		$message = $this->get_email_template($html);
		if($preview){
			echo $message;exit;
		}
		$CI->webspice->massmail($subscriber,$email_subject,$message,$site_name.': Luxury Brands, Fabulous Prices!','support@bazardhaka.com');
	}
	
	function send_unsubscribe_email($user_name=null, $user_email){
		$CI = &get_instance();
		$site_url = $CI->webspice->settings()->site_url;

		$html =<<< HTML_MESSAGE
			<table style="background:#eeeeee; font:normal 12px tahoma; padding:15px;">
				<tr>
					<td>
						Deal PresidePhoto user, {$user_name}!
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td>
						Unsubscribed successfully. But we hope that you will back soon to PresidePhoto.
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td>
						Please feel free to contact us at {$CI->webspice->get_customer_service_contact()} for any questions you may have. Thank you.
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td>
						The PresidePhoto team
					</td>
				</tr>
			</table>
HTML_MESSAGE;
			
		$message = $this->get_email_template($html);
//echo $message;exit;
		if( ! $CI->webspice->email($user_email, $site_name.': we hope that you will back', $message, NULL, 'html') ){
			if( ! $CI->webspice->email($user_email, $site_name.': we hope that you will back', $message, NULL, 'html') ){
				$CI->webspice->email($user_email, $site_name.': we hope that you will back', $message, NULL, 'html');
			}
		}
	}
	
	
	function get_email_template( $email_content=NULL ){
		$CI =& get_instance();
		$site_url = $CI->webspice->settings()->site_url;
		$html_email_teamplate =<<< HTMLEMAILTEMPLATE
		
		<table width="700" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td>
					<table width="100%" style="font-family:tahoma; font-size:12px;">
						<tr>
							<td><img src="{$site_url}global/img/logo_white_bg.png" alt="" /></td>
							<td colspan="3" align="right">
								<table align="right" style="font-family:tahoma; font-size:12px;">
									<tr>
										<td><a style="text-decoration:none;" href="{$site_url}contact_us">Contact Us</a></td>
										<td><img src="{$site_url}global/img/icon_customer_care.png" alt="" /></td>
										<td width="5">&nbsp;</td>
										<td><a style="text-decoration:none;" href="https://twitter.com/DAM">Follow Us</a></td>
										<td><img src="{$site_url}global/img/icon_twitter.png" alt="" /></td>
										<td width="5">&nbsp;</td>
										<td><a style="text-decoration:none;" href="https://www.facebook.com/DAM">Like Us</a></td>
										<td><img src="{$site_url}global/img/icon_facebook.png" alt="" /></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					$email_content
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td align="center" style="font-family:tahoma; font-size:11px;">
				For more information please read our <a href="{$site_url}static/privacy_policy">Privacy Policy</a> and <a href="{$site_url}static/terms_and_conditions">Terms &amp; Conditions</a>.
				<br />
				{$CI->webspice->get_customer_service_contact('full')}
				</td>
			</tr>
		</table>

HTMLEMAILTEMPLATE;

	return $html_email_teamplate;
	
	}
	
}