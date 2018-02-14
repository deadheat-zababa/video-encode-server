<?php
/**
 * Class Media
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_Upload $upload
 * @property Media_model $media_model
 */
class Media extends CI_Controller {

  /**
   * 必要なモノをロード
   * @return void
   */
  public function __construct(){
    parent::__construct();
    
    $this->load->view('media');
    //モデルのロード
    $this->load->model('media_model');

    //ヘルパーをロード
    $this->load->helper(array('url','form'));

    //映像サーバー設定読込
    $this->config->load('video_server');

    //アップロードライブラリの読み込み＆設定
    $config = array(
      // ファイルのアップロード制限
      "allowed_types"=>"mp4",

      //ファイル名が被ったときは上書きする
      'overwrite' => TRUE,

       //ファイル名変更
       'file_name' => test.mp4,

      // ファイルのアップロード先を決める
      "upload_path"=>APPPATH.'../uploads'
    );
    $this->load->library('upload', $config);
  }

  /**
   * 設定されているホワイトリストIP以外のアクセスを弾くメソッド
   * @return void
   */
  private function _ip_filter(){
    foreach ($this->config->item('ip_white_list') as $ip){
      if(isset($_SERVER['REMOTE_ADDR']) && $ip === $_SERVER['REMOTE_ADDR']) {
        return;
      }
    }
    die('IP Adress Error'); 
    //die:メッセージを出力し、現在のスクリプトを終了する
  }

  /**
   * ファイルをCI_Uploadクラスでアップロードする
  *クライアントからアップロードされるファイルを受け取る
   * $upload->dataの返り値を返す
   * @return array
   */
  private function _up(){
    try{
      if (!isset($_FILES['asset']['error']) || !is_int($_FILES['asset']['error'])) {
        throw new Exception('パラメータが不正です');
      }

      if($this->upload->do_upload('asset')){
        return $this->upload->data();
      }

      //ファイルのアップロードに失敗した場合
      throw new Exception($this->upload->display_errors('<p>', '</p>'));
    }
    catch (Exception $e){
      die($e->getMessage());
    }
  }

  /**
   * フォームから値を受け取る
   * _up()を呼び出したらモデルへ
   * @return void
   */
  public function upload(){
    //モデルへ情報を投げる
    print $this->media_model->upload($this->_up());
  }

  /**
   * 外部からデータを受け取る
   * 指定されたIPアドレス以外は弾く
   */
  public function external_upload(){
    //外部からのアクセス専用なので、許可なきIPは滅
    $this->_ip_filter();

    //モデルへ情報を投げる
    print $this->media_model->external_upload($this->_up());
  }

  /**
   * 画面の表示
   * @return void
   */
  public function index(){
    //画像パスの設定を読み込む
    $path = base_url().'media';
    if($this->config->item('video_server')){
      $path = $this->config->item('video_server').'/media';
    }

    //CSRF TOKENの名前
    $token_name = $this->security->get_csrf_token_name();

    $this->load->view('media', array(
      'path' => $path,
      'token_name' => $token_name
    ));
  }

}
