<?php
//画像サーバーのドメイン（''にすればファイルサーバー無しと判断する）、末尾の/は付けないこと
//デフォルトのCodeigniterで扱うURLにくっついている'index.php'を外す処理をしているなら末尾の'/index.php'は削除
$config['video_server'] = 'https://localhost/index.php';

//ホワイトリストIP番号
//この配列の中に存在しないIPアドレスからのアップロードを受け付けない
$config['ip_white_list'] = array('192.168.11.88','192.168.11.59');
