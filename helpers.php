<?php

if (!function_exists("FeedSyndicateRemoteImageGrabber")) {

    // Function from remote-image-grabber plugin
    function FeedSyndicateRemoteImageGrabber($post, $url) {

        $url2 = str_replace('&amp;', '&', $url);
        $upload = wp_upload_dir($post['post_date']);
        preg_match('/[a-z0-9%_\.-]+\.(jpg|jpeg|gif|png)/i', $url2, $pu);
        $file_name = $pu[0];

        $upload2 = wp_upload_bits($file_name, 0, '', $post['post_date']);

        if ($upload2['error']) {
            echo $upload2['error'];
            return new WP_Error('upload_dir_error', $upload2['error']);
        }

        $headers = wp_get_http($url2, $upload2['file']);

        if (!$headers) {
            @unlink($upload2['file']);
            return new WP_Error('import_file_error', __('Remote server did not respond', 'FeedSyndicateFeeds'));
        }

        if ($headers['response'] != '200') {
            @unlink($upload2['file']);
            return new WP_Error('import_file_error', sprintf(__('Remote server says: %1$d %2$s', 'FeedSyndicateFeeds'), $headers['response'], get_status_header_desc($headers['response'])));
        } elseif (isset($headers['content-length']) && filesize($upload2['file']) != $headers['content-length']) {
            @unlink($upload2['file']);
            return new WP_Error('import_file_error', __('Remote file is incorrect size', 'FeedSyndicateFeeds'));
        }

        $upload2['content-type'] = $headers['content-type'];
        return $upload2;
    }

}
?>