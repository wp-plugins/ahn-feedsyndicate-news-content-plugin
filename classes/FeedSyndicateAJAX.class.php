<?php

/**
 *
 */
class FeedSyndicateAJAX {

    /**
     * @var object FeedSyndicateFeeds
     */
    private $feed_handler;

    /**
     * @var object FeedSyndicateAdmin
     */
    private $admin_handler;

    /**
     * @var array Feed(s)
     */
    private $feed;

    /**
     * Creates class properties and wordpress hooks / filters
     * @param object $feed_handler  FeedSyndicateFeeds
     * @param object $admin_handler FeedSyndicateAdmin
     * @return void
     */
    public function __construct($feed_handler, $admin_handler) {
        add_action("wp_ajax_feed_syndicate", array($this, "ajax_controller"));

        $this->feed_handler = $feed_handler;
        $this->admin_handler = $admin_handler;
    }

    /**
     * Ajax controller for functions (a-la MVP style)
     * @return void
     */
    public function ajax_controller() {
        $this->feed = filter_input(INPUT_POST, "feed");
        $this->security();

        /*
         * $_POST["type"] contains the name of the function to execute
         * Provided by JS during AJAX session
         * By supplying () to string, string looks for function with the same name and executes it
         */
        $type = filter_input(INPUT_POST, "type");
        $this->$type();
    }

    /**
     *
     */
    private function security() {
        switch (filter_input(INPUT_POST, "type")) {
            case "new_feed_handler":
                $this->verify_nonce();
                break;
            case "remove_feed_handler":
                $this->verify_nonce();
                $this->verify_data();
                break;
            case "update_feed_handler":
                $this->verify_nonce();
                break;
            case "get_specific_feed_for_edit":
                break;
            case "Edit_feed_handler":
                $this->verify_nonce();
                break;
            case "auto_update_feed_handler":
                $this->verify_data();
                break;
        }
    }

    /**
     * Verifies nonce
     * @return void
     */
    private function verify_nonce() {
        if (!(filter_input(INPUT_POST, "nonce")) || !wp_verify_nonce(filter_input(INPUT_POST, "nonce"), 'FeedSyndicateNewFeed')) {
            wp_die("Security error!");
        }
    }

    /**
     * Verifies that $feed is not empty
     * @return void
     */
    private function verify_data() {
        if (empty($this->feed)) {
            wp_die("Bad data");
        }
    }

    /**
     * Gets feed parameters from POST
     * @return array Associative array with feed parameters
     */
    private function create_feed() {
        $return["feed_title"] = (filter_input(INPUT_POST, "feed_title") ? : "");
        $return["feed_url"] = (filter_input(INPUT_POST, "feed_url") ? : "");
        $return["cat"] = (filter_input(INPUT_POST, "cat") ? : "");
        $return["hash"] = (md5(filter_input(INPUT_POST, "feed_url"))? : "");
        $return["cron"] = (filter_input(INPUT_POST, "cron") ? : "");
        $return["user"] = (filter_input(INPUT_POST, "user") ? : "");
        $return["publish"] = (filter_input(INPUT_POST, "publish") ? : "");
        return $return;
    }

    /**
     * Updates feed
     * @return void
     */
    public function update_feed_handler() {
        if ($this->feed === "all") {
            $feeds = get_option("FeedSyndicateFeeds");
        } else {
            $feeds = array($this->feed_handler->get_feed_from_hash(filter_input(INPUT_POST, "feed")));
        }

        $inserted = 0;
        if (!empty($feeds)) {
            foreach ($feeds as $feed) {
                $inserted += $this->feed_handler->do_the_import($feed, filter_input(INPUT_POST, "publish"));
            }
        }
        echo sprintf(__("%d posts were inserted", "FeedSyndicateFeeds"), $inserted);
        wp_die(); // AJAX
    }

    /**
     * Removes feed
     * @return void
     */
    public function remove_feed_handler() {
        if ($this->feed === "all") {
            $this->feed_handler->remove_all_feeds();
        } else {
            $cron_worker = new feed_syndicate_cron();
            $feeds = get_option("FeedSyndicateFeeds");
            $cont = 0;
            foreach ($feeds as $feed) {
                if ($feed["hash"] === filter_input(INPUT_POST, "feed")) {
                    $cron_worker->unschedule(array($feed));
                    unset($feeds[$cont]);
                    $feeds = array_values($feeds); //regenerate indexes
                    update_option("FeedSyndicateFeeds", $feeds);
                    break;
                }
                $cont++;
            }
        }

        $this->admin_handler->show_table();

        wp_die(); // AJAX
    }

    /**
     * Creates new feed handle
     * @return void
     */
    public function new_feed_handler() {

        $feeds = (get_option("FeedSyndicateFeeds") ? : array());
        $feeds[] = $this->create_feed();

        update_option("FeedSyndicateFeeds", $feeds);

        $cron_worker = new feed_syndicate_cron();
        $cron_worker->schedule(array($feeds[count($feeds) - 1])); // [count($feeds) - 1] this passes the last sub array (which is new entry)

        $this->admin_handler->show_table();

        $_SESSION['add_new_feed'] = true;

        echo "<script>window.location='" . site_url() . "/wp-admin/admin.php?page=FeedSyndicateFeeds'; </script>";

        wp_die(); // AJAX
    }

    /**
     * Get specific feed handle
     * @return void
     */
    public function get_specific_feed_for_edit() {
        $hash = $this->feed;
        $feeds = get_option("FeedSyndicateFeeds");
        $i = 0;
        foreach ($feeds as $feed) {
            if ($hash == $feed['hash']) {
                $feed['index'] = $i;
                echo json_encode($feed);
                wp_die(); // AJAX
            }
            $i++;
        }
    }

    /**
     * Edit specific feed handle
     * @return void
     */
    public function Edit_feed_handler() {
        $index = filter_input(INPUT_POST, "index");
        $cron_worker = new feed_syndicate_cron();
        $feeds = get_option("FeedSyndicateFeeds");

        foreach (array_keys($feeds) as $key) {
            if ($index == $key) {
                $cron_worker->unschedule(array($feeds[$key])); // removes old schedule
                $feeds[$key] = $this->create_feed();
                $cron_worker->schedule(array($feeds[$key])); // creates new schedule
            }
        }

        update_option("FeedSyndicateFeeds", $feeds);

        $this->admin_handler->show_table();

        $_SESSION['edit_feed'] = true;
        $_SESSION['edit_feed_redirect'] = true;

        echo "<script>window.location.href='" . site_url() . "/wp-admin/admin.php?page=FeedSyndicateFeeds'; </script>";
        wp_die(); // AJAX
    }

    /**
     * Update (via cron) feed handle
     * @return void
     */
    public function auto_update_feed_handler() {
        if ($this->feed === "all") {
            $feeds = get_option("FeedSyndicateFeeds");
        }

        $inserted = 0;
        if (!empty($feeds)) {
            foreach ($feeds as $feed) {
                $inserted += $this->feed_handler->do_the_import($feed, filter_input(INPUT_POST, "publish"));
            }
        }
        echo sprintf(__("%d posts were inserted", "FeedSyndicateFeeds"), $inserted);

        wp_die(); // AJAX
    }

}
