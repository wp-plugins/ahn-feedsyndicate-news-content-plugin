<?php

/**
 *
 */
class FeedSyndicateAJAX {

	/**
	 * @var FeedSyndicateFeeds
	 */
	private $feed_handler;

	/**
	 * @var FeedSyndicateAdmin
	 */
	private $admin_handler;

	/**
	 * @param $feed_handler  FeedSyndicateFeeds
	 * @param $admin_handler FeedSyndicateAdmin
	 */
	public function __construct( $feed_handler, $admin_handler ) {
		add_action( 'wp_ajax_FeedSyndicateNewFeed',    array( $this, 'new_feed_handler'    ) );
		add_action( 'wp_ajax_FeedSyndicateRemoveFeed', array( $this, 'remove_feed_handler' ) );
		add_action( 'wp_ajax_FeedSyndicateUpdateFeed', array( $this, 'update_feed_handler' ) );
		add_action( 'wp_ajax_FeedSyndicateGetFeed', array( $this, 'get_specific_feed_for_edit' ) );
		add_action( 'wp_ajax_FeedSyndicateEditFeed', array( $this, 'Edit_feed_handler' ) );

		$this->feed_handler  = $feed_handler;
		$this->admin_handler = $admin_handler;
	}

	public function update_feed_handler() {

		if ( !isset( $_POST["nonce"] ) || !wp_verify_nonce( $_POST["nonce"], 'FeedSyndicateNewFeed' ) )
			die( "Security error!" );

		if ( empty( $_POST["feed"] ) )
			die( "Bad data" );

		$status = "pending";
		if ( !empty( $_POST["publish"] ) && $_POST["publish"] === "true" )
			$status = "publish";

		if ( $_POST["feed"] === "all" ) {
			$feeds = get_option( "FeedSyndicateFeeds" );
		} else {
			$feeds = array( $this->feed_handler->get_feed_from_hash( $_POST["feed"] ) );
		}

		$inserted = 0;
		if(!empty($feeds)){
			foreach ( $feeds as $feed ){
				$inserted = $this->feed_handler->do_the_import( $feed, $status );
			}
		}
		echo sprintf( __( "%d posts were inserted", "FeedSyndicateFeeds" ), $inserted );

		die();
	}

	public function remove_feed_handler() {

		if ( !isset( $_POST["nonce"] ) || !wp_verify_nonce( $_POST["nonce"], 'FeedSyndicateNewFeed' ) )
			die( "Security error!" );

		if ( empty( $_POST["feed"] ) )
			die( "Bad data" );

		if ( $_POST["feed"] === "all" ) {
			$this->feed_handler->remove_all_feeds();
		} else {
			$options = get_option( "FeedSyndicateFeeds" );
			$cont    = 0;
			foreach ( $options as $option ) {
				if ( $option["hash"] === $_POST["feed"] ) {
					$next = wp_next_scheduled( "FeedSyndicateCronUpdate", array( $option["hash"] ) );
					if ( $next )
						wp_unschedule_event( $next, 'FeedSyndicateCronUpdate', array( $option["hash"] ) );

					unset( $options[$cont] );
					$options = array_values( $options ); //regenerate indexes
					update_option( "FeedSyndicateFeeds", $options );
					break;
				}
				$cont++;
			}
		}

		$this->admin_handler->show_table();

		die();
	}

	public function new_feed_handler() {

		if ( !isset( $_POST["nonce"] ) || !wp_verify_nonce( $_POST["nonce"], 'FeedSyndicateNewFeed' ) )
			die( "Security error!" );

		$feeds = get_option( "FeedSyndicateFeeds" );
		if ( !$feeds )
			$feeds = array();

		$new_feed = array();

		$new_feed["feed_url"]   = isset( $_POST["feed_url"] ) ? $_POST["feed_url"] : "";
		$new_feed["feed_title"] = isset( $_POST["feed_title"] ) ? $_POST["feed_title"] : "";
		$new_feed["cat"]        = isset( $_POST["cat"] ) ? $_POST["cat"] : "";
		$new_feed["hash"]       = md5( $new_feed["feed_url"] );
		$new_feed["cron"]       = isset( $_POST["cron"] ) ? $_POST["cron"] : "never";
		$new_feed["user"]       = isset( $_POST["user"] ) ? $_POST["user"] : null;

		$feeds[] = $new_feed;

		update_option( "FeedSyndicateFeeds", $feeds );

		if ( $new_feed["cron"] != "never" && !wp_next_scheduled( 'FeedSyndicateCronUpdate', $new_feed["hash"] ) )
			wp_schedule_event( time(), $new_feed["cron"], 'FeedSyndicateCronUpdate', array( $new_feed["hash"] ) );

		$this->admin_handler->show_table();
		
		$_SESSION['add_new_feed'] = true;
		
		echo "<script>window.location='" . site_url() . "/wp-admin/admin.php?page=FeedSyndicateFeeds'; </script>";

		die();

	}
	
	public function get_specific_feed_for_edit(){
		$hash = $_POST['feed'];
		$feeds = get_option( "FeedSyndicateFeeds" );
		$i = 0;
		foreach($feeds as $feed){
			if($hash == $feed['hash']){
				$feed['index'] = $i;
				echo json_encode($feed);
				die();
			}
			$i++;
		}
	}
	
	public function Edit_feed_handler() {

		if ( !isset( $_POST["nonce"] ) || !wp_verify_nonce( $_POST["nonce"], 'FeedSyndicateNewFeed' ) )
			die( "Security error!" );
		
		$index = $_POST['index'];	
		
		$feeds = get_option( "FeedSyndicateFeeds" );
		
		foreach($feeds as $key => $feed){
			if($index == $key){
				$feeds[$key]['feed_url'] 	= $_POST["feed_url"];
				$feeds[$key]['feed_title']  = $_POST["feed_title"];
				$feeds[$key]['cat'] 		= $_POST["cat"];
				$feeds[$key]['hash'] 		= $_POST["feed"];
				$feeds[$key]['cron'] 		= $_POST["cron"];
				$feeds[$key]['user'] 		= $_POST["user"];
			}
		}

		update_option( "FeedSyndicateFeeds", $feeds );

		if ( $_POST["cron"] != "never" && !wp_next_scheduled( 'FeedSyndicateCronUpdate', $_POST["feed"] ) )
			wp_schedule_event( time(), $_POST["cron"], 'FeedSyndicateCronUpdate', array( $_POST["feed"] ) );

		$this->admin_handler->show_table();
		
		$_SESSION['edit_feed'] = true;
		$_SESSION['edit_feed_redirect'] = true;
		
		echo "<script>window.location.href='" . site_url() . "/wp-admin/admin.php?page=FeedSyndicateFeeds'; </script>";

		die();

	}
}
