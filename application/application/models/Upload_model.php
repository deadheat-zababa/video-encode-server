<?php
class Upload_model extends CI_Model {

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->config('video_server');
  }

  private function input_db($file_name, $size, $directory_path)
  {
    $this->db->insert('video_data', array(
	'name' => "$file_name",
	'size' => "$size",
	'directory_path' =>  "$directory_path",
	'time' => date("Y-m-d H:i:s", time())
    ));

    shell_exec("ffmpeg -ss 1 -t 1 -r 1 -i ".$directory_path.$file_name.".mp4 ".$directory_path.$file_name.".jpg &");
    
    $this->db->insert('img_data',array(
	'name' => "$directory_path$file_name.jpg"
    ));
  }

  public function upload($data)
  {
    $directory_path 	 = $data['upload_data']['file_path']; // /tmp/
    $directory_path_full = $data['upload_data']['full_path']; // /tmp/sample.jpg
    $file_name 		 = $data['upload_data']['raw_name']; // sample.jpg
    

    shell_exec("ffmpeg -i ".$directory_path_full." -vcodec libx264 ".$directory_path.$file_name.".mp4 &");
    shell_exec("rm -rf ".$directory_path_full."");
    $file_name = "".$file_name.".mp4";
    $directory_path_full = "$directory_path$file_name";

    $size = filesize($directory_path_full);
    
    $this->input_db($file_name,$size,$directory_path);

    //①アップロードされたファイルのパスとファイル名を取得する。
    $filePath = pack($directory_path_full);
    $file = file_get_contents($filePath);

  //  foreach($this->config->item('ip_white_list') as $ip)
    //{	
	$url = "http://192.168.11.76:5000";
        //$url = "http://192.168.11.59/receive";

      //ファイルタイプを取得
       $finfo    = finfo_open(FILEINFO_MIME_TYPE);
       $type = finfo_file($finfo, $filePath);
       finfo_close($finfo);

      //改行文字の設定
       $rn = "\r\n";

      //画像サーバーに投げるヘッダーを作成
       $boundary = '--------------------------'.microtime(TRUE);
       $headers = 'Accept-language: en'.$rn.
        'Content-Type: multipart/form-data; boundary='.$boundary.$rn;

       //画像サーバーに投げるcontent部分の作成
       $content = '--'.$boundary.$rn.
         'Content-Disposition: form-data; name="uerfile"; filename="".$filePath. ""' .$rn.
         'Content-Type: '.$type.$rn.$rn.
         $file .$rn.
         '--'.$boundary.'--'.$rn;

      //投げる内容をまとめる
       $opts['http'] = [
        'method' => 'POST',
        'header' => $headers,
        'content' => $content
       ];

       $context = stream_context_create($opts);
       file_get_contents($url, FALSE, $context);
  //}
 }
} 
?>
