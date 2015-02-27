<?php

function FeedSyndicateExtraCronIntervals($schedules) {
    $schedules['ev5min'] = array(
        'interval' => 300,
        'display' => __('Every 5 minutes')
    );
    $schedules['ev15min'] = array(
        'interval' => 900,
        'display' => __('Every 15 minutes')
    );
    $schedules['ev20min'] = array(
        'interval' => 1200,
        'display' => __('Every 20 minutes')
    );
    $schedules['ev30min'] = array(
        'interval' => 1800,
        'display' => __('Every 30 minutes')
    );
    return $schedules;
}

add_filter('cron_schedules', 'FeedSyndicateExtraCronIntervals');

add_action('FeedSyndicateCronUpdate', 'FeedSyndicateCronUpdate', 10, 1);

function FeedSyndicateCronUpdate($feed_hash) {
    $ahn = new FeedSyndicateFeeds();
    $ahn->cron_update($feed_hash);
    echo "done!";
}
