<?php
class FeedSyndicateAdmin {

	private $page;

	/**
	 * @var FeedSyndicateFeeds
	 */
	private $feed_handler;

	public function __construct( $feed_handler ) {
		add_action( "admin_menu", array( $this, "admin_menu" ) );
		new FeedSyndicateAJAX ( $feed_handler, $this );
		$this->feed_handler = $feed_handler;
		add_action('FeedSyndicate_show_table', array($this, 'show_table'));
	}

	public function admin_print_styles($version) {
                global $wp_scripts;

		wp_register_script( "feedsyndicate", FeedSyndicateFeeds_URL . '/resources/admin.js', array( 'thickbox',
		                                                                                            'jquery-ui-dialog' ) );
		wp_enqueue_script( "feedsyndicate" );
                
		wp_register_script( "jquery-ui", FeedSyndicateFeeds_URL . '/resources/jquery-ui.js');
		wp_enqueue_script( "jquery-ui" );

		wp_register_script( "jquery-validation", FeedSyndicateFeeds_URL . '/resources/jquery.validate.min.js');
		wp_enqueue_script( "jquery-validation" );
		
		wp_register_style( "jquery-ui-css", FeedSyndicateFeeds_URL . '/resources/jquery-ui-1.8.16.custom.css' );
		wp_enqueue_style( "jquery-ui-css" );

	}
        
        //loading the currrent version of jquery 
        public function admin_current_jquery($version) {
                global $wp_scripts;
                if ( ( version_compare($version, $wp_scripts->registered['jquery']->ver) == 1 ) ) {
                    wp_deregister_script('jquery');
                    wp_register_script( "jquery", FeedSyndicateFeeds_URL . '/resources/jquery-'.$version.'.js');
                    wp_enqueue_script( "jquery" );
                }
        }

	public function admin_menu() {
		$this->page = add_menu_page( "FeedSyndicate Feeds", "FeedSyndicate", 'edit_posts', "FeedSyndicateFeeds", array( $this,
		                                                                                                                "admin_page" ), FeedSyndicateFeeds_URL . "/images/FeedSyndicateLogo.png",6 );
		add_action( 'admin_print_styles-' . $this->page, array( $this, "admin_print_styles" ) );
                
                //loading the currrent version of jquery 
                @add_action( 'wp_head', $this->admin_current_jquery( '1.10.2' ) );
	}

	public function admin_page() {
            
		load_template (FeedSyndicateFeeds_PATH . '/templates/forms.tpl.php');

	}
	public function show_table() {

		$table = new FeedSyndicateFeedsTable();
		$table->prepare_items();
		
		$options = get_option( "FeedSyndicateFeeds" );
		
		if(empty($options)){
			$_SESSION['data_not_found'] = true;
		}else{
			unset($_SESSION['data_not_found']);
		}		
		?>
		<div class="wrap">
			<form id="topics-filter" method="get" action="#">
				<?php $table->display() ?>
			</form>
		</div>
	<?php
	}

}
