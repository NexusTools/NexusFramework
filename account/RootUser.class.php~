<?php
class RootUser extends BasicBuiltinUser {

    private static $instance = false;
    
    public function instance(){
        return self::$instance === false ? (self::$instance = new RootUser()) : self::$instance;
    }

    protected function __construct(){
        $email = file_get_contents(INDEX_PATH . "framework.config.rootemail.txt");
        UserInterface::__construct(0, $email ? $email : "[unset]", "root", 6);
    }
    
    protected function setEmailImpl($email){
        return file_put_contents(INDEX_PATH . "framework.config.rootemail.txt", $email) !== false;
    }
    
    public function setPassword($password){
        return file_put_contents(INDEX_PATH . "framework.config.rootpass.bin", hash("sha512", $password, true)) !== false;
    }
    
    public function checkPassword($password){
        $passData = file_get_contents(INDEX_PATH . "framework.config.rootpass.bin");
        return $passData && $passData == hash("sha512", $password, true);
    }

}
?>
