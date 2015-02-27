<?php

/**
 * Primary cron class
 * @author Alexander K
 */
class feed_syndicate_cron {

    /**
     * @var mixed Feeds (WP options)
     */
    private $feeds;

    /**
     * @var array To be add cron intervals
     */
    private $intervals;

    /**
     * Creates class properties and wordpress hooks / filters
     * @param array $intervals (optional) To be add cron intervals
     * @return void
     */
    public function __construct($intervals = array()) {
        $this->intervals = $intervals;
    }

    /**
     * Registers WP hooks and filters
     * @return void
     */
    public function hooks() {
        register_activation_hook(__FILE__, array($this, "schedule")); // activation hook
        register_deactivation_hook(__FILE__, array($this, "unschedule")); // deactivation hook
        add_action("cron_update", array($this, "update"), 10, 2); // cron hook
        add_filter("cron_schedules", array($this, "schedules")); // new cron schedule hook
    }

    /**
     * Function wrapper for scheduling cron
     * @param array $feeds (optional) Array of cron interval and feed hash
     * @return void
     */
    public function schedule($feeds = "") {
        $this->get_feeds($feeds);
        $this->schedule_hook();
    }

    /**
     * Creates cron schedules
     * @return void
     */
    private function schedule_hook() {
        foreach ($this->feeds as $feed) {
            if ($feed["publish"] != "no" && !wp_next_scheduled("cron_update", array($feed["hash"], $feed["publish"]))) {
                wp_schedule_event(time(), $feed["cron"], "cron_update", array($feed["hash"], $feed["publish"]));
            }
        }
    }

    /**
     * Function wrapper for descheduling cron
     * @param string|array $feeds (optional) Array of cron interval and feed hash
     * @return void
     */
    public function unschedule($feeds = "") {
        $this->get_feeds($feeds);
        $this->unschedule_hook();
    }

    /**
     * Removes cron schedules
     * @return void
     */
    private function unschedule_hook() {
        foreach ($this->feeds as $feed) {
            if ($feed["publish"] != "no") {
                $next = wp_next_scheduled("cron_update", array($feed["hash"], $feed["publish"]));
                ($next ? wp_unschedule_event($next, "cron_update", array($feed["hash"], $feed["publish"])) : NULL);
            }
        }
    }

    /**
     * Gets feed options (WP options)
     * @param string|array $feeds (optional) Specific feed(s) to get
     * @return void
     */
    private function get_feeds($feeds = "") {
        if (!empty($feeds)) {
            $this->feeds = $feeds;
        } else {
            $this->feeds = get_option("FeedSyndicateFeeds");
        }
    }

    /**
     * Function wrapper for schedules function
     * @return array WP schedules
     */
    public function schedules($schedules) {
        if (empty($this->intervals)) {
            return $schedules;
        }
        return $this->schedules_hook($schedules);
    }

    /**
     * Adds new cron intervals
     * @return array Cron schedules
     */
    private function schedules_hook($schedules) {
        if (count($this->intervals) === count($this->intervals, TRUE)) { // checks if multidimensional array
            $this->schedules_helper();
        }
        foreach ($this->intervals as $int_key => $int_value) {
            $schedules[$int_key] = array(
                "interval" => $int_value["interval"],
                "display" => __($int_value["display"])
            );
        }
        return $schedules;
    }

    /**
     * Prepares associate array for function that adds new cron intervals
     * @return void
     */
    private function schedules_helper() {
        foreach ($this->intervals as $int_key => $int_value) {
            $broken = explode("_", $int_key);
            if ($broken[2] != "fs") { // checks if interval is feed syndicate
                continue;
            }
            switch ($broken[1]) {
                case "hr":
                    $this->intervals[$int_key] = array(
                        "interval" => $int_value * 3600,
                        "display" => "Every " . $int_value . " Hours"
                    );
                    break;
                case "min":
                    $this->intervals[$int_key] = array(
                        "interval" => $broken[0] * 60,
                        "display" => "Every " . $int_value . " minutes"
                    );
                    break;
            }
        }
    }

    /**
     * Wrapper for cron update function
     * @param string $feed_hash URL of the feed (MD5)
     * @return void
     */
    public function update($feed_hash, $status) {
        $this->update_hook($feed_hash, $status);
    }

    /**
     * Updates feeds
     * @param string $feed_hash URL of the feed (MD5)
     * @return void
     */
    private function update_hook($feed_hash, $status) {
        $ahn = new FeedSyndicateFeeds();
        $ahn->cron_update($feed_hash, $status);
    }

}
