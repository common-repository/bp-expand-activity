jQuery(document).ready( function(j) {
	var bpExpandActivityItem;
	
	j("a.bp-expand-activity[href=#]").livequery('click',
		function() { 
			bpExpandActivityItem = j(this).closest('blockquote');
			j(this).text('Loading...');
			
			j.post( 
				'/wp-load.php', 
				{
					action: 'bp_expand_activity',
					'cookie': encodeURIComponent(document.cookie),
					'_wpnonce': j(this).attr('id').substr(19),
					'bp-expand-activity' : j(this).attr('rel')
				},
				function(response){	
					bpExpandActivityItem.html(response);
				}
			);
			
			return false;
		}
	);
});