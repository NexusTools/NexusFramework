<?php
class RequestProxy {

	public function __construct($domain, $port=80, $hostname=false){
		header("Content-Type: text/plain");
		try{
			ob_end_clean();
		}catch(Exception $e){}
	
		if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
			header("http/1.1 502 Bad Gateway");
			header("Content-Type: text/plain");
			die("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
		}
		
		if(socket_connect($sock, $domain, $port) === false){
			header("http/1.1 502 Bad Gateway");
			header("Content-Type: text/plain");
			die("socket_connect() failed: reason: " . socket_strerror(socket_last_error()));
		}
		
		if(count($_GET)){
			$GET = "?";
			$first = true;
			foreach($_GET as $key => $val){
				if($first)
					$first = false;
				else
					$GET .= "&";
				$GET .= urlencode($key);
				if(strlen($val))
					$GET . "=" . urlencode($val);
			}
		} else
			$GET = "";
		
		$data = $_SERVER['REQUEST_METHOD'] . " " . REQUEST_URI . $GET . " HTTP/1.1\r\n";
		$heads = getallheaders();
		if($hostname)
			$heads['Host'] = $hostname;
		else
			$heads['Host'] = $domain;
		foreach($heads as $key => $val){
			$data .= "$key: $val\r\n";
		}
		$data .= "\r\n";
		socket_write($sock, $data);
		if($postin = fopen("php://input", "r"))
			while($data = fread($postin, 1024))
				socket_write($sock, $data);
		
		$next = false;
		while(($line = socket_read($sock, 1024, PHP_NORMAL_READ)) !== false) {
			$line = substr($line, 0, strlen($line)-1);
			if(!strlen($line)) {
				if($next)
					break;
				else
					$next = true;
				continue;
			} else
				$next = false;
			
			//header($line);
			echo $line;
		}
		
		while(($data = socket_read($sock, 1024)))
			echo $data;
		exit;
	}

}
?>
