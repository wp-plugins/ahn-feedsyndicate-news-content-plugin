<?php
class FeedSyndicateNewsML {

	public static function process_news_item( $newsitem ) {

		$data = array();

		$headline = $newsitem['NewsComponent']['NewsLines']['HeadLine']['value'];
		$ID       = $newsitem['Identification']['NewsIdentifier']['PublicIdentifier']['value'];

		$data["title"] = $headline;
		$data["ID"]    = $ID;

		foreach ( $newsitem['NewsComponent']['NewsComponent'] as $newscomponent ) {
			$data = self::process_news_component( $newscomponent, $data );
		}

		return $data;
	}

	public static function process_news_component( $newscomponent, $data ) {

		$excerpt     = "";
		$content     = "";
		$image       = "";
		$image_title = "";

		if ( array_key_exists( 'Role', $newscomponent ) ) {
			$excerpt = $newscomponent['ContentItem']['DataContent']['value'];
		} elseif ( array_key_exists( 'ContentItem', $newscomponent ) ) {
			$content = $newscomponent['ContentItem']['DataContent']['value'];
		} elseif ( array_key_exists( 'NewsComponent', $newscomponent ) ) {
			foreach ( $newscomponent['NewsComponent'] as $subitem ) {
				if ( array_key_exists( 'Role', $subitem ) ) {
					if ( $subitem['Role']['attr']['FormalName'] == 'Caption' ) {
						$image_title = $subitem['ContentItem']['DataContent']['value'];
					} elseif ( $subitem['Role']['attr']['FormalName'] == 'JPG' ) {
						$image = $subitem['ContentItem']['attr']['Href'];
					}
				}
			}
		}

		if ( $excerpt != "" )
			$data["excerpt"] = $excerpt;
		if ( $content != "" )
			$data["content"] = html_entity_decode( $content );
		if ( $image != "" )
			$data["image"] = $image;
		if ( $image_title != "" )
			$data["image_title"] = $image_title;

		return $data;

	}


	public static function xml2array( $contents, $get_attributes = 1 ) {

		if ( !$contents )
			return array();
		if ( !function_exists( 'xml_parser_create' ) )
			return array();

		$parser = xml_parser_create( '' );
		xml_parser_set_option( $parser, XML_OPTION_TARGET_ENCODING, "UTF-8" );
		xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_parse_into_struct( $parser, trim( $contents ), $xml_values );
		xml_parser_free( $parser );

		$xml_array = array();
		$current   = &$xml_array;

		foreach ( $xml_values as $data ) {
			unset( $attributes, $value );
			extract( $data );
			$result = '';

			if ( $get_attributes ) {
				$result = array();
				if ( isset( $value ) )
					$result['value'] = $value;

				if ( isset( $attributes ) ) {
					foreach ( $attributes as $attr => $val ) {
						if ( $get_attributes == 1 )
							$result['attr'][$attr] = $val;
					}
				}

			} elseif ( isset( $value ) ) {
				$result = $value;
			}

			if ( $type == "open" ) {
				$parent[$level - 1] = &$current;
				if ( !is_array( $current ) or ( !in_array( $tag, array_keys( $current ) ) ) ) { //Insert New tag
					$current[$tag] = $result;
					$current       = &$current[$tag];
				} else {
					if ( isset( $current[$tag][0] ) ) {
						array_push( $current[$tag], $result );
					} else {
						$current[$tag] = array( $current[$tag], $result );
					}
					$last    = count( $current[$tag] ) - 1;
					$current = &$current[$tag][$last];
				}
			} elseif ( $type == "complete" ) {

				if ( !isset( $current[$tag] ) ) {
					$current[$tag] = $result;
				} else {

					if ( ( is_array( $current[$tag] ) and $get_attributes == 0 ) or ( isset( $current[$tag][0] ) and is_array( $current[$tag][0] ) and $get_attributes == 1 ) ) {
						array_push( $current[$tag], $result );
					} else {
						$current[$tag] = array( $current[$tag], $result );
					}
				}
			} elseif ( $type == 'close' ) {
				$current = &$parent[$level - 1];
			}
		}
		return ( $xml_array );
	}


	public static function download_feed( $feed ) {

		$args = array( 'method'      => 'GET',
		               'timeout'     => 45,
		               'redirection' => 10,
		               'httpversion' => '1.0',
		               'user-agent'  => 'WordPress/FeedSyndicate-Plugin; ' . get_bloginfo( 'url' ),
		               'blocking'    => true,
		               'headers'     => array(),
		               'cookies'     => array(),
		               'body'        => null,
		               'compress'    => false,
		               'decompress'  => true,
		               'sslverify'   => false,
		               'stream'      => false,
		               'filename'    => null );

		$response = wp_remote_get( $feed["feed_url"], $args );
		if ( is_wp_error( $response ) ) {
			return null;
		} else {
			return $response;
		}
	}

}
