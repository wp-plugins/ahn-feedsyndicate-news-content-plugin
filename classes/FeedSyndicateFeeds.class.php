<?php

class FeedSyndicateFeeds {

    const LOCK_TRANSIENT_NAME = 'feed_syndicate_update_lock';
    const LOCK_TRANSIENT_LONG = 60;

    public function __construct() {
        new FeedSyndicateAdmin($this);
    }

    public function do_the_import($feed, $status) {

        set_time_limit(180);

        if (get_transient(self::LOCK_TRANSIENT_NAME)) {
            return -1;
        }

        if (!set_transient(self::LOCK_TRANSIENT_NAME, true, self::LOCK_TRANSIENT_LONG)) {
            return -1;
        }

        $inserted = 0;

        $xml_worker = new feed_syndicate_xml($feed, $status);
        $posts = $xml_worker->work();

        foreach ($posts as $post) {
            $inserted += $this->create_post_if_not_exist($post);
        }

        delete_transient(self::LOCK_TRANSIENT_NAME);

        return $inserted;
    }

    public function cron_update($hash, $status) {
        if (is_array($hash)) {
            $hash = array_shift($hash);
        }

        $feed = $this->get_feed_from_hash($hash);
        $this->do_the_import($feed, $status);
    }

    public function get_feed_from_hash($hash) {
        $options = get_option("FeedSyndicateFeeds");
        foreach ($options as $option) {
            if ($option["hash"] == $hash) {
                return $option;
            }
        }
        return null;
    }

    private function create_post_if_not_exist($post_data) {


        $args = array('post_status' => array('publish', 'pending', 'draft', 'future', 'private', 'trash'),
            'meta_query' => array(array("key" => "_FeedSyndicateID",
                    "value" => $post_data["ID"],
                    "compare" => "=")));

        $existence = get_posts($args);

        if (!empty($existence)) {
            return 0;
        }

        $post = array('post_category' => array($post_data["category"]),
            'post_content' => $post_data["content"],
            'post_excerpt' => $post_data["excerpt"],
            'post_status' => $post_data["status"],
            'post_title' => $post_data["title"]);

        if (!empty($post_data["author"])) {
            $post['post_author'] = $post_data["author"];
        }

        $post_id = wp_insert_post($post);

        if ($post_id) {
            update_post_meta($post_id, '_FeedSyndicateID', $post_data["ID"]);
            $this->create_thumbnail($post_id, $post_data);
            return 1;
        } else {
            return 0;
        }
    }

    private function create_thumbnail($post_id, $post_data) {

        if (empty($post_data["image"])) {
            return null;
        }

        if (!function_exists("wp_generate_attachment_metadata")) {
            require_once ABSPATH . "/wp-admin/includes/image.php";
        }

        if (!function_exists('media_sideload_image')) {
            require_once ABSPATH . "wp-admin/includes/media.php";
        }

        if (!function_exists('download_url')) {
            require_once ABSPATH . "wp-admin/includes/file.php";
        }


        $att = media_sideload_image(esc_url($post_data["image"]), $post_id);

        if (is_wp_error($att)) {
            return null;
        }

        $image = preg_replace("/.*(?<=src=[\"'])([^\"']*)(?=[\"']).*/", '$1', $att);

        if (esc_url($image) !== $image) {
            return null;
        }

        $att_ID = $this->get_attachment_id_from_src($image);

        if (!empty($att_ID)) {
            update_post_meta($post_id, '_thumbnail_id', $att_ID, true);
            return true;
        } else {
            return null;
        }
    }

    private function get_attachment_id_from_src($image_src) {
        global $wpdb;
        $query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid='%s'", esc_url($image_src));
        $id = $wpdb->get_var($query);
        return $id;
    }

    public function remove_all_feeds() {

        $feeds = get_option("FeedSyndicateFeeds");

        if (!empty($feeds)) {
            foreach ($feeds as $feed) {

                if ($feed["cron"] != "never") {
                    $next = wp_next_scheduled("FeedSyndicateCronUpdate", array($feed["hash"]));
                    ($next ? wp_unschedule_event($next, 'FeedSyndicateCronUpdate', array($feed["hash"])) : NULL);
                }
            }
        }

        delete_option("FeedSyndicateFeeds");
    }

}
