jQuery(document).ready(function(){
	jQuery('table#n0tice-table tbody').sortable();
	jQuery('table#n0tice-table tbody').disableSelection();
	var n = 0;
	//This populates the search field when "New source" is clicked
	var $i = 0;
	jQuery('#new-source').click(function(){
		var searchTop = '<div id="#source-' + $i + '" style="border: 1px solid black; margin: 10px 0px; padding: 0px 5px 5px 5px;">' +
							'<h3>Search for n0tices:</h3>';
							
		var criteria =		'<div class="criterion">' +
								'<select class="search-criteria">' +
									'<option value="search">Headline/location search</option>'+
									'<option value="user">User</option>'+
									'<option value="n0ticeboard" selected>Noticeboard</option>'+
									'<option value="type">Type</option>' +
									'<option value="latlng">Latitude/Longitude</option>' +
									'<option value="location">Location</option>' +
								'</select>' +
								'<input type="text" class="n0ticeboard-input source-n0ticeboard" />' +
							'</div>';
							

		var searchBottom =	'<input type="submit" class="button-secondary add-another-criterion" onclick="return false" value="Add another criterion" style="margin: 10px 0px;" /><br />' +
							'<strong>Return # latest n0tices:</strong>' + 
							'<input type="text" class="num-n0tices" size="3" value="10" /> ' +
							'<input type="submit" class="fetch button-primary" style="float: right;" value="Search" onclick="return false" />' +
							'</div>';
		
		jQuery('div#source-container').append(searchTop + criteria + searchBottom);
		$i++;

		jQuery(document).on('click', 'input.add-another-criterion', function(event){
			jQuery(this).prev().after(criteria);
		});		
		
	});
	

	//This switches the fields around when the criterion type dropdown changes.
	jQuery(document).on('change', 'select.search-criteria', function(event){
		var fields = '';
		switch (this.value)
		{
			case 'search':
				jQuery(this).siblings().remove();
				fields = '<input type="text" class="n0ticeboard-input source-search" />';
			break;
			
			case 'user':
				jQuery(this).siblings().remove();			
				fields = '<input type="text" class="n0ticeboard-input source-user" />';				
			break;
			
			case 'n0ticeboard':
				jQuery(this).siblings().remove();			
				fields = '<input type="text" class="n0ticeboard-input source-n0ticeboard" />';				
			break;
			
			case 'type':
				jQuery(this).siblings().remove();			
				fields = '<input type="text" class="n0ticeboard-input source-type" />';				
			break;
			
			case 'latlng':
				jQuery(this).siblings().remove();			
				fields = '<span>Latitude: </span><input type="text" class="n0ticeboard-input source-lat" />' +
						 '<span>Longitude: </span><input type="text" class="n0ticeboard-input source-lng" />' +
						 '<span>Radius (optional): </span><input type="text" class="n0ticeboard-input source-radius" />';
			break;
			
			case 'location':
				jQuery(this).siblings().remove();		
				fields = '<input type="text" class="n0ticeboard-input source-location" />' +
						 '<span>Radius (optional): </span><input type="text" class="n0ticeboard-input source-radius" />';				
			break;
		}
		jQuery(this).parent().append(fields);
		
		
	});
	
	//Finally, this assigns all the data in fields to variables and sends it to wp_ajax when "submit" is clicked.
	
	jQuery(document).on('click', 'input.fetch', function(event){
		//assign values from fields. NOTE: if multiple same criteria present, last one is accepted. TODO: fix that.
		var search = jQuery(this).prevAll('div.criterion').children('input.source-search').val();
		var user = jQuery(this).prevAll('div.criterion').children('input.source-user').val();
		var n0ticeboard = jQuery(this).prevAll('div.criterion').children('input.source-n0ticeboard').val();
		var type = jQuery(this).prevAll('div.criterion').children('input.source-type').val();						
		var lat = jQuery(this).prevAll('div.criterion').children('input.source-lat').val();		
		var lng = jQuery(this).prevAll('div.criterion').children('input.source-lng').val();
		var location = jQuery(this).prevAll('div.criterion').children('input.source-location').val();		
		var radius = jQuery(this).prevAll('div.criterion').children('input.source-radius').val();
		var amount = jQuery(this).siblings('input.num-n0tices').val();
		var data = {				
				action : 'get-n0tices',
				search : search,
				user : user,
				n0ticeboard : n0ticeboard,
				type: type,
				lat: lat,
				lng: lng,
				location: location,
				radius: radius,
				amount: amount
			};
		jQuery(this).parent().fadeOut('slow', function(){
			jQuery(this).remove();
		});
		console.log(data);
		jQuery.post(
			ajaxurl, data, function ( response ) { // this writes the response as table rows.
				var obj = jQuery.parseJSON(response);
//				console.log(obj);
				jQuery.each(obj, function(i, notice){
//					console.log(notice);
					var tableRow = '<tr>' +
								   '<td class="middle"><span class="move-icon"></span></td>' +
								   '<td class="middle select"><input class="item-enabled" type="checkbox" name="n0tice[' + n + '][enabled]"></td>' +
								   '<td class="middle" style="text-align: left;"><a class="headline" href="' + notice.webUrl + '">' + notice.headline + '</a>' +
								   		'&nbsp;&nbsp;<a href="#" onclick="return false" class="edit-headline" style="font-size: small">[edit]</a>' +
								   		'<input class="headline-hidden" name="n0tice[' + n + '][headline]" type="hidden" value="' + notice.headline + '" /></td>' + 
								   '<td class="middle">' + notice.type + 
								   '<input type="hidden" name="n0tice[' + n + '][type]" value="' + notice.type + '"/></td>' +
								   '<td class="middle">' + notice.noticeboard + 
								   '<input type="hidden" name="n0tice[' + n + '][noticeboard]" value="' + notice.noticeboard + '" /></td>' +
								   '<td class="middle">' + notice.created + 
								   '<input type="hidden" name="n0tice[' + n + '][created]" value="' + notice.created + '" /></td>'+
								   '<td><a href="' + notice.webUrl + '">' + notice.webUrl + '</a>'+
								   '<input type="hidden" name="n0tice[' + n + '][url]" value="' + notice.webUrl + '" /></td>' +
								   '</tr>';
								   n++;
//					console.log(tableRow);
					jQuery(tableRow).appendTo('#n0tice-table tbody');
				});
			}
		);
	});	

	//"edit" headline buttons functionality
	jQuery(document).on('click', 'a.edit-headline', function(event){
		var $headline = jQuery(this).prev('a.headline');
		var url = $headline.attr('href');
		var headlineText = $headline.text();
		jQuery(this).prev().replaceWith('<input type="text" size="35" class="edit-headline-input" value="' + headlineText + '"/>&nbsp;&nbsp;<a href="#" onclick="return false" class="save-headline" style="font-size: small">[save]</a>');
		jQuery(this).remove();
		jQuery('a.save-headline').click(function(event){
			var newHeadline = jQuery(this).prev('input.edit-headline-input').val();		
			jQuery(this).prev('input.edit-headline-input').replaceWith('<a class="headline" href="'+ url +'">'+newHeadline+'</a>&nbsp;&nbsp;<a href="#" onclick="return false" class="edit-headline" style="font-size: small">[edit]</a>');
			jQuery(this).next('input.headline-hidden').val(newHeadline);
			jQuery(this).remove();
		});		
	});


	jQuery('form#n0tice-curation').submit( function(event){
		if (jQuery('input#curation-name').val() === '') { //if name is empty, prevent submission.
			return false;
		}
		else { //else set order variables and rename variables to prevent namespace collision bug for multiple searches.
			var i = 0;
			jQuery('#n0tice-table tbody tr').each( function(){ //for each row...
				jQuery(this).children('td').children('input').each( function(index){ //for each input in each row...
					var oldName = jQuery(this).attr('name');
					var newName = oldName.replace(/\[[0-9]\]/, '[' + i + ']'); //change name to n0tice[i][thing]
					//alert('Old: ' + oldName + '; New: ' + newName);
					jQuery(this).attr('name', newName);
					if (jQuery(this).attr('name').search('order') > 0) { //set the order variable based on position in stack.
						jQuery(this).val(i);
					}
				});
			i++;
		});
		}
	});
	
});


function toggleChecked(status){
	jQuery('input.item-enabled').each(function(){
		jQuery(this).attr("checked", status);
	});
}
