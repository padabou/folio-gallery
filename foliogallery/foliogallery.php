<?php 
include 'config.php';
$targetid = isset($_POST['targetid']) ? $_POST['targetid'] : ''; // id of gallery or album
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : ''; // page number if not inline view
?>

<div class="fg">

<?php
if (empty($_REQUEST['album'])) // if no album requested, show all albums
{	
	$albums = []; 
	$albums = glob($root.$albumsDir.'/*', GLOB_ONLYDIR); // scan only directories
	$numAlbums = count($albums);
		
	// sort the abums array	
	if($sort_albums == 'newest')
	{
		array_multisort(array_map('filemtime', $albums ), SORT_NUMERIC, SORT_DESC, $albums);
	}
	elseif($sort_albums == 'oldest')
	{
		array_multisort(array_map('filemtime', $albums ), SORT_NUMERIC, SORT_ASC, $albums);
	}
	elseif($sort_albums == 'namereverse')
	{
		rsort($albums, SORT_FLAG_CASE);
	}
	else
	{
		sort($albums, SORT_FLAG_CASE);
	}
				 
	if($numAlbums == 0) 
	{
		echo '<div class="fgtitlebar"><p>There are no albums</p></div>';
	}
	else
	{		  				
		if($inline_albums)
		{
			$start = 0;
			$thumbs_per_page = $numAlbums;
		}
		else
		{		
			if($numAlbums <= $thumbs_per_page)
			{				
				$start = 0;	
				$numPages = 1;
				$currentPage = 1;			
			}
			else
			{
				$numPages = ceil( $numAlbums / $thumbs_per_page );
				
				if($page != '')
				{
					$currentPage = $page > $numPages ? $numPages : $page; 
				} 
				else 
				{
					$currentPage = 1;
				}
				
				$start = ($currentPage * $thumbs_per_page) - $thumbs_per_page;
				$show_pagination = TRUE;
			}	
		}
		
		if($show_titles)
		{ ?>
			<span class="fgtitle fgm5-left">Gallery, <?php echo $numAlbums; ?> albums</span>
				
			<p class="fgclear"></p>
		<?php
		} ?>
		
		<div class="thumbwrap-outer">
			<?php
			if($inline_albums)
			{ ?>
				<span class="fgleft"></span>
				<span class="fgright"></span>
			<?php
			} ?>
			
			<div class="fgthumbwrap">
			<div<?php if($inline_albums){ ?> class="fgthumbwrap-inner"<?php } ?>>
				
				<?php				
				$end = $start + $thumbs_per_page;						
				for($i=$start; $i<$end; $i++)
				{   									
					if(isset($albums[$i]))
					{
						// create thumbs dir if it does not exist and make it writable
						if(!is_dir($albums[$i].'/thumbs')) 
						{						
							if(is_writable($root.$albumsDir.'/'.$albums[$i]))
							{
								mkdir($root.$albumsDir.'/'.$albums[$i].'/thumbs');
								chmod($root.$albumsDir.'/'.$albums[$i].'/thumbs', 0777);
							} 
							else
							{
								echo $albums[$i].'" directory is not writable.';
							}		 	
						}
						
						$album = basename($albums[$i]); // extract folder name from path
																		
						$thumb_pool = glob($root.$albumsDir.'/'.$album.'/*.{'.$image_ext_string.'}', GLOB_BRACE);		
										
						if (count($thumb_pool) == 0)
						{ 
							$thumb = '<img src="'.$fgDir.'/images/'.$no_thumb.'" alt="">';
						}
						else
						{
							$rand_thumb = ($random_thumbs ? $thumb_pool[array_rand($thumb_pool)] : $thumb_pool[0]); // display a random thumb or the 1st thumb	
							$rand_thumb = basename($rand_thumb); // extract just the filename
							$thumb = show_thumb($root, $fgDir, $albumsDir, $album, 'thumbs/', $rand_thumb, $make_thumb_width);									
						}						
						?>
										 
						<div class="fgthumb">
							
							<div class="fgthumb-itself">
								<a class="showAlb" title="<?php echo $album; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>?album=<?php echo urlencode($album); ?>">
									<?php echo $thumb; ?>
								</a>
							</div>
							
							<?php
							if($album_captions)
							{ ?>
							
								<p class="fgclear"></p>	
								<div class="fgcaption">
									<div class="fgcaption-inner">
										<a class="showAlb" title="<?php echo $album; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>?album=<?php echo urlencode($album); ?>">
											<?php echo $album; ?>
											<span class="gradient-end"></span>
										</a>	
									</div>
								</div>
							
							<?php
							} ?>		
						
						</div>
					
					<?php
					}
					
				} ?>
			
			</div>
			</div>	
			
			<?php
			if($show_pagination)
			{ ?>
				<p class="fgclear"></p>
	  
				<div class="paginate-wrapper">
					<?php 
					$paginate_link_class = ($targetid == '') ? '' : 'pag';
					echo paginate_fggallery($numPages,'',$targetid,0,$currentPage,$paginate_link_class); ?>
				</div>
			<?php
			} ?>
			
		</div>

    <?php
	}
	
} 
else //display photos in album 
{
		
	$requested_album = sanitize($_REQUEST['album']); // xss prevention
	
	$user_path = realpath(__DIR__.'/'.$albumsDir.'/'.$requested_album); 
	$safe_path = realpath(__DIR__.'/'.$albumsDir.'/');
		
	// make sure $user_path is inside $safe_path
	if (substr($user_path, 0, strlen($safe_path)) != $safe_path) 
	{
	   die ('Nothing to see here!');
	}
	
	$albumpath = $root.$albumsDir.'/'.$requested_album;
	
	$scan_files = [];
	$scan_files = glob($albumpath.'/*.*', GLOB_BRACE);
	$numFiles = count($scan_files);
				
	// sort the files array	
	if($sort_files == 'newest')
	{
		array_multisort(array_map('filemtime', $scan_files ), SORT_NUMERIC, SORT_DESC, $scan_files);
	}
	elseif($sort_files == 'oldest')
	{
		array_multisort(array_map('filemtime', $scan_files ), SORT_NUMERIC, SORT_ASC, $scan_files);
	}
	elseif($sort_files == 'namereverse')
	{
		rsort($scan_files, SORT_FLAG_CASE);
	}
	else
	{
		sort($scan_files, SORT_FLAG_CASE);
	}
	
	$files = [];
	foreach($scan_files as $f) { $files[] = basename($f); }
		
	if($show_titles)
	{	
		if($fullAlbum==1)
		{ ?>
			<a class="fgtitle fgrefresh fgm5-left" href="#">Index</a><span class="arrow-right"></span>
			<span class="fgtitle"><?php echo $requested_album; ?>, <?php echo $numFiles; ?> items</span>
		<?php 
		}
		elseif($fgrefresh==1)
		{ ?>
			<a class="fgtitle fgm5-left" href="<?php echo $_SERVER['PHP_SELF']; ?>">Index</a><span class="arrow-right"></span>
			<span class="fgtitle"><?php echo $requested_album; ?>, <?php echo $numFiles; ?> items</span>
		<?php
		} 
		else
		{ ?>
			<span class="fgtitle fgm5-left"><?php echo $requested_album; ?>, <?php echo $numFiles; ?> items</span>
	    <?php 
		} ?>	   
	   	<p class="fgclear"></p>
	<?php
	}
	
	if($numFiles == 0)
	{ ?>
		<p class="fgclear"></p>No images in this album.	
	<?php
	}
	else	
	{							 				
		// create thumbs dir if it does not exist and make it writable
		if(!is_dir($albumpath.'/thumbs')) 
		{						
			if(is_writable($albumpath))
			{
				mkdir($albumpath.'/thumbs');
				chmod($albumpath.'/thumbs', 0777);
			} 
			else
			{
				echo $albumpath.'" directory is not writable.';
			}		 	
		}
		
		if($inline_thumbs)
		{
			$start = 0;
			$thumbs_per_page = $numFiles;
		}
		else
		{		
			if($numFiles <= $thumbs_per_page)
			{				
				$start = 0;	
				$numPages = 1;
				$currentPage = 1;			
			}
			else
			{
				$numPages = ceil( $numFiles / $thumbs_per_page );
				
				if($page != '')
				{
					$currentPage = $page > $numPages ? $numPages : $page; 
				} 
				else 
				{
					$currentPage = 1;
				}
				
				$start = ($currentPage * $thumbs_per_page) - $thumbs_per_page;
				$show_pagination = TRUE;
			}	
		}	
		?>

		<div class="fgthumbwrap-outer">
		
			<?php			
			if($inline_thumbs)
			{ ?>
				<span class="fgleft"></span>
				<span class="fgright"></span>
			<?php
			} ?>
		
			<div class="fgthumbwrap">		
			<div<?php if($inline_thumbs){ ?> class="fgthumbwrap-inner"<?php } ?>>
			
				<?php
				$end = $start + $thumbs_per_page;				 
				for($i=$start; $i<$end; $i++)
				{   									
					if(isset($files[$i]) && is_file($albumpath.'/'.$files[$i]))
					{   		    		
						$file = $files[$i];
						
						$full_caption = (itemDescription($albumpath, $file) ? itemDescription($albumpath, $file) : $file); // image captions
						$caption = encodeto($full_caption);		
						$thumb = show_thumb($root, $fgDir, $albumsDir, $requested_album, 'thumbs/', $file, $make_thumb_width);			
						?>	   
					   
						<div class="fgthumb">
							
							<div class="fgthumb-itself">
								<?php echo url_start($albumpath,$file).$thumb.$url_end; ?>
							</div>
							
							<?php
							if($thumb_captions) 
							{ ?>
								<p class="fgclear"></p>
								<div class="fgcaption">
									<div class="fgcaption-inner">
										<?php echo url_start($albumpath,$file).$caption.$url_end; ?>
										<span class="gradient-end"></span>
									</div>	
								</div>
							<?php 
							} ?>
							
						</div> 
							
					<?php				
					} 
				
				} ?>
			
			</div>
			</div>
			
			<?php
			if($show_pagination)
			{ ?>
				<p class="fgclear"></p>
	  
				<div class="paginate-wrapper">
					<?php 
					$paginate_link_class = ($targetid == '') ? '' : 'pag';
					echo paginate_fggallery($numPages,$requested_album,$targetid,$fullAlbum,$currentPage,$paginate_link_class); ?>
				</div>
			<?php
			} ?>	

		</div>
				
		<?php 
		if($album_descriptions)
		{
			echo '<div class="description-wrapper">'.encodeto(itemDescription($albumpath)).'</div>'; //display album description 
	  	}
	  
	} // end if numFiles not 0
	
} ?>
</div>


<div id="fgOverlay"></div>	

<?php
if($inline_albums || $inline_thumbs)
{ ?>
	<script>	
	$(function() {
				
		var targetId    = '<?php echo $targetid; ?>',
			targetDiv   = '#'+targetId,
			blockWidth  = $(targetDiv+' .fgthumbwrap').width(), // .thumbwrap width
			thumb       = $(targetDiv+' .fgthumb'),
			sliderWidth = thumb.length * thumb.outerWidth(true), // .thumbwrap-inner width	
			thumbHeight = thumb.outerHeight(true);
				
		$(targetDiv+' .fgthumbwrap-inner').css({
			'width':sliderWidth+'px',
			'height':thumbHeight+'px'
		});
		
		$('.fgright, .fgleft').css({'top': (thumbHeight/2) + 'px'});		
		
		if(sliderWidth > blockWidth) {
			$(targetDiv+' .fgright').show();
		}
		
		if($(targetDiv+' .fgthumbwrap-inner').scrollLeft() >= 17){
			 $(targetDiv+' .fgright').show();
			 $(targetDiv+' .fgleft').show();   
		}
		
		// show left right buttons on scroll	
		$(targetDiv+' .fgthumbwrap').scroll(function() {
			$(targetDiv+' .fgright').show();
			$(targetDiv+' .fgleft').show();
		});	
						
	});
	</script>	
<?php
} ?>	
