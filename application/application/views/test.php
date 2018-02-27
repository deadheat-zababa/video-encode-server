<html>
 <head>
  <title>Upload Form</title>
 </head>
 <body>

 <h2>Upload File</h2>
 <h3>usage</h3>

 <?php echo $error;?>

 <?php echo form_open_multipart('upload/do_upload');?>

 <input type="file" accept="video/*" name="userfile" />

 <br /><br />

 <input type="submit" value="upload" />
 </from> 
 
 </body>
</html>
