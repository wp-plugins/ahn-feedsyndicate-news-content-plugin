function removeFeed( feed_id ) {
	var data = {
		action:'FeedSyndicateRemoveFeed',
		feed  :feed_id,
		nonce :jQuery( "#FeedSyndicateNonce" ).val()
	};

	jQuery.post( ajaxurl, data, function ( response ) {
		jQuery( "#FeedSyndicateData" ).html( response );
	} );
}

function updateFeed( feed_id, shouldPublish ) {

	jQuery( "#FeedSyndicateLoader" ).dialog( {
		modal    :true,
		height   :100,
		resizable:false
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
			height   :100,
			resizable:false
		} );


	} );
}

jQuery( document ).ready( function () {


	jQuery( "#FeedSyndicateFeedsForm" ).submit( function () {

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
			jQuery( "#FeedSyndicateData" ).html( response );
			jQuery( "#form_feed_url" ).val( "" );
			jQuery( "#form_feed_title" ).val( "" );
		} );

		return false;
	} );


	jQuery( "#FeedSyndicateRemoveAll" ).click( function () {
		jQuery( "#delete-confirm" ).dialog( {
			resizable:false,
			height   :160,
			modal    :true,
			buttons  :{
				"Delete all feeds":function () {
					removeFeed( "all" );
					jQuery( this ).dialog( "close" );
				},
				Cancel            :function () {
					jQuery( this ).dialog( "close" );
				}
			}
		} );
	} );

	jQuery( "#FeedSyndicatePublishAll" ).click( function () {

		updateFeed( "all", true );

	} );

	jQuery( "#FeedSyndicateDraftAll" ).click( function () {

		updateFeed( "all", false );

	} );


} );