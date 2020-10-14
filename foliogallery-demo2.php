<!DOCTYPE html>
<html lang="en">
<head>
<title>Image Gallery By FolioPages.com</title>
<meta charset="UTF-8">
<meta name="viewport" content="initial-scale=1, width=device-width" />
<style type="text/css">
body {
background:#eee;
margin:0;
padding:0;
font:13px arial, Helvetica, sans-serif;
color:#222;
}
.top-container{
display:block;
position:relative;
width:94%;
padding:3% 3% 30px 3%;
max-width:560px;
margin:0 auto;
font-size:16px;
line-height:28px
}
</style>
<link type="text/css" rel="stylesheet" href="foliogallery/foliogallery.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="foliogallery/foliogallery.js"></script>
</head>
<body>

<br />
<br />

<div class="top-container">
	If you want to embed the gallery in your php page using php include, you must set the $root and $indexit variables before including the gallery file.
	<br>
	See the source code of this page.
</div>
<?php 
$root = '';
$indexit = 1;
include 'foliogallery/foliogallery.php'; 
?>

<br />
<br />

<div align="center">folioGallery - Installation and instructions @ <a href="http://foliopages.com/php-jquery-ajax-photo-gallery-no-database">FolioPages.com</a></div>

<br />
<br />


</body>
</html>