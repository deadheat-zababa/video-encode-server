<?php
 
class Test extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
    }

    public function index() {
        $this->load->view('upload_form', array('error' => ' ' ));
    }

 public funtion image() {
       foreach(glob('dir/*') as $file){
    if(is_file($file)){
        echo htmlspecialchars($file);
        ob_clean();  // ← 最初にやる
        $image_file = "$file";
        $this->output->set_content_type(get_mime_by_extension($image_file));
        $this->output->set_output(file_get_contents($image_file));

}}
}
?>
