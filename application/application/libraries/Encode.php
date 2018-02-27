<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Encode {

        public function mp4_enc()
        {
                shell_exec('ffmpeg -i "'. $data['file_name'] .'" -vcodec libx264 "'. $data['raw_name'] .'".mp4');
                $data['file_name'] = '"'.$data['raw_name'].'".mp4';
		return $data;
        }
	
	/*public function jpg2_enc($var)
	{
		$name = $var['file_name'];
                $raw = $var['raw_name'];
                exec("ffmpeg -i "'.$name.'" -vcodec openjpeg -vpre default "'.$raw.'".jp2");
                $name = "'.$raw.'.jp2";
	}

	public function jsvm($var)
        {
                $name = $var['file_name'];
                $raw = $var['raw_name'];
                exec("ffmpeg -i '.$name.' -vcodec openjpeg -vpre default '.$raw.'.jp2");
                $name = "'.$raw.'.264";
        }*/


}
?>
