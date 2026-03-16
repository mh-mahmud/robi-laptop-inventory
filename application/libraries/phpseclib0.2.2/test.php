<?php
   include('Net/SFTP.php');

	 $sftp = new Net_SFTP('75.126.233.162');
	 if (!$sftp->login('clickbd', 'Ver$eBD321clk')) {
       exit('Login Failed');
   }

   echo $sftp->pwd() . "\r\n";
   $sftp->put('filename.ext', 'hello, world!');
   print_r($sftp->nlist());
?>