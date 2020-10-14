<?php 
include 'config.php';

$album_page = isset($_POST['albumpage']) ? $_POST['albumpage'] : ''; // generated in main javascript file to psss it via ajax

$albumpath = sanitize($_POST['alb']);
$album = basename($albumpath);
$albumpath = $root.$albumsDir.'/'.$album;

$file = sanitize($_POST['img']);

$albums_in_maindir = scandir($root.$albumsDir);
if (!in_array($album, $albums_in_maindir)) { die('Invalid Request'); } // check requested album against directory traverse
if (!file_exists($albumpath.'/'.$file)) { die('No image exists in specified location'); } // check if image exists

$scan_files = glob($albumpath.'/*.*', GLOB_BRACE);
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

foreach($scan_files as $f){ 
	$files[] = basename($f);  // extract filenames from path and create an array	
}
$file_parts = pathinfo($file);
$file_name = $file_parts['filename'];
$prefix = explode('-', $file_name, 2);
$ext = $file_parts['extension'];


echo url_start($albumsDir.'/'.$album, $file, 0, 'fgicon icon-expand', 'target="_blank"').$url_end; ?>
<a href="#" id="fgOverlay-close" class="fgicon icon-close" title="Close"></a>
<a href="#" id="fgOverlay-drawer" class="fgicon icon-drawer" title="Show/Hide info pane"></a>

<?php	
$album_page = strtok($album_page, '?'); // strip url parameters
$album_url = $album_page.'?alb='.urlencode($albumpath).'&img='.urlencode($file)
?>

<a href="#" id="fgOverlay-share" class="tooltip fgicon icon-share" title="Share URL" data-text="<?php echo trim($album_url); ?>"></a>
<?php echo url_start($albumsDir.'/'.$album, $file, 0, 'fgicon icon-open', 'target="_blank"').$url_end; ?>

<div id="leftCol">
		
	<div class="fgmainspinner"></div>
	
	<?php	
	if($ext=='mp4' || $ext=='webm')
	{ ?>
		<video class="vidFrame" width="100%" controls autostart="false">
			<source src="<?php echo $albumsDir.'/'.$album.'/'.$file; ?>" type="video/mp4">
			<source src="<?php echo $albumsDir.'/'.$album.'/'.$file; ?>" type="video/webm" />
			Your browser does not support the mp4 tag.
		</video>
	<?php
	}
	elseif($ext=='mp3')
	{ ?>
		<audio class="audFrame" controls autostart="false">
			<source src="<?php echo $albumsDir.'/'.$album.'/'.$file; ?>" type="audio/mpeg">
			Your browser does not support the mp3 tag
		</audio>	
	<?php
	}
	else
	{ ?>
		<div id="mainImage">
		
			<?php
			switch($prefix[0])
			{
				case "utube":
				$video_id = $prefix[1];
				echo '<iframe width="100%" class="vidFrame" src="https://www.youtube.com/embed/'.$video_id.'" frameborder="0" allowfullscreen></iframe>';
				break;
				
				case "vimeo":
				$video_id = $prefix[1];
				echo '<iframe width="100%" class="vidFrame" src="https://player.vimeo.com/video/'.$video_id .'?portrait=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				break;
								
				default:
				echo show_thumb($root, $fgDir, $albumsDir, $album, '', $file, $make_thumb_width, 0);
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
			if(isset($files[$i]) && is_file($albumpath.'/'. $files[$i]))
			{   		    	   		    												
				if($files[$i]===$file)
				{  				  					    											
					$p = ($i == 0 ? $numFiles-1 : $i-1);
					$n = ($i == $numFiles-1 ? 0 : $i+1);
					$nf = $i+1;	
							
					echo url_start($albumpath, $files[$p], $i, 'showimage fgicon icon-left').$url_end;
					echo '<span class="itemnums">'.$nf.' of '.$numFiles.'</span>';
					echo url_start($albumpath, $files[$n], $i, 'showimage fgicon icon-right').$url_end; 
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
	
	<h3>Album: <?php echo $album; ?></h3>
	<?php
	echo $file.'<p class="fgclear"></p>';
	
	if($showExiff)
	{	
		if(extension_loaded('exif')) // check if exif extension is available
		{
			if(exif_imagetype($albumpath.'/'.$file) == IMAGETYPE_JPEG) // check if file format is jpg
			{
				$exif = @exif_read_data($albumpath.'/'.$file, 'IFD0');
				$exif = @exif_read_data($albumpath.'/'.$file, 0, true); 
				
				// show available exif data
				/*foreach ($exif as $key => $section)
				{
					foreach ($section as $name => $val) 
					{
						echo "$key.$name: $val<br>";
					}
				}*/
				?>
							
				<div class="exif-info">
					<?php
					if(isset($exif['IFD0']['Width']) && isset($exif['IFD0']['Height']))
					{
						echo '<span class="exifname">Dimensions</span>: '.$exif['IFD0']['Width'].' x '.$exif['IFD0']['Height'];
					}
							
					echo (isset($exif['IFD0']['Artist']) ? '<p class="fgclear"></p><span class="exifname">Artist: </span>'.$exif['IFD0']['Artist'] : '');
									
					echo (isset($exif['IFD0']['Copyright']) ? '<p class="fgclear"></p><span class="exifname">Copyright: </span>'.$exif['IFD0']['Copyright'] : ''); 
					
					echo (isset($exif['IFD0']['Comments']) ? '<p class="fgclear"></p><span class="exifname">Comments: </span>'.$exif['IFD0']['Comments'] : ''); 
					?> 
				</div>
			
			<?php
			}
		}	
	} 

	// image description if available
	echo itemDescription($albumpath, $file) ? '<span class="exifname">Description: </span>'.itemDescription($albumpath, $file) : ''; ?>
	
	<p></p>
	<br class="exifname">
		
	<div id="thumb-container">	
		
		<?php
		for( $i=0; $i <= $numFiles; $i++ )
		{   
			if(isset($files[$i]) && is_file($albumpath.'/'. $files[$i]))
			{   		    	   		    																  					    															
				$thumbClass = ($files[$i] == $file ? ' selected' : '');
				$thumb = show_thumb($root, $fgDir, $albumsDir, $album, 'thumbs/', $files[$i], $make_thumb_width, 0);
				         				
				echo '<div class="thumb'.$thumbClass.'">'.url_start($albumpath, $files[$i], $i).$thumb.$url_end.'</div>';
			}
		} ?>
		
	</div>
	
</div>
</div>
</div>

<script>
$(function() {	

	var screenHeight = $(window).height(), /* height of browser viewport */	
		screenWidth = $(window).width(),
		mainImage = $('#mainImage'),
		infoBox = $('#infoBox'),
		infoboxState = localStorage.getItem('infobox'); 		
				
	// hide scroolbar
	$('body').css({'overflow':'hidden'});
	
	// set height of elements conditionally
	if(screenWidth > 480) {
		infoBox.height(screenHeight);
		mainImage.height(screenHeight);
	}
	
	if(infoboxState == 1) 
	{	
		$('#infoBox').addClass('infoBoxToggle');
		$('#leftCol').addClass('leftColFW');
	}
	else
	{
		$('#infoBox').removeClass('infoBoxToggle');	
		$('#leftCol').removeClass('leftColFW');
	}
		
});		
</script>
