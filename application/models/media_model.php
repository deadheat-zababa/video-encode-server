<?php

/**
 * Class Media_model
 * @property CI_DB $db
 * @property CI_Image_lib $image_lib
 */
class Media_model extends CI_Model {
  /**
   * コンストラクタ
   * 必要なクラスのロード
   * @return void
   */

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

  /**
   * DBにデータをinsertする
   * 上書きが発生したかを判断し、発生したらTRUE、発生しなかったらFALSEを返す
   * @param string $new_name
   * @param string $original_name
   * @return bool
   */
  private function _input_db($new_name, $original_name){
    //data_mediaというテーブルにデータを突っ込みたいとする
    //さらに、同名のデータが既にあったら何もせずTRUEを返したいとする

    //テーブル名
    $table = 'data_media';

    //既存の被りデータ探し
    $this->db->where('new_name', $new_name);
    $query = $this->db->get($table);

    //被りがあるかどうかの判定
    $data = $query->result();
    if(isset($data[0])){
      //被りがあったので上書きフラグTRUE
      return TRUE;
    }

    //被りが無かったのでDBにinsert
    $this->db->insert($table, array(
      'original_name' => $original_name,
      'new_name' => $new_name
    ));
    //上書きフラグはFALSE
    return FALSE;
  }

  /**
   * リネーム処理
   * @param array $data
   * @return string
   */
  private function _rename($data){
    //例えば、先頭の0を消す処理
    try{
      $new_name = ltrim($data['file_name'], '0');
      if(rename($data['full_path'], $data['file_path'].$new_name)){
        return $new_name;
      }
      throw new Exception('ファイルのリネームに失敗しました');
    }

    catch (Exception $exception){
      die($exception->getMessage());
    }
  }

  /**
   * サムネイルを生成する
   * @param string $file_path
   * @param string $new_name
   * @return void
   */
  private function _create_thumb($file_path, $new_name){
    //パスを変数に格納
    $path = $file_path.$new_name;

    //画像ファイルでなかったら処理を中断
    $extension = strtolower(pathinfo(realpath($path), PATHINFO_EXTENSION));
    if (
      $extension !== 'jpeg' &&
      $extension !== 'jpg' &&
      $extension !== 'gif' &&
      $extension !== 'png'
    )
    {
      return;
    }

    //作成するサムネイルの種類を設定
    $thumbConfigs = array(
      array(
        'suffix' => '_big',
        'width' => 1000
      ),
      array(
        'suffix' => '_middle',
        'width' => 400
      ),
      array(
        'suffix' => '_small',
        'width' => 130
      )
    );

    foreach ($thumbConfigs as $thumbConfig){
      //イメージライブラリの設定
      $config = array(
        'source_image' => $path,
        'create_thumb' => TRUE,
        'thumb_marker' => $thumbConfig['suffix'],
        'width' => $thumbConfig['width']
      );
      $this->image_lib->initialize($config);

      //サムネイル生成
      try{
        if( ! $this->image_lib->resize()){
          throw new Exception($this->image_lib->display_errors());
        }
      }
      catch (Exception $exception){
        die($exception->getMessage());
      }
    }
  }

  /**
   * 外部ファイルサーバー指定があった場合は現地のコントローラを叩く
   * @param array $data
   * @return string
   */
  public function upload($data){
    //以下三行はブラウザに接続を切らせないための処理
    //長時間（30秒とか）のアップロード時のみ必要なので、極端な場合を除きいらないかも
    echo '';
    @ob_flush();
    flush();

    //元のファイル名
    $original_name = $data['client_name'];

    //リネーム処理が必要ならこのタイミングで
    $new_name = $this->_rename($data);

    //既に存在しているファイル名ならば上書きフラグをonに、そうでなければDBに登録
    $override = $this->_input_db($new_name, $original_name);

    //外部画像サーバーが設定されているかどうか
    if($this->config->item('img_server'))
    {
      //外部画像サーバーが設定されていたらそこのMediaコントローラーに狙いを定める
      $url = $this->config->item('img_server').'/media/external_upload';

      //ファイルをhttpで送れるよう加工
      $file = file_get_contents($data['file_path'].$new_name);

      //ファイルタイプを取得
      $finfo    = finfo_open(FILEINFO_MIME_TYPE);
      $type = finfo_file($finfo, $data['file_path'].$new_name);
      finfo_close($finfo);

      //改行文字の設定
      $rn = "\r\n";

      //画像サーバーに投げるヘッダーを作成
      $boundary = '--------------------------'.microtime(TRUE);
      $headers = 'Accept-language: ja'.$rn.
        'Content-Type: multipart/form-data; boundary='.$boundary.$rn;

      //画像サーバーに投げるcontent部分の作成
      $content = '--'.$boundary.$rn.
        'Content-Disposition: form-data; name="asset"; filename="'.$new_name. '"' .$rn.
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

      //お待たせしました、投げます
      file_get_contents($url, FALSE, $context);

      //このサーバーの画像ファイル削除
      unlink($data['file_path'].$new_name);
    }
    else
    {
      //外部サーバーが設定されていなかったのでサムネイル作成
      $this->_create_thumb($data['file_path'], $new_name);
    }

    //上書き情報をControllerに返す
    return $override;
  }

  /**
   * 外部からのファイルアップロードに対応する
   * リネームとサムネイル作成
   * @param array $data
   * @return void
   */
  public function external_upload($data)
  {
    //リネーム処理が必要ならこのタイミングで
    $new_name = $this->_rename($data);

    //サムネイル作成
    $this->_create_thumb($data['file_path'], $new_name);
  }

}
