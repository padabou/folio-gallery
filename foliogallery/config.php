<?php
/***** comment out the following 3 lines to display php errors *****/
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

/***** memory setting, if you get out of memory message during thumbnail creation, uncomment the following line to increase memory_limit size *****/ 
// ini_set('memory_limit', '256M');

/***** global settings *****/
$rootDir  		   = '../';		     // root directory relative to $fgDir - this means 1 level up  
$fgDir   		   = 'foliogallery'; // name of folder where foliogallery files are located
$albumsDir         = 'albums';       // main folder that holds albums located on website's root
$no_thumb          = 'noimg.jpg';    // show this when no thumbnail exists 
$image_ext		   = ['jpg','jpeg','png','gif','JPG','JPEG','PNG','GIF','webp','WEBP'];
$make_thumb_width  = 240;         	 // max width of thumbnails when being created
$show_titles 	   = TRUE;        	 // show/hide titles above gallery or albums
$showExiff         = TRUE;        	 // TRUE will display exiff info of image in image view overlay info box - only for image files
$thumbs_per_page   = '30';        	 // number of thumnbnails per page -- effective only if $inline_albums = FALSE or $inline_thumbs = FALSE 

/***** gallery settings *****/
$inline_albums     = TRUE;        // TRUE will display album thumbs in the main gallery in one line scroller, FALSE will display all albums in gallery
$album_captions    = TRUE;        // TRUE will display file names or captions for each album, FALSE will display no captions
$sort_albums       = 'newest';    // OPTIONS: newest: sort by modified date. oldest: sort by oldest. name: sort alphabetically. namereverse: sort alphabetically in reverse
$random_thumbs     = TRUE;        // TRUE will display random thumbnails for albums when $inline_gallery is TRUE, FALSE will display the first image
$album_descriptions= TRUE;        // TRUE will show description for each album in gallery, FALSE will not

/***** album settings *****/
$inline_thumbs	   = TRUE;        // TRUE will display thumbs in album in one line scroller, FALSE will display all thumbs 
$thumb_captions    = TRUE;        // TRUE will display file names or captions for each thumb in album, FALSE will display no captions
$sort_files        = 'newest';    // newest: sort by modified date. oldest: sort by oldest. name: sort alphabetically. namereverse: sort alphabetically in reverse

/***** DO NOT CHANGE the following variables if you are not sure*****/

$root = (isset($root)) ? $root : $rootDir;
$fgrefresh = (isset($indexit) && $indexit==1) ? 1 : 0;
$show_pagination = FALSE;

$url_end = '</a>';
$fullAlbum = !empty($_REQUEST['fullalbum']) ? 1 : 0;

$image_ext_string  = implode(',',$image_ext); // convert array to string

/***** PHP functions *****/

// sanitize string
function sanitize($string)
{
	$string = htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
	return $string;
}

// encode string to
function encodeto($string)
{
	$string = mb_convert_encoding(trim($string), 'UTF-8', 'HTML-ENTITIES');
	return $string;
}

// function to show thumbnail for each image
function show_thumb($root, $fgDir, $albumsDir, $folder, $thumbs_dir, $file, $make_thumb_width, $create_thumb=1) {
												
	$image_ext = ['jpg','jpeg','png','gif','JPG','JPEG','PNG','GIF','webp','WEBP'];
	
	$html = '';
	$file_parts = pathinfo($file);
	$filename = $file_parts['filename'];
	$ext = $file_parts['extension'];
	
	if (file_exists($root.$albumsDir.'/'.$folder.'/thumbs/'.$filename.'.'.$ext))
	{
		return '<img src="'.$albumsDir.'/'.$folder.'/'.$thumbs_dir.$filename.'.'.$ext.'" alt="">';
	}
	else
	{									
		if(in_array($ext,$image_ext))
		{
			if($create_thumb==1)
			{
				make_thumb($root.$albumsDir.'/'.$folder, $file, $root.$albumsDir.'/'.$folder.'/thumbs/'.$file, $make_thumb_width);
			}
			return '<img src="'.$albumsDir.'/'.$folder.'/'.$thumbs_dir.$filename.'.'.$ext.'" alt="">';	
		}
		elseif(file_exists($root.$albumsDir.'/'.$folder.'/thumbs/'.$filename.'.jpg'))
		{
			return '<img src="'.$albumsDir.'/'.$folder.'/'.$thumbs_dir.$filename.'.jpg" alt="">';
		}
		elseif(file_exists('images/'.$ext.'.jpg'))
		{
			return '<img src="'.$fgDir.'/images/'.$ext.'.jpg" alt="">';
		}
		else
		{
			return '<img src="'.$fgDir.'/images/noimg.jpg" alt="">';
		}
	}	
}

// function to create thumbnails from images
function make_thumb($folder, $file, $dest, $thumb_width)
{		
	$file_parts = pathinfo($file);
	$ext = strtolower($file_parts['extension']);
	
	if(isset($ext) && $ext != '')
	{	
		switch($ext)
		{
			case "jpeg":		
			case "jpg":
			$source_image = imagecreatefromjpeg($folder.'/'.$file);
			break;
			
			case "png":
			$source_image = imagecreatefrompng($folder.'/'.$file);
			break;
			
			case "gif":
			$source_image = imagecreatefromgif($folder.'/'.$file);
			break;
			
			case "webp":
			$source_image = imagecreatefromwebp($folder.'/'.$file);
			break;
		}	
		
		if(isset($source_image))
		{
			$width = imagesx($source_image);
			$height = imagesy($source_image);
			
			if($width < $thumb_width) // if original image is smaller don't resize it
			{
				$thumb_width = $width;
				$thumb_height = $height;
			}
			else
			{
				$thumb_height = floor($height*($thumb_width/$width));
			}
			
			$virtual_image = imagecreatetruecolor($thumb_width,$thumb_height);
			
			if($ext == "gif" or $ext == "png") // preserve transparency
			{
				imagecolortransparent($virtual_image, imagecolorallocatealpha($virtual_image, 0, 0, 0, 127));
				imagealphablending($virtual_image, false);
				imagesavealpha($virtual_image, true);
			}
			
			imagecopyresampled($virtual_image,$source_image,0,0,0,0,$thumb_width,$thumb_height,$width,$height);
			
			switch($ext)
			{
				case 'jpeg':				
				case 'jpg': imagejpeg ($virtual_image, $dest);
				break;
				
				case 'gif': imagegif($virtual_image, $dest); 
				break;
				
				case 'png': imagepng($virtual_image, $dest); 
				break;
				
				case 'webp': imagewebp($virtual_image, $dest); 
				break;

			}
			
			imagedestroy($virtual_image); 
			imagedestroy($source_image);
		}	
		
	}	
	
}

// get album and image descriptions
function itemDescription($album, $file='')
{
	if(file_exists($album.'/descriptions/descriptions.txt'))
	{
		$lines_array = file($album.'/descriptions/descriptions.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); 
		if($lines_array)
		{
			if($file == '')
			{
				$album_line = explode(';', $lines_array[0]); 
				return (!empty($album_line[0]) && $album_line[0] == 'album' ? $album_line[1] : '');
			}
			else
			{
				foreach($lines_array as $img_line)
				{	
					if(!empty($img_line)) {
						$img_desc = explode(';', $img_line);	
						if($img_desc[0] == $file) { return $img_desc[1]; }
					}
				}
			}	
		}
		else
		{
			return '';
		}
	}	
}

// return first part of url
function url_start($album, $file, $i=0, $class='showimage', $target='') 
{
	
	$file_parts = pathinfo($file);
	$file_name = $file_parts['filename']; // filename without extension
	$prefix = explode('-', $file_name, 2);
	
	switch($prefix[0]) 
	{
		case "utube":
		$video_id = $prefix[1];
		$url_start = '<a href="http://www.youtube.com/embed/'.$video_id.'?rel=0&amp;wmode=transparent" data-album="'.$album.'" data-file="'.$file.'" tabindex="'.$i.'" title="Open External Link" class="'.$class.'" '.$target.'>';
		break;
		
		case "vimeo":
		$video_id = $prefix[1];
		$url_start = '<a href="http://player.vimeo.com/video/'.$video_id.'?rel=0&amp;wmode=transparent" data-album="'.$album.'" data-file="'.$file.'" tabindex="'.$i.'" title="Open External Link" class="'.$class.'" '.$target.'>';
		break;
		
		default:
		$url_start = '<a href="'.$album.'/'.$file.'" data-album="'.$album.'" data-albumname="'.basename($album).'" data-file="'.$file.'" tabindex="'.$i.'" title="'.$file.'" class="'.$class.'" '.$target.'>';
		break;	
	}
	
	return $url_start;
}

// display pagination
function paginate_fggallery($numPages,$album,$targetid,$fullAlbum,$currentPage,$paginate_link_class) {
        
	$html = '';
	
	$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$page_url = strtok($current_url, '?'); // with url params stripped
	
	if ($numPages > 1) 
	{
	   if ($currentPage > 1)
	   {
			$prevPage = $currentPage - 1;
		   
		   	unset($_GET['page']); // delete edit parameter;
			$_GET['page'] = $prevPage; // change page number
			$qs = http_build_query($_GET); // rebuild query string
			$url = $page_url.'?'.$qs;
		   
		    $html .= '<a href="'.$url.'" class="'.$paginate_link_class.' prev" data-vars="'.$album.'|'.$prevPage.'|'.$targetid.'|'.$fullAlbum.'"><i class="material-icons">keyboard_arrow_left</i></a>';
	   }
	   else
	   {
			$html .= '<span class="prev inactive"><i class="material-icons">keyboard_arrow_left</i></span>';
	   }	   
	   
	   for( $e=0; $e < $numPages; $e++ )
	   {
		   $p = $e+1;
		   
		   unset($_GET['page']); // delete edit parameter;
		   $_GET['page'] = $p; // change page number
		   $qs = http_build_query($_GET); // rebuild query string
		   $url = $page_url.'?'.$qs;
		   
		   $class = ($p==$currentPage ? 'current-paginate' : 'paginate'); 
		   $html .= '<a href="'.$url.'" class="'.$paginate_link_class.' '.$class.'" data-vars="'.$album.'|'.$p.'|'.$targetid.'|'.$fullAlbum.'">'.$p.'</a>';	  
	   }
	   
	   if ($currentPage != $numPages)
	   {
			$nextPage = $currentPage + 1;
		   
			unset($_GET['page']); // delete edit parameter;
			$_GET['page'] = $nextPage; // change page number
			$qs = http_build_query($_GET); // rebuild query string
			$url = $page_url.'?'.$qs;
		   	
			$html .= '<a href="'.$url.'" class="'.$paginate_link_class.' next" data-vars="'.$album.'|'.$nextPage.'|'.$targetid.'|'.$fullAlbum.'"><i class="material-icons">keyboard_arrow_right</i></a>';
	   }
	   else
	   {
		   $html .= '<span class="next inactive"><i class="material-icons">keyboard_arrow_right</i></span>';
	   }	  	 
	
	}
	
	return $html;
}


?>