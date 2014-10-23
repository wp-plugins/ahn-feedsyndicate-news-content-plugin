<?php

add_action( 'FeedSyndicateCronUpdate', 'FeedSyndicateCronUpdate', 10, 1 );

function FeedSyndicateCronUpdate( $feed_hash ) {
	$ahn = new FeedSyndicateFeeds();
	$ahn->cron_update( $feed_hash );
	echo "done!";
}

