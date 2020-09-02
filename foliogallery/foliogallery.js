/*!
	folioGallery v4.1 - 2019-05-27
	(c) 2019 Harry Ghazanian - foliopages.com/php-jquery-ajax-photo-gallery-no-database
	This content is released under the http://www.opensource.org/licenses/mit-license.php MIT License.
*/

var folioGalleryDir = './'; // foliogallery folder relative path - absolute path like http://my_website.com/foliogallery may not work

$(function() {					   
			
	// get parameters for share url
	var sharealb = getUrlParameter('alb'),
		shareimg = getUrlParameter('img');	
	
	// load album in overlay if above parameters exist
	if(sharealb && shareimg)
	{	
		var albumpage = document.location.href;
						
		$('#mainImage').html('');
		
		$.ajax
		({
			type: 'POST',
			url: 'foliogallery-img.php',
			data: {
				albumpage: albumpage,
				alb: sharealb,
				img: shareimg
			},
			cache: false,
			success: function(dat)
			{
				setTimeout(function() {
					$('#fgOverlay').html(dat).show();
				}, 300);
			}
		});	
		
		$('#fgOverlay').fadeIn('fast');
	}
	
	// find divs with class folioGallery and load album in it based on id
	$('.folioGallery').each(function(index, value) {								   
		
		var targetId,
			targetDiv,
			block,
			slider,
			blockWidth,
			sliderWidth,
			thumbWidth,
			pos,
			dir,
			fullAlbum,
			showAlb,
			album,
			albumpage,
			thumb,
			image;
		
		this == value; //true
		targetId = this.id; // id of div to load albums
		targetDiv = '#'+this.id;		
																
		if(targetId=='folioGallery') {
			fullAlbum = 1;
			showAlb = ''; // empty will show full gallery
		} else {
			fullAlbum = 0;
			showAlb = $(targetDiv).prop('title'); // title attribute of div - same as album folder
		}
		
		loadGallery(folioGalleryDir,targetId,showAlb,fullAlbum,1); // inital load
		
		// in gallery view, load album when thumb is clicked
		$(this).on('click', 'a.showAlb', function() {	
			var showAlb = $(this).prop('rel');
			loadGallery(folioGalleryDir,targetId,showAlb,fullAlbum,1);
			return false;
		});
								
		// refresh div content
		$(this).on('click', 'a.refresh', function() {
		   loadGallery(folioGalleryDir,targetId,'',fullAlbum,1);
		   return false;
		});
		
		// next prev links	
		$(this).on('click', '.fgright, .fgleft', function(e) {
							
			e.preventDefault();
			
			block       = $(targetDiv+' .fgthumbwrap');
			slider      = $(targetDiv+' .fgthumbwrap-inner');
			thumb       = $(targetDiv+' .fgthumb');
			sliderWidth = thumb.length * thumb.outerWidth(true); // number of thumbs times thumb width
			slider.width(sliderWidth); // set width of .thumbwrap-inner
			blockWidth  = block.width();					
			
			if(this.className == 'fgright') {	
				block.stop().animate({scrollLeft: '+=' + blockWidth}, 'fast');
			}
			if(this.className == 'fgleft') {	
				block.stop().animate({scrollLeft: '-=' + blockWidth}, 'fast');
			}
																				
			var scrollLeftPrev = 0,
				newScrollLeft;
			block.scroll(function() {
							
				sliderWidth = block.get(0).scrollWidth;
				newScrollLeft = block.scrollLeft();
								
				if(sliderWidth - newScrollLeft == blockWidth) { $(targetDiv+' .fgright').hide(); }
				if(newScrollLeft === 0) { $(targetDiv+' .fgleft').hide(); }
				
				scrollLeftPrev = newScrollLeft;
												
			});
												
			if(this.className == 'fgleft'){ $(targetDiv+' .fgright').show(); }
			if(this.className == 'fgright'){ $(targetDiv+' .fgleft').show(); }
																			
			return false;
			
		});
			
	});
			
	// load image in overlay - left right or thumbnail click	
	$(this).on('click', '.showimage', function() {
		
		$('#mainImage').html('');		
		$('#tooltipDiv').hide(); // close tooltip when arrows clicked
		$('.fgmainspinner').show();
								
		album = $(this).prop('rel');
		image = $(this).prop('rev');
		albumpage = document.location.href;
		
		$.ajax
		({
			type: 'POST',
			url: 'foliogallery-img.php',
			data: {
				albumpage: albumpage,
				alb: album,
				img: image
			},
			cache: false,
			success: function(dat)
			{
				setTimeout(function() {
					$('#fgOverlay').html(dat).show();
					$('.fgmainspinner').hide();
				}, 300);
			}
		});
		
		$('#fgOverlay').fadeIn('fast');
				
		return false;
	
	});
	
	// image overlay close
	$(this).on('click', '#fgOverlay-close', function() {
		$("#fgOverlay").fadeOut('fast').html('');
		$('body').css('overflow', 'auto');
		$('#tooltipDiv').hide();
		return false;
	});
			
	// tooltip
	$(this).on('click', '.tooltip', function(e) {	
		e.preventDefault();
		var tooltipId = 'tooltipDiv',
			rev = $(this).attr('rev'),
			content = "<div id='"+ tooltipId +"'><span id='tooltipClose'></span><span class='label-txt'>Share URL:</span><br> "+ rev +"</div>";
				
		if (content != ''){		
			$('body').prepend(content);								
			$('#'+tooltipId).show();	
		}
	});	
	// tooltip close	
	$(this).on('click', '#tooltipClose', function(e) {
		e.preventDefault();
		if($('#tooltipDiv').css('display') == 'block') {
			$('#tooltipDiv').hide();
		}
	});
	
	// pagination	
	$(this).on('click', '.pag', function(e) {
		
		e.preventDefault();
		
		var paginateVars = $(this).prop('rel'),
			album = paginateVars.split('|')[0],
			page =  paginateVars.split('|')[1],
			targetId = paginateVars.split('|')[2],
			fullalbum = paginateVars.split('|')[3];
		
		loadGallery(folioGalleryDir,targetId,album,fullalbum,page);
		
		return false;
	
	});
			
});

function loadGallery(folioGalleryDir,targetId,album,fullalbum,page) {                    
	$.ajax
	({
		type: 'POST',
		url: folioGalleryDir+'/foliogallery.php',
		data: {
			album: album,
			fullalbum: fullalbum,
			targetid: targetId,
			page: page
		},
		cache: false,
		success: function(msg)
		{
			$('#'+targetId).html(msg).hide().show();
		}
	});
	return false;
}

function getUrlParameter(variable) {
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	for (var i=0;i<vars.length;i++) {
		var pair = vars[i].split("=");
		if(pair[0] == variable)
		{
			// must remove the + from url parameters
			return decodeURIComponent((pair[1] + '').replace(/\+/g, '%20'));
		}
	}
	return false;
}


function removeUrlParam(param){
    
	if(window.location.href.indexOf(param) > -1){
        url = getUrlVars()[param];
    }
    return url;
}
