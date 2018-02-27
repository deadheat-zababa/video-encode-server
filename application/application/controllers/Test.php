<?php
class Test extends CI_Controller {
	public function __construct() {
                parent::__construct();
                $this->load->helper(array('form', 'url'));
        }

        public function index() {
                $this->load->view('test', array('error' => ' ' ));
        }

        public function do_upload() {
                $config['upload_path']          = './uploads/';
                $config['allowed_types']        = '*';
                $config['max_size']             = '10000000';
                $config['max_width']            = '2048';
                $config['max_height']           = '2048';

                $this->load->library('upload', $config);

                if ( ! $this->upload->do_upload('userfile')) {
                        $error = array('error' => $this->upload->display_errors());
                        $this->load->view('test', $error);
                }

                else {
                        $data = array('upload_data' => $this->upload->data());
			$this->load->view('test_success', $data);
		        //$this->load->model('test');
                        //$this->model->upload($data);
			$this->controller->do_encode($data);
                }
        }

        public function do_encode() {
                $var = $data;
    		$name = $var['file_name'];
		$raw = $var['raw_name'];
                exec("ffmpeg -i '.$name.' -vcodec libx264 -vpre default '.$raw.'.mp4");
		$name = "'.$raw.'.mp4";
		//$this->load->model('test');
  		//$this->model->upload($var);
        }
}
?>
