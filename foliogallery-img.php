<!--
	folioGallery v4.1 - 2019-05-27
	(c) 2019 Harry Ghazanian - foliopages.com/php-jquery-ajax-photo-gallery-no-database
	This content is released under the http://www.opensource.org/licenses/mit-license.php MIT License.
-->
<?php 
include 'foliogallery/config.php';

$album_page = isset($_POST['albumpage']) ? $_POST['albumpage'] : ''; // generated in main javascript file then pssed via ajax

$album = sanitize($_POST['alb']);
$image = sanitize($_POST['img']);
$album_name = explode('/', $album, 2);
$album_name = $album_name[1];

$albums_in_maindir = scandir($mainFolder);
if (!in_array($album_name, $albums_in_maindir)) { die('Invalid Request'); } // check requested album against directory traverse
if (!file_exists($album.'/'.$image)) { die('No image exists in specified location'); } // check if image exists

$scan_files = glob($album.'/*.{'.$supported_extensions.'}', GLOB_BRACE);
$numFiles = count($scan_files);

// sort the scaned files array	
if($sort_files == 'newest')
{
	array_multisort(array_map('filemtime', $scan_files ), SORT_NUMERIC, SORT_DESC, $scan_files);
}
elseif($sort_files == 'oldest')
{
	array_multisort(array_map('filemtime', $scan_files ), SORT_NUMERIC, SORT_ASC, $scan_files);
}
else
{
	sort($scan_files);
}

foreach($scan_files as $f){ $files[] = basename($f); } // extract filenames from path and create an array	

$file_parts = pathinfo($image);
$file_name = $file_parts['filename'];
$prefix = explode('-', $file_name, 2);
$ext = $file_parts['extension'];
?>

<?php echo url_start($album, $image, 0, 'fgicon icon-expand', 'target="_blank"').$url_end; ?>
<a href="#" id="fgOverlay-close" class="fgicon icon-close" title=""></a>

<div id="leftCol">	
		
	<div class="fgmainspinner"></div>
	
	<?php
	if($ext=='mp4')
	{ 
		$isImage = FALSE; ?>
		<video class="vidFrame" src="<?php echo $album.'/'.$image; ?>" width="100%" controls preload autostart="false"></video>
	<?php
	}
	elseif($ext=='mp3')
	{ 
		$isImage = FALSE; ?>
		<div class="audFrame"><audio src="<?php echo $album.'/'.$image; ?>" controls preload autostart="false"></audio></div>	
	<?php
	}
	elseif($ext=='pdf')
	{ 
		$isImage = FALSE; ?>
		<a href="<?php echo $album.'/'.$file_name.'.pdf'; ?>"><img src="foliogallery/images/pdf.png" alt="" style="position:absolute;margin:auto;top:0;right:0;bottom:0;left:0"></a>	
	<?php
	}
	else
	{ ?>
	
		<div id="mainImage">
		
			<?php
			switch($prefix[0])
			{
				case "utube":
				$isImage = FALSE;
				$isVideo = TRUE;
				$video_id = $prefix[1];
				echo '<iframe width="100%" class="vidFrame" src="https://www.youtube.com/embed/'.$video_id.'" frameborder="0" allowfullscreen></iframe>';
				break;
				
				case "vimeo":
				$isImage = FALSE;
				$isVideo = TRUE;
				$video_id = $prefix[1];
				echo '<iframe width="100%" class="vidFrame" src="https://player.vimeo.com/video/'.$video_id .'?portrait=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				break;
				
				default:
				$isImage = TRUE;
				$isVideo = FALSE;
				echo '<img class="imgFrame" src="'.$album.'/'.$image.'" alt="" />';
				break;	
			} ?>
		
		</div>
			
	<?php
	}		
	?>
			
	<div id="bottombar" class="bottombar">
		
		<?php	
		for( $i=0; $i <= $numFiles; $i++ )
		{   
			if(isset($files[$i]) && is_file($album .'/'. $files[$i]))
			{   		    	   		    												
				if($files[$i]===$image)
				{  				  					    											
					$p = ($i == 0 ? $numFiles-1 : $i-1);
					$n = ($i == $numFiles-1 ? 0 : $i+1);
					$nf = $i+1;	
							
					echo url_start($album, $files[$p], $i, 'showimage fgicon icon-left').$url_end;
					echo '<span class="itemnums">'.$nf.' of '.$numFiles.'</span>';
					echo url_start($album, $files[$n], $i, 'showimage fgicon icon-right').$url_end; 
				}
				else
				{
					echo '';
				}					
			}
		}
		?>
		
	</div>
	
</div>	

<div id="infoBox">	
<div class="tablediv">
<div class="celldiv">

	<h3>Album: <?php echo $album_name; ?></h3> 
	
	<?php
	if($isImage)
	{
		list($img_width_orig, $img_height_orig) = getimagesize($album.'/'.$image);
		$exif = @exif_read_data($album.'/'.$image, 'IFD0');
		$exif = @exif_read_data($album.'/'.$image, 0, true); 
	
		if($showExiff)
		{ ?>
						
			<div class="exif-info">
				<span class="exifname">Dimensions</span>: <?php echo $img_width_orig; ?> x <?php echo $img_height_orig; ?>
						
				<?php echo (isset($exif['IFD0']['Artist']) ? '<br><span class="exifname">Artist: </span>'.$exif['IFD0']['Artist'] : ''); ?>
								
				<?php echo (isset($exif['COMPUTED']['Copyright']) ? '<br><span class="exifname">Copyright: </span>'.$exif['COMPUTED']['Copyright'] : ''); ?> 
			
				<br>
			</div>
		
		<?php
		}
	} 
	?>
		
	<?php echo itemDescription($album, $image) ? '<span class="exifname">Description: </span>'.itemDescription($album, $image) : $image; // image description if available ?>
	
	<p></p>
	<br class="exifname">

	<?php	
	$album_page = strtok($album_page, '?'); // strip url parameters
	$album_url = $album_page.'?alb='.urlencode($mainFolder.'/'.$album_name).'&img='.urlencode($image)
	?>
	
	<a href="#" class="tooltip btn ntn-share" rev="<?php echo trim($album_url); ?>"><i class="material-icons">share</i>&nbsp;Share URL</a>
	
	<?php echo url_start($album, $image, 0, 'btn icon-expand', 'target="_blank"'); ?><i class="material-icons">open_in_new</i>&nbsp;Open File<?php echo $url_end; ?>
	
	<br>
		
	<div id="thumb-container">	
		
		<?php
		for( $i=0; $i <= $numFiles; $i++ )
		{   
			if(isset($files[$i]) && is_file($album .'/'. $files[$i]))
			{   		    	   		    																  					    											
				$file_parts = pathinfo($files[$i]);
				$filename = $file_parts['filename'];
				$this_ext = $file_parts['extension'];
				
				$thumbClass = ($files[$i] == $image ? ' selected' : '');
				
				$thumb = show_thumb($album, $files[$i], $make_thumb_width);
				
				echo '<div class="thumb'.$thumbClass.'">'.url_start($album, $files[$i], $i).$thumb.$url_end.'</div>';
								
			}
		} ?>
		
	</div>
	
</div>
</div>
</div>

<script>
$(function() {	

// load unload spinner

	var screenHeight = $(window).height(), /* height of browser viewport */	
		screenWidth = $(window).width(),
		mainImage = $('#fgOverlay #mainImage'),
		infoBox = $('#fgOverlay #infoBox'); 
				
	// hide scroolbar
	$('body').css({'overflow':'hidden'});
	
	// set height of elements conditionally
	if(screenWidth > 480) {
		infoBox.height(screenHeight);
		mainImage.height(screenHeight);
	}	
	
});		
</script>
