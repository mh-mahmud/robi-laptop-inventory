<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $this->webspice->settings()->domain_name; ?>: Message</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	
	<?php include("global.php"); ?>
</head>

<body>
	<div id="wrapper">
		<div id="header_container"><?php include("header.php"); ?></div>
		
		<div id="page_message" class="main_container page_identifier">
			<div class="page_caption">Message Board</div>
			<div class="page_body">
				<h3 class="fsecond"><?php if( isset($title) ){ echo $title; } ?></h3>
				<p><?php if( isset($body) ){ echo $body; } ?></p>
				
				<div><?php if( isset($footer) ){ echo '<br />'.$footer; } ?></div>
			</div><!--end .page_body-->

		</div>
		
		<div id="footer_container"><?php include("footer.php"); ?></div>
	</div>
</body>
</html>