<?php 
	$realfilename = str_replace('\\', '', str_replace('/', '', trim($_GET['s'])));

	if(file_exists($realfilename))
	{
		$script = file_get_contents($realfilename);
		$mtime = filemtime($realfilename); 
		$offset = 5 * (60 * 60);  // gzip version expires in 5 hours
	
		ob_start ("ob_gzhandler");
		header("Accept-Ranges: bytes");
		header("Content-type: application/x-javascript");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $mtime) . " GMT");
		header("Cache-Control: max-age=315360000"); 
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");
	
		print($script);
	}
	else
	{
		header('Pragma: no-cache');
		header('Cache-Control: no-store'); 
		header("Content-type: application/x-javascript");
		header("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
	
		print("alert('bad package-file (".$realfilename."). contact the admin!');");
	}

?>