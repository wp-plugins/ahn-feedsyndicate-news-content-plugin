function removeFeed( feed_id , from) {
	var data = {
		action:'FeedSyndicateRemoveFeed',
		feed  :feed_id,
		nonce :jQuery( "#FeedSyndicateNonce" ).val()
	};
	
	if(from != "all"){
        jQuery( "#FeedSyndicateFeedDelete" ).dialog({
			modal    :true,
			minHeight:150,
			height	 :"auto",
			resizable:false,
			closeText:"",
			buttons  : {
				'Delete' : function(){
					jQuery("#delete_loading").show();
					jQuery.post( ajaxurl, data, function ( response ) {
						jQuery( "#FeedSyndicateData" ).html( response );
						jQuery("#delete_loading").hide();
						jQuery( "#delete_feeds" ).remove();
						jQuery('<div id="delete_feeds" class="updated settings-error" ><p>Feed successfully deleted.</p></div>').insertBefore("#delete_loading");
						jQuery( "#delete_feeds" ).fadeOut(3000);
					} );
					jQuery(this).dialog('close');
				},
				'Cancel' : function(){
					jQuery(this).dialog('close');
				}
			}
        });
	} else {
		jQuery.post( ajaxurl, data, function ( response ) {
			jQuery( "#FeedSyndicateData" ).html( response );
			jQuery("#delete_loading").hide();
			jQuery( "#delete_feeds" ).remove();
			jQuery(this).dialog('close');
			jQuery('<div id="delete_feeds" class="updated settings-error" ><p>Feed successfully deleted.</p></div>').insertBefore("#delete_loading");
			jQuery( "#delete_feeds" ).fadeOut(3000);
		} );
	}
        
}

function updateFeed( feed_id, shouldPublish ) {

	jQuery( "#FeedSyndicateLoader" ).dialog( {
		modal    :true,
		minHeight:120,
		height 	 :"auto",
		resizable:false,
		closeText:""
	} );

	var data = {
		action :'FeedSyndicateUpdateFeed',
		feed   :feed_id,
		publish:shouldPublish,
		nonce  :jQuery( "#FeedSyndicateNonce" ).val()
	};

	jQuery.post( ajaxurl, data, function ( response ) {

		jQuery( "#FeedSyndicateNotices" ).html( response );

		jQuery( "#FeedSyndicateLoader" ).dialog( "close" );

		jQuery( "#FeedSyndicateNotices" ).dialog( {
			modal    :true,
			minHeight:120,
			height   :"auto",
			resizable:false,
			closeText:""
		} );
	} );
}

function editFeed(feed_id){

	var data = {
		action :'FeedSyndicateGetFeed',
		feed   :feed_id,
		nonce  :jQuery( "#FeedSyndicateNonce" ).val()
	};
	
	jQuery.post( ajaxurl, data, function ( response ) {
		jQuery( "#FeedSyndicateEditFeed" ).show();
		jQuery("#FeedSyndicateEditFeed").dialog({
			modal    :true,
			minHeight:200,
			minWidth :600,
			resizable:false,
			closeText:""
		} );
		var data = JSON.parse(response);
		jQuery("#edit_form_feed_url").val(data['feed_url']);
		jQuery("#edit_form_feed_title").val(data['feed_title']);
		jQuery("#edit_feed_array_index").val(data['index']);
		jQuery("#edit_form_cat option").each(function(){
			var cat = jQuery(this).attr('value');
			if(cat == data['cat']){
				jQuery(this).attr("selected","selected");
			}
		});
		jQuery("#edit_user option").each(function(){
			var user = jQuery(this).attr('value');
			if(user == data['user']){
				jQuery(this).attr("selected","selected");
			}
		});
		jQuery("input[name='edit_form_feed_cron']").each(function(){
			var cron = jQuery(this).val();
			if(cron == data['cron']){
				jQuery(this).attr('checked','checked');
			}
		});
	} );
	//form validation rules for edit feed form
	jQuery("#FeedSyndicateFeedsEditForm").validate({
		rules: {
			edit_form_feed_url: "required",
			edit_form_feed_title: "required"
		},
		messages: {
            edit_form_feed_url: " Required field",
			edit_form_feed_title: " Required field"
		},
		submitHandler: function(form) {	
			jQuery("#edit_submit_loading").show();
			var data = {
				feed	  :feed_id,
				action    :'FeedSyndicateEditFeed',
				feed_url  :jQuery( "#edit_form_feed_url" ).val(),
				feed_title:jQuery( "#edit_form_feed_title" ).val(),
				cat       :jQuery( "#edit_form_cat" ).val(),
				cron      :jQuery( 'input[name=edit_form_feed_cron]:checked', '#FeedSyndicateFeedsEditForm' ).val(),
				nonce     :jQuery( "#FeedSyndicateNonce" ).val(),
				user      :jQuery( "#edit_user" ).val(),
				index 	  :jQuery("#edit_feed_array_index").val()
			};

			jQuery.post( ajaxurl, data, function ( response ) {
				jQuery( "#FeedSyndicateData" ).html( response );
				jQuery("#edit_submit_loading").hide();
				jQuery("#FeedSyndicateEditFeed").dialog('close');
			} );
		}
	});
}

jQuery( document ).ready( function () {
	
	jQuery( "#FeedSyndicateEditFeed" ).hide();
	jQuery( "#Save_feeds" ).fadeOut(5000);
	
	//form validation rules for add new feed form
	jQuery("#FeedSyndicateFeedsForm").validate({
		rules: {
			form_feed_url: "required",
			form_feed_title: "required"
		},
		messages: {
            form_feed_url: " Required field",
			form_feed_title: " Required field"
		},
		submitHandler: function(form) {	
			jQuery("#submit_loading").show();
			var data = {
				action    :'FeedSyndicateNewFeed',
				feed_url  :jQuery( "#form_feed_url" ).val(),
				feed_title:jQuery( "#form_feed_title" ).val(),
				cat       :jQuery( "#form_cat" ).val(),
				cron      :jQuery( 'input[name=form_feed_cron]:checked', '#FeedSyndicateFeedsForm' ).val(),
				nonce     :jQuery( "#FeedSyndicateNonce" ).val(),
				user      :jQuery( "#user" ).val()
			};

			jQuery.post( ajaxurl, data, function ( response ) {
				jQuery( "#form_feed_url" ).val('');
				jQuery( "#form_feed_title" ).val('');
				jQuery( "#FeedSyndicateData" ).html( response );
				// location.reload();
				jQuery("#submit_loading").hide();
			} );
		}
	});


	jQuery( "body" ).on("click", "#FeedSyndicateRemoveAll", function () {
		jQuery( "#delete-confirm" ).dialog( {
			resizable:false,
			minHeight:150,
			height	 :"auto",
			modal    :true,
			closeText:"",
			buttons  :{
				"Delete all feeds":function () {
					removeFeed( "all" , "all" );
					jQuery( this ).dialog( "close" );
				},
				Cancel:function () {
					jQuery( this ).dialog( "close" );
				}
			}
		} );
	} );

	jQuery( "body" ).on("click", "#FeedSyndicatePublishAll", function () {

		updateFeed( "all", true );

	} );

	jQuery( "body" ).on("click", "#FeedSyndicateDraftAll", function () {

		updateFeed( "all", false );

	} );
        
       jQuery( "#tabs" ).tabs();
       
       //Auto publish cron set to 30 minutes
       setInterval(function(){
           var data = {
		action :'FeedSyndicateAutoUpdateCron',
		feed   :'all',
		publish:true
            };
            jQuery.post( ajaxurl, data);
       },1800000);
       
       window.onload = function(){
           jQuery( "#tabs-1" ).find(".error").hide();
           var wordpress_error = jQuery( "#tabs-1" ).find(".error").html();
           if(wordpress_error != undefined){
               var error_html = "<div class='error'>" + wordpress_error + "</div>";
               jQuery( error_html ).insertAfter(".update-nag");
               jQuery( ".error" ).css("margin-left" , "2px !important");
           }
       };
} );