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
</style>
<link type="text/css" rel="stylesheet" href="foliogallery/foliogallery.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="foliogallery/foliogallery.js"></script>
</head>
<body>
<header>
    <nav class="nav">
        <div class="navbar" id="myTopnav">
            <div class="dropdown">
                <a href="/">Accueil</a>
            </div>

        </div>
    </nav>
</header>
	<br />
	<br />

	<p>&nbsp;</p>
	
	<?php	
	include 'foliogallery/foliogallery-functions.php';

	$content_using_divs = '	
	<!-- display list of folders in "albums" folder -->
	<div id="folioGallery" class="folioGallery"></div>
	
	<p>&nbsp;</p>
	
	<!-- display images located in "Videos" subfolder -->
	<div id="folioGallery3" class="folioGallery" title="Videos"></div>
	
	<p>&nbsp;</p>
	
	<!-- display images located in "Scenery" subfolder -->
	<div id="folioGallery1" class="folioGallery" title="Scenery"></div>
	
	<p>&nbsp;</p>

	<!-- display images located in "Los Angeles" subfolder -->
	<div id="folioGallery2" class="folioGallery" title="Los Angeles"></div>
	
	<p>&nbsp;</p>
				
	<div align="center">folioGallery - Installation and instructions @ <a href="http://foliopages.com/php-jquery-ajax-photo-gallery-no-database">FolioPages.com</a></div>
	
	<p>&nbsp;</p>
	';
	
	$content = '	
	
	<!-- display all "albums" -->
	[foliogallery]
	
	<p>&nbsp;</p>
	
	<!-- display list of folders in "albums" folder -->
	[foliogallery=Videos]
	
	<p>&nbsp;</p>	
	
	<!-- display images located in "Scenery" subfolder -->
	[foliogallery=Scenery]
	
	<p>&nbsp;</p>
	<!-- display images located in "Loa Angeles" subfolder -->
	[foliogallery=Los Angeles]
	
	';
	echo foliogallery_shortcode($content); 
	?>

	<p>&nbsp;</p>
				
	<div align="center">folioGallery - Installation and instructions @ <a href="http://foliopages.com/php-jquery-ajax-photo-gallery-no-database">FolioPages.com</a></div>
	
	<p>&nbsp;</p>

</body>
</html>