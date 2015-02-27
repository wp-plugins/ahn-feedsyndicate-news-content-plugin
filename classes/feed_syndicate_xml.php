<?php

/**
 * Primary XML parsing class
 * Requires libxml
 * @author Alexander K
 */
class feed_syndicate_xml {

    /**
     * @var object|false Processed (XML) feed
     */
    private $obj_xml;

    /**
     * @var array|WP_Error Downloaded (raw) feed
     */
    private $raw_xml;

    /**
     * @var array Feed parameters
     */
    private $feed;

    /**
     * @var string Reserved for further processing (posting stage)
     */
    private $status;

    /**
     * @var array Return value
     */
    private $return;

    /**
     * Assigns calling parameters as properties
     * @param array $feed Feed parameters
     * @param string $status optional Reserved for further processing (posting stage)
     * @return void
     */
    public function __construct($feed, $status = "publish") {
        $this->feed = $feed;
        $this->status = $status;
    }

    /**
     * Primary function to be called when class is initiated
     * @return array Associative array with all necessary info for publishing posts
     */
    public function work() {
        $this->download_feed(); // downloads feed
        $this->xml_to_obj(); // parses feed into object

        foreach ($this->obj_xml->NewsItem as $NewsItem) {
            $return = array(
                "title" => $NewsItem->NewsComponent->NewsLines->HeadLine,
                "ID" => $NewsItem->Identification->NewsIdentifier->PublicIdentifier,
                "excerpt" => $NewsItem->NewsComponent->NewsComponent[0]->ContentItem->DataContent,
                "content" => html_entity_decode($NewsItem->NewsComponent->NewsComponent[1]->ContentItem->DataContent),
                "image_title" => (isset($NewsItem->NewsComponent->NewsComponent[2]->NewsComponent[0]->ContentItem->DataContent) ? $NewsItem->NewsComponent->NewsComponent[2]->NewsComponent[0]->ContentItem->DataContent : ""),
                "image" => (isset($NewsItem->NewsComponent->NewsComponent[2]->NewsComponent[1]->ContentItem["Href"]) ? $NewsItem->NewsComponent->NewsComponent[2]->NewsComponent[1]->ContentItem["Href"] : ""),
                "status" => $this->status,
                "category" => $this->feed["cat"],
                "author" => (isset($this->feed["user"]) ? : ""));
            foreach ($return as &$return_item) { // trims each member of $return array directly (reference)
                $return_item = (string) trim($return_item);
            }
            unset($return_item);

            $this->return[] = $return;
        }

        return $this->return;
    }

    /**
     * Downloads feed and assisgns it to internal property
     * @return void
     */
    private function download_feed() {
        $args = array('method' => 'GET',
            'timeout' => 45,
            'redirection' => 10,
            'httpversion' => '1.0',
            'user-agent' => 'WordPress/FeedSyndicate-Plugin; ' . get_bloginfo('url'),
            'blocking' => TRUE,
            'headers' => array(),
            'cookies' => array(),
            'body' => NULL,
            'compress' => FALSE,
            'decompress' => TRUE,
            'sslverify' => FALSE,
            'stream' => FALSE,
            'filename' => NULL); // Preparing download parameters

        $this->raw_xml = wp_remote_get($this->feed["feed_url"], $args); // WP function for downloading

        if (is_wp_error($this->raw_xml)) { // error checking
            wp_die($this->raw_xml->get_error_code() & " " & $this->raw_xml->get_error_message());
        }
    }

    /**
     * Parses downloaded feed into object and assigns it to internal property
     * @return void
     */
    private function xml_to_obj() {
        $this->obj_xml = simplexml_load_string($this->raw_xml["body"], "SimpleXMLElement", LIBXML_NOCDATA); // LIBXML_NOCDATA is required for parsing CDATA blocks properly
    }

}
