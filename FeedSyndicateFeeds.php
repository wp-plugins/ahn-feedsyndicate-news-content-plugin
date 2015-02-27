<?php

/*
  Plugin Name: FeedSyndicate Feeds
  Plugin URI: http://www.feedsyndicate.com/software
  Description: Plugin to import data from NewsML to wordpress database. Copyright FeedSyndicate
  Version: 3.6.8
  Author: FeedSyndicate
  Author URI: http://www.feedsyndicate.com
 */

define('FeedSyndicateFeeds_PATH', dirname(__FILE__));
define('FeedSyndicateFeeds_URL', plugins_url('', __FILE__));

require FeedSyndicateFeeds_PATH . '/classes/FeedSyndicateFeeds.class.php';
require FeedSyndicateFeeds_PATH . '/classes/FeedSyndicateFeedsTable.class.php';
require FeedSyndicateFeeds_PATH . '/classes/FeedSyndicateNewsML.class.php';
require FeedSyndicateFeeds_PATH . '/classes/feed_syndicate_xml.php';
require FeedSyndicateFeeds_PATH . '/classes/feed_syndicate_cron.php';

load_plugin_textdomain('FeedSyndicateFeeds', false, FeedSyndicateFeeds_URL);

$cron_worker = new feed_syndicate_cron(array(
    "5_min_fs" => 5,
    "10_min_fs" => 10,
    "15_min_fs" => 15,
    "30_min_fs" => 30,
    "1_hr_fs" => 1,
    "4_hr_fs" => 4,
    "6_hr_fs" => 6,
    "12_hr_fs" => 12,
    "24_hr_fs" => 24));

$cron_worker->hooks();

if (!class_exists('FeedSyndicateAJAX')) {
    require 'classes/FeedSyndicateAJAX.class.php';
}

if (!class_exists('FeedSyndicateAdmin')) {
    require 'classes/FeedSyndicateAdmin.class.php';
}

new FeedSyndicateFeeds();

if (is_session_started() == FALSE) {
    session_start();
}

function is_session_started() {
    if (php_sapi_name() !== 'cli') {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}
