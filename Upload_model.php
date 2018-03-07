<?php
class Upload_model extends CI_Model {

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->config('video_server');
  }

  private function input_db($file_name, $size,$directory_path,$directory_path_full,$tmp)
  {
    $this->db->insert('video_data', array(
	'name' => "$file_name",
	'size' => "$size",
	'directory_path' =>  "$directory_path_full",
	'time' => date("Y-m-d H:i:s", time())
    ));
    
    $this->db->insert('img_data',array(
	'name' => "$tmp"
        ));
  }

  public function upload($data)
  {
    $directory_path 	 = $data['upload_data']['file_path']; // /tmp/
    $directory_path_full = $data['upload_data']['full_path']; // /tmp/sample.jpg
    $file_name 		 = $data['upload_data']['raw_name']; // sample.jpg
    $path = '/usr/local/src/jsvm/bin/';
    shell_exec("ffmpeg -i ".$directory_path_full." -s 1280x720 ".$directory_path.$file_name.".mp4 &");
    shell_exec("ffmpeg -i ".$directory_path.$file_name.".mp4 ".$path."layer2.yuv &");
    
    //shell_exec("ffmpeg -i ".$directory_path_full." ".$directory_path.$file_name.".yuv &");
    shell_exec("ffmpeg -i ".$directory_path.$file_name.".mp4 -ss 1 -vframes 1 /usr/share/nginx/html/uploads/image/".$file_name.".jpg");
    shell_exec("rm -rf ".$directory_path_full.""); 
   
    $tmp = "$file_name";
    $yuv = "$file_name.yuv";
    $directory_path_full = "$directory_path$yuv";

    //shell_exec("".$path."./DownConvertStatic 1920 1080 ".$directory_path_full." 1280 720 ".$path."/layer2.yuv &");
    shell_exec("".$path."./DownConvertStatic 1280 720 ".$path."layer2.yuv 640 352 ".$path."layer1.yuv &");
    shell_exec("".$path."./DownConvertStatic 640 352 ".$path."layer1.yuv 320 240 ".$path."layer0.yuv &");
    shell_exec("".$path."./H264AVCEncoderLibTestStatic -pf ".$path."main.cfg &");
    
    shell_exec("rm -rf ".$directory_path.$file_name.".mp4 &");
    shell_exec("rm -rf ".$directory_path.$file_name.".yuv &");
    shell_exec("".$path."./BitStreamExtractorStatic ".$path."test.264 ".$path.$file_name."_sub.264 -l 0 &");
    shell_exec("MP4Box -add ".$path.$file_name."_sub.264:svcmode=MODE ".$directory_path.$file_name.".mp4 &");
    
    $tmp = "$file_name.jpg";
    $file_name = "$file_name.mp4";
    $directory_path_full = "$directory_path$file_name";
    $size = filesize($directory_path_full);
    
    $this->input_db($file_name,$size,$directory_path,$directory_path_full,$tmp);
   
    $this->load->library('ftp');
    //foreach($this->config->item('ip_white_list') as $ip)
    //{	
        $config = array(
	'hostname' => '192.168.11.88',
	'username' => 'pi',
	'password' => 'raspberry',
	);

	if($this->ftp->connect($config)){
	 $this->ftp->upload($directory_path_full,"Videos/$file_name");
	}

	else{
		echo 'failed';
	}
  //}
	$this->ftp->close();
 }
} 
?>
