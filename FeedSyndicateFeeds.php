<?php
/*
Plugin Name: FeedSyndicate Feeds
Plugin URI: http://www.feedsyndicate.com/software
Description: Plugin to import data from NewsML to wordpress database. Copyright FeedSyndicate
Version: 3.5
Author: FeedSyndicate
Author URI: http://www.feedsyndicate.com
*/

define( 'FeedSyndicateFeeds_PATH', dirname( __FILE__ ) );
define( 'FeedSyndicateFeeds_URL',  plugins_url( '', __FILE__ ) );

require FeedSyndicateFeeds_PATH . '/cron_functions.php';
require FeedSyndicateFeeds_PATH . '/classes/FeedSyndicateFeeds.class.php';
require FeedSyndicateFeeds_PATH . '/classes/FeedSyndicateFeedsTable.class.php';
require FeedSyndicateFeeds_PATH . '/classes/FeedSyndicateNewsML.class.php';

load_plugin_textdomain( 'FeedSyndicateFeeds', false, FeedSyndicateFeeds_URL );

if ( !class_exists( 'FeedSyndicateAJAX' ) )
	require 'classes/FeedSyndicateAJAX.class.php';

if ( !class_exists( 'FeedSyndicateAdmin' ) )
	require 'classes/FeedSyndicateAdmin.class.php';

new FeedSyndicateFeeds();

session_start();







