<?php
// process shortcodes
function foliogallery_shortcode($content) {
		
	$foliogallery_search = '[foliogallery';
		
	preg_match_all("/\[([^\]]*)\]/", $content, $matches); // text between brackets
		
	// get text between brackets and replace it with a div container to display the gallery
	foreach($matches[1] as $shortcode)
	{
		if (strpos($content, $foliogallery_search) !== false) 
		{
			$f = explode('=', $shortcode);
			if (isset($f[1])) 
			{
				$foliogallery_album = $f[1];
																
				$f_pos = strpos($content, $foliogallery_search) + strlen($foliogallery_search); // find position of shortcode
				$f_length = strpos($content, '"', $f_pos) - $f_pos; // find ending " and subtract $pos to find shortcode length
				$virtual_id = str_replace(' ', '', $foliogallery_album);
				
				$content = str_replace('[foliogallery='.$foliogallery_album.']', '<div class="folioGallery" id="folioGallery'.$virtual_id.'" title="'.$foliogallery_album.'"></div>', $content);	
			}
			else
			{
				$content = str_replace('[foliogallery]', '<div class="folioGallery" id="folioGallery"></div>', $content);
			}
		}
	}
		
	return $content;
}
?>
