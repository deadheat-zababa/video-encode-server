<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>アップロードDEMO</title>
</head>
<body data-url="<?php echo $path; ?>">
  <div class="wrap">
    <div class="dropArea">
      <?php echo form_open_multipart('/media/upload/'); ?>
      <h4 class="managementFormBoxTitle">ファイルをアップロードする</h4>
      <div class="fileDropArea">
        <p class="fileDropComment">ファイルをドロップ<br/>
          または<br/>
          <label class="fileChooseBtn">
            ファイルを選択
            <?php echo form_upload(array(
              'name' => 'asset',
              'class' => 'file',
              'value' => 'ファイルを選択'
            )); ?>
          </label>
          <span id="numbers" style="display: none;"><span id="numberNow"></span>/<span id="numberCount"></span>　件処理中...</span>
        </p>
      </div>
      <p id="alerts"></p>
      <?php echo form_close(); ?>
    </div>
    <div class="managementTableSpace picArea">
      <ul class="mediaDisplayTypeTileListBox picBox" style="position: relative;"></ul>
    </div>
  </div>
  <script>
    (function(){

      //トークン取得
      var token = document.querySelector('[name = <?php echo $token_name; ?>]').value;

      //パス取得
      var path = window.location.href;

      //進行状況部分
      var numbers = document.getElementById('numbers');

      //処理中のファイル番号部分
      var numberNow = document.getElementById('numberNow');

      //処理中のファイル全体数部分
      var numberCount = document.getElementById('numberCount');

      //警告部分
      var alerts = document.getElementById('alerts');

      //画像投稿
      function upload(targets){
        numbers.style.display = 'block';

        //投稿された画像らしきファイルの数
        var length = targets.length;
        numberCount.innerHTML = length;

        //カウント変数
        var i = 0;

        //サーバーエラー確認
        var handleErrors = function(response) {
          if (!response.ok) {
            throw Error(response.statusText);
          }
          return response;
        }

        //まだアップロードファイルがあるか確認する
        var loop = function (response) {
          console.log(response);
          i++;
          if(i < length){
            upload();
          }
          else{
            numbers.style.display = 'none';
          }
        }

        //サーバーエラー発生時処理
        var reject = function() {
          alerts.innerHTML = alerts.innerHTML + '<span>'+(i + 1)+'番目：'+targets[i].name + 'のアップロードに失敗しました</span><br />';
        }

        //アップロード処理
        var up = function(){
          if(targets[i].type.indexOf('image/') === 0){

            //進捗表示
            numberNow.innerHTML = i;

            //ファイル
            var fd = new FormData();
            fd.append('asset', targets[i]);
            fd.append('<?php echo $token_name; ?>', token);

            //アップする
            fetch(path + '/upload', {
              method: 'POST',
              body: fd,
              mode: 'cors',
              credentials: 'include'
            })
              .then(handleErrors)
              .then(loop)
              .catch(reject);
            return;
          }

          //画像でないファイルをアップロード
          alerts.innerHTML = alerts.innerHTML + '<span>'+(i + 1)+'番目：'+targets[i].name + 'は画像ではありません</span><br />';
        }

        //アップロード開始
        up();
      }

      //画像投稿
      var fileBtn = document.getElementsByClassName('file')[0];
      fileBtn.addEventListener('change', function (e) {
        upload(e.target.files);
      });

      //画像ドロップ
      var dropArea = document.getElementsByClassName('dropArea')[0];
      //ドラッグ途中
      dropArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
      });
      //ドロップ
      dropArea.addEventListener('drop', function (e) {
        e.preventDefault();
        upload(e.dataTransfer.files);
      });

    })();
  </script>
</body>
</html>
