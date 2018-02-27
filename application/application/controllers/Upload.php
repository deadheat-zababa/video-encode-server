<?php

class Upload extends CI_Controller {

	function __construct()
	{
		parent::__construct();
          	$this->load->helper(array('form', 'url'));
		$this->load->model('Upload_model');
	}

	function index()
	{
		$this->load->view('upload_form', array('error' => ' ' ));
	}
	
	function do_upload()
	{
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = '*';
		$config['max_size']	= '10000000';
		$config['max_width']  = '1920';
		$config['max_height']  = '1080';

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload())
		{
			$error = array('error' => $this->upload->display_errors());
			$this->load->view('upload_form', $error);
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());
			$this->load->view('upload_success', $data);
			$this->Upload_model->upload($data);
		}			
	}
}
?>
