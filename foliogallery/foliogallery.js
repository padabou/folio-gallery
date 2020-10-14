var folioGalleryDir = './foliogallery'; // foliogallery folder relative path - absolute path like http://my_website.com/foliogallery may not work

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
			url: folioGalleryDir+'/foliogallery-img.php',
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
			image,
			root;
		
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
			var showAlb = $(this).prop('title');
			loadGallery(folioGalleryDir,targetId,showAlb,fullAlbum,1);
			return false;
		});
								
		// refresh div content
		$(this).on('click', 'a.fgrefresh', function() {
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
								
		album = $(this).data('album'); // only name/dir of album
		image = $(this).data('file');
		root = $(this).data('root');
		albumpage = document.location.href;

		$.ajax
		({
			type: 'POST',
			url: folioGalleryDir+'/foliogallery-img.php',
			data: {
				albumpage: albumpage,
				alb: album,
				img: image,
				root: root
			},
			cache: false,
			success: function(dat)
			{
				setTimeout(function() {
					$('#fgOverlay').html(dat).show();
					$('.fgmainspinner').hide();
				}, 300);
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				alert("Error: jqXHR " + JSON.stringify(jqXHR) + " - textStatus " + JSON.stringify(textStatus) + " - errorThrown " + JSON.stringify(errorThrown));				
				console.log($(jqXHR.responseText));
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
	
	// show/hide info pane in image overlay
	$(this).on('click', '.icon-drawer', function(e) {	
		e.preventDefault();
				
		$('#leftCol').removeClass('leftColFW');
		
		// save status of infobox in localStorage
		if($('#infoBox').is(':hidden'))
		{
			localStorage.setItem('infobox', 0);
			$('#leftCol').removeClass('leftColToggle');
			$('#infoBox').removeClass('infoBoxToggle');
			
		}
		else
		{
			localStorage.setItem('infobox', 1);
			$('#leftCol').addClass('leftColToggle');
			$('#infoBox').addClass('infoBoxToggle');
		}
				
	});
			
	// tooltip
	$(this).on('click', '.tooltip', function(e) {	
		e.preventDefault();
		var tooltipId = 'tooltipDiv',
			tooltipData = $(this).data('text'),
			content = "<div id='"+ tooltipId +"'><span id='tooltipClose'></span><span class='label-txt'>Share URL:</span><br><textarea>"+ tooltipData +"</textarea>URL copied to clipboard</div>";

		if (content != ''){		
			$('body').prepend(content);								
			$('#'+tooltipId).show();

			$('#'+tooltipId+' textarea').select();
			document.execCommand('copy');
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
		
		var paginateVars = $(this).data('vars'),
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
		},
		error: function(jqXHR, textStatus, errorThrown)
		{
			alert("Error: jqXHR " + JSON.stringify(jqXHR) + " - textStatus " + JSON.stringify(textStatus) + " - errorThrown " + JSON.stringify(errorThrown));				
			console.log($(jqXHR.responseText));
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
