<html>
<head>
<title>アップロードフォーム</title>
</head>
<body>

<?php echo $error;?>

<?php echo form_open_multipart('upload/do_upload');?>

<input type="file" name="userfile" size="2048" />

<br /><br />

<input type="submit" value="upload" />

</form>

</body>
</html>
