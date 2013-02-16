<?php

$__framework_error_occured = false;
$__framework_embedded_errorPage_tried = false;

class Error extends Exception {
	public function __construct($errno, $errstr, $errfile, $errline){
		$this->code = $errno;
		$this->message = $errstr;
		$this->file = $errfile;
		$this->line = $errline;
	}
}

function __convertExtensionToArray($exception){
    if(!($exception instanceof Exception))
        return $exception;

    return Array(
                "message" => $exception->getMessage(),
                "file" => $exception->getFile(),
                "line" => $exception->getLine(),
                "code" => $exception->getCode(),
                "trace" => $exception->getTrace(),
                "previous" => $exception->getPrevious()
            );
}

function framework_store_exception(&$exception, &$errorid, &$data){
    $data = Array("date" => time(),
                    "exception" => ($exception = __convertExtensionToArray($exception)));
    
    try{ 
		if(!is_dir(INDEX_PATH . "exceptions") && !mkdir(INDEX_PATH . "exceptions"))
			throw new Exception("Failed to Create Exceptions Directory");
		$errorid = md5(json_encode($exception));
		$errorFile = INDEX_PATH . "exceptions" . DIRSEP . $errorid . ".json";
		if(!file_put_contents($errorFile, json_encode($data)))
			throw new Exception("Failed to Write Recovery File...");
	    file_put_contents("$errorFile.new", 1);
				
		if(!file_exists($errorFile))
			throw new Exception("Failed to Write Recovery File...");
	}catch(Exception $e){
		if($authorized){
			echo "<!-- ERROR RECOVERY - WRITING FILE FAILED:\n";
			print_r($e);
			echo "\n\nERROR DATA:\n";
			print_r($exception);
			die("--><h2 style=\"margin: 0px; padding: 10px; color: red; background-color: white; position: absolute; left: 0px; top: 0px\">A Internal Error Occured but could not be Processed</h2>");
		}
	}
	return $convertedError;
}

function recovery_process_exception($exception, $alwaysRedirect=false){
	global $__framework_error_occured, $__framework_embedded_errorPage_tried;
	while(ob_get_level())
		ob_end_clean();
	if($__framework_error_occured)
		return;

	$__framework_error_occured = true;
	$authorized = !class_exists("User", false) || User::isStaff();
	
	$errorid = 0;
	$data = false;
	framework_store_exception($exception, $errorid, $data);

	if(defined("ABORT_ERROR"))
		return;
		
	if($__framework_embedded_errorPage_tried || $alwaysRedirect || headers_sent()) {
	    if($authorized)
	        die("<script>location.href=\"" . BASE_URL . "?recovery=$errorid\";</script><meta http-equiv=\"refresh\" content=\"1;url=" . BASE_URL . "?recovery=$errorid\">");
	    if(REQUEST_URI == "/errordoc/500")
	        die("<script>location.href=\"" . BASE_URL . "res" . RES_CONNECTOR . "internal-error\";</script><meta http-equiv=\"refresh\" content=\"1;url=" . BASE_URL . "res" . RES_CONNECTOR . "internal-error\">");
	    die("<script>location.href=\"" . BASE_URL . "errordoc/500\";</script><meta http-equiv=\"refresh\" content=\"1;url=" . BASE_URL . "errordoc/500\">");
    }
    $__framework_embedded_errorPage_tried = true;
    if(!$authorized) {
        if(REQUEST_URI == "/errordoc/500")
            Framework::serveResource("internal-error");
        $__framework_error_occured = false;
        Framework::runPage("/errordoc/500");
    }
	require FRAMEWORK_CORE_PATH . "recovery.inc.php";
	recovery_show_page($data);
	die();
}

if(isset($_GET['recovery']) &&
		is_dir(INDEX_PATH . "exceptions") &&
		file_exists(INDEX_PATH . "exceptions" . DIRSEP . $_GET['recovery'] . ".json")){
	
	require(FRAMEWORK_PATH . "recovery.inc.php");
	recovery_show_page(json_decode(file_get_contents(INDEX_PATH . "exceptions" . DIRSEP . $_GET['recovery'] . ".json"), true));
	die();
}

function __framework_usable_error($type){
	switch($type){
		case E_USER_NOTICE:
		case E_NOTICE:
			return false;
			
		case E_USER_WARNING:
		case E_USER_DEPRECATED:
		case E_DEPRECATED:
	    case E_WARNING:
	        return 2;
			
		default:
			return true;
	}
}

function __framework_error_recover($errno, $errstr, $errfile, $errline) {
	if($type = __framework_usable_error($errno)) {
	    if($type == 2) {
	        if(!defined("INAPI")) {
	            echo "<!-- DEBUG ERROR CAUGHT\n";
	            echo "$errfile:$errline\n";
	            echo "$errstr\n";
	            echo "-->";
	        }
	    } else
		    recovery_process_exception(new Error($errno, $errstr, $errfile, $errline));
	}
	
	return true;
}

function __framework_error_shutdown() { 
    $error = error_get_last();
    if($error && __framework_usable_error($error['type']))
    	recovery_process_exception($error);
} 

register_shutdown_function('__framework_error_shutdown'); 
set_exception_handler('recovery_process_exception');
set_error_handler("__framework_error_recover");
//error_reporting(0);

?>