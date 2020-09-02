<?php
// memory setting, if you get out of memory message during thumbnail creation, uncomment the following line, increase memory_limit size if required
//ini_set('memory_limit', '256M');

/***** global settings *****/
$mainFolder        = 'albums'; 	   // main folder that holds albums - this folder resides on root directory of your domain
$no_thumb          = 'noimg.jpg';  // show this when no thumbnail exists 
$extensions        = array('jpg','png','gif','mp3','mp4','pdf'); // allowed file extensions
$make_thumb_width  = 240;         // max width of thumbnails when being created
$show_titles 	   = TRUE;        // show/hide titles above gallery or albums
$showExiff         = TRUE;        // TRUE will display exiff info of image in image view overlay info box - only for image files
$thumbs_per_page   = '30';        // number of thumnbnails per page -- effective only if $inline_albums = FALSE or $inline_thumbs = FALSE 

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

define('FG_DIR', 'foliogallery'); // define foliogallery directory
$show_pagination = FALSE;

// accept both lowercase and uppercase extensions
$accepted_lower = array_map('strtolower', $extensions); // convert extensions array to lowercase
$accepted_upper = array_map('strtoupper', $extensions); // then uppercase
$accepted_array = array_merge($accepted_lower, $accepted_upper); // merge above arrays into 1 array 
$supported_extensions = implode(',',$accepted_array); // convert array to string 

$url_end = '</a>';
$fullAlbum = !empty($_REQUEST['fullalbum']) ? 1 : 0;

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
function show_thumb($folder, $file, $make_thumb_width) {
												
	$html = '';
	$file_parts = pathinfo($file);
	$filename = $file_parts['filename'];
	$ext = $file_parts['extension'];
	
	if (file_exists($folder.'/thumbs/'.$filename.'.'.$ext))
	{
		return '<img src="'.$folder.'/thumbs/'.$filename.'.'.$ext.'" alt="">';
	}
	else
	{
		switch($ext)
		{
			case 'mp4':	
			return '<img src="'.FG_DIR.'/images/video.jpg" alt="">';
			break;
			
			case 'mp3':
			return '<img src="'.FG_DIR.'/images/audio.jpg" alt="">';
			break;
			
			case 'pdf': 
			return '<img src="'.FG_DIR.'/images/pdf.jpg" alt="">'; 
			break;
		
			default:
			
			if(!is_dir($folder.'/thumbs')) 
			{						
				if(is_writable($folder))
				{
					mkdir($folder.'/thumbs');
					chmod($folder.'/thumbs', 0777);
					//chown($album.'/thumbs', 'apache');
					make_thumb($folder, $file, $folder.'/thumbs/'.$file, $make_thumb_width);	
					return '<img src="'.$folder.'/thumbs/'.$filename.'.'.$ext.'" alt="">';
				} 
				else
				{
					return  $folder.' is not writable';
				}
			}
			else
			{				
				make_thumb($folder, $file, $folder.'/thumbs/'.$file, $make_thumb_width);	
				return '<img src="'.$folder.'/thumbs/'.$filename.'.'.$ext.'" alt="">'; 
			}
		}	
	}
}
 
// function to create thumbnails from images
function make_thumb($folder, $file, $dest, $thumb_width)
{		
	$file_parts = pathinfo($file);
	//$filename = strtolower($file_parts['filename']);
	$ext = strtolower($file_parts['extension']);
	
	if(isset($ext) && $ext != '')
	{	
		switch($ext)
		{
			case "jpeg":		
			case "jpg":
			$source_image = imagecreatefromjpeg($folder.'/'.$file);
			fixImageOrientation($folder.'/'.$file);
			break;
			
			case "png":
			$source_image = imagecreatefrompng($folder.'/'.$file);
			break;
			
			case "gif":
			$source_image = imagecreatefromgif($folder.'/'.$file);
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

			}
			
			imagedestroy($virtual_image); 
			imagedestroy($source_image);
			
		}	
		
	}	
	
}

// get album and image descriptions
function itemDescription($album, $file='')
{
	if(file_exists($album.'/descriptions.txt'))
	{
		$lines_array = file($album.'/descriptions.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); 
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
		$url_start = '<a href="http://www.youtube.com/embed/'.$video_id.'?rel=0&amp;wmode=transparent" rel="'.$album.'" rev="'.$file.'" tabindex="'.$i.'" title="" class="'.$class.'" '.$target.'>';
		break;
		
		case "vimeo":
		$video_id = $prefix[1];
		$url_start = '<a href="http://player.vimeo.com/video/'.$video_id.'?rel=0&amp;wmode=transparent" rel="'.$album.'" rev="'.$file.'" tabindex="'.$i.'" title="" class="'.$class.'" '.$target.'>';
		break;
		
		default:
		$url_start = '<a href="'.$album.'/'.$file.'" rel="'.$album.'" rev="'.$file.'" tabindex="'.$i.'" title="" class="'.$class.'" '.$target.'>';
		break;	
	}
	
	return $url_start;
	
}

function fixImageOrientation($filename) 
{  	
	//read EXIF header from file
	$exif = @exif_read_data($filename);
	 
	if($exif) 
	{ 
		//fix the Orientation if EXIF data exists
		if(!empty($exif['Orientation'])) 
		{
			switch($exif['Orientation']) 
			{
				case 3:
				$createdImage = imagerotate($filename,180,0);
				break;
				case 6:
				$createdImage = imagerotate($filename,-90,0);
				break;
				case 8:
				$createdImage = imagerotate($filename,90,0);
				break;
			}
		}
	}   
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
		   
		    $html .= '<a href="'.$url.'" class="'.$paginate_link_class.' prev" rel="'.$album.'|'.$prevPage.'|'.$targetid.'|'.$fullAlbum.'"><i class="material-icons">keyboard_arrow_left</i></a>';
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
		   $html .= '<a href="'.$url.'" class="'.$paginate_link_class.' '.$class.'" rel="'.$album.'|'.$p.'|'.$targetid.'|'.$fullAlbum.'">'.$p.'</a>';	  
	   }
	   
	   if ($currentPage != $numPages)
	   {
			$nextPage = $currentPage + 1;
		   
			unset($_GET['page']); // delete edit parameter;
			$_GET['page'] = $nextPage; // change page number
			$qs = http_build_query($_GET); // rebuild query string
			$url = $page_url.'?'.$qs;
		   	
			$html .= '<a href="'.$url.'" class="'.$paginate_link_class.' next" rel="'.$album.'|'.$nextPage.'|'.$targetid.'|'.$fullAlbum.'"><i class="material-icons">keyboard_arrow_right</i></a>';
	   }
	   else
	   {
		   $html .= '<span class="next inactive"><i class="material-icons">keyboard_arrow_right</i></span>';
	   }	  	 
	
	}
	
	return $html;

}
?>