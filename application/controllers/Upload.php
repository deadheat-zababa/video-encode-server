<?php

class Upload extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url'));
		$this->load->config('video_server');
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
 
			$directory_path 	 = $data['upload_data']['file_path'];
			$directory_path_full      = $data['upload_data']['full_path'];
			$file_name 		= $data['upload_data']['raw_name'];
				
			$this->load->view('upload_success', $data);	
			shell_exec("ffmpeg -i ".$directory_path_full." -vcodec libx264 ".$directory_path.$file_name.".mp4 &");
			shell_exec("rm -rf ".$directory_path_full."");
			shell_exec("ffmpeg -ss 1 -t 1 -r 1 -i ".$directory_path.$file_name.".mp4 ".$directory_path.$file_name.".jpg &");
			//
			//①アップロードされたファイルのパスとファイル名を取得する。
			$filePath = "$directory_path$file_name.mp4";
			$fileName = basename($filePath);

			//②アップロードされたファイルの内容を取得する。
			$file = file_get_contents($filePath);

			//③送信先URLを指定する。

			foreach($this->config->item('ip_white_list') as $ip)
			{
			$url = '".$ip."/receive.php';

			//④バウンダリを作成する。
			$boundary = '--------------------------'.microtime(true);

			//⑤ヘッダーを作成する。
			$headers = [
			    'Accept-language: ja',
			    'Cookie: hash=12345abcde',
			    'Content-Type: multipart/form-data; boundary='.$boundary
			];

			//⑥ボディにファイル名とファイルの内容を詰め込む。
			$content = '--'.$boundary."\r\n".
   			 'Content-Disposition: form-data; name="userfile"; filename="'. $fileName . '"' . "\r\n" .
  			  'Content-Type: text/plain'."\r\n\r\n".
   			 $file ."\r\n".
   			 '--'.$boundary.'--'."\r\n";

			//⑦送信先への送信データ(ヘッダ、ボディ)を作成する。
			$opts['http'] = [
			    'method' => 'POST',
			    'header' => implode("\r\n", $headers),
			    'content' => $content,
			];
			$context = stream_context_create($opts);

			//⑧送信先へデータを送信する。
			$contents = file_get_contents($url, false, $context);
			}
		}
	}
}
?>
