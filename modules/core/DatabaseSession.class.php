<?
class EncryptedSessionHandler extends SessionHandlerInterface
{

    private $database = false;

    public close() {
        $this->database = false;
    }
    
    public destroy($session_id) {
        
    }
    
    public gc($maxlifetime) {
        
    }
    
    public open($save_path, $name) {
        
    }
    
    public read($session_id) {
        
    }
    
    public write($session_id, $session_data) {
        
    }
}
?>