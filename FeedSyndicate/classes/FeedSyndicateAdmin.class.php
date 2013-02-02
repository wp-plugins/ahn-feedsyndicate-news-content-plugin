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
	}

	public function admin_print_styles() {

		wp_register_script( "feedsyndicate", FeedSyndicateFeeds_URL . '/resources/admin.js', array( 'thickbox',
		                                                                                            'jquery-ui-dialog' ) );
		wp_enqueue_script( "feedsyndicate" );

		wp_register_style( "jquery-ui-css", FeedSyndicateFeeds_URL . '/resources/jquery-ui-1.8.16.custom.css' );
		wp_enqueue_style( "jquery-ui-css" );

	}


	public function admin_menu() {
		$this->page = add_menu_page( "FeedSyndicate Feeds", "FeedSyndicate", 'edit_posts', "FeedSyndicateFeeds", array( $this,
		                                                                                                                "admin_page" ), FeedSyndicateFeeds_URL . "/images/icon.png" );
		add_action( 'admin_print_styles-' . $this->page, array( $this, "admin_print_styles" ) );
	}

	public function admin_page() {
		echo "<div class='wrap'>";

		echo "<div class='icon32'><img src='" . FeedSyndicateFeeds_URL . "/images/icon_big.png' /></div>";
		echo "<h2>" . __( "NewsML Feeds", "FeedSyndicateFeeds" ) . "</h2>";
		echo "</div>";

		echo '<div style="display:none;" id="delete-confirm" title="' . __( "Delete all feeds?", "FeedSyndicateFeeds" ) . '">';
		echo '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' . __( "These items will be permanently deleted and cannot be recovered. Are you sure?", "FeedSyndicateFeeds" ) . '</p>';
		echo '</div>';

		echo "<div id='FeedSyndicateLoader' style='display: none;' title='" . __( "Downloading news...", "FeedSyndicateFeeds" ) . "'>";
		echo "<center><img src='" . FeedSyndicateFeeds_URL . "/images/load.gif'/></center>";
		echo "</div>";

		echo "<div id='FeedSyndicateNotices' title='" . __( "Finished!", "FeedSyndicateFeeds" ) . "'>";
		echo "</div>";

		echo "<div id='FeedSyndicateData'>";
		$this->show_table();
		echo "</div>";

		$this->show_new_form();

	}


	public function show_table() {

		$table = new FeedSyndicateFeedsTable();
		$table->prepare_items();
		?>
	<div class="wrap">
		<form id="topics-filter" method="get" action="#">
			<?php $table->display() ?>
		</form>
	</div>
	<?php
	}

	protected function show_new_form() {

		echo "<form method='post' name='FeedSyndicateFeedsForm' id='FeedSyndicateFeedsForm' action='#'>";
		wp_nonce_field( 'FeedSyndicateNewFeed', 'FeedSyndicateNonce' );
		?>

	<div class="wrap">
		<div id="icon-edit" class="icon32 icon32-posts-topic"><br/></div>
		<h2><?php _e( "Add new feed", "FeedSyndicateFeeds" ); ?></h2>
	</div>
	<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
		<tbody>
		<tr>
			<td width="21%"><strong><?php _e( "Enter the NewsML Feed URL", "FeedSyndicateFeeds" );?>:</strong></td>
			<td width="79%"><input type="text" style="width:250px;" name="form_feed_url" id="form_feed_url"/></td>
		</tr>
		<tr>
			<td width="21%"><strong><?php _e( "Title of NewsML Feed", "FeedSyndicateFeeds" );?>: </strong></td>
			<td width="79%"><input type="text" style="width:250px;" name="form_feed_title" id="form_feed_title"/></td>
		</tr>
		<tr>
			<td width="80"><strong><?php _e( "Store this feed in Category", "FeedSyndicateFeeds" );?>: </strong></td>
			<td colspan="2"><?php wp_dropdown_categories( 'hide_empty=0&id=form_cat' ); ?>
			</td>
		</tr>
		<tr>
			<td width="80"><strong><?php _e( "Assign posts to this author", "FeedSyndicateFeeds" );?>: </strong></td>
			<td colspan="2"><?php wp_dropdown_users( array( 'who' => 'authors', 'id' => 'user' ) ); ?>
			</td>
		</tr>
		<tr>
			<td width="80" valign="top"><strong><?php _e( "Auto Publish", "FeedSyndicateFeeds" );?>: </strong></td>
			<td colspan="2">
				<input type="radio" name="form_feed_cron" value="never" checked/>&nbsp;<?php _e( "Never", "FeedSyndicateFeeds" );?><br/>
			<?php foreach ( wp_get_schedules() as $name => $data ) { ?>
				<input type="radio" name="form_feed_cron" value="<?php echo esc_attr( $name );?>"/>&nbsp;<?php echo esc_html($data['display']); ?><br/>
			<?php } ?>

			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input class="button-primary" type="submit" value="<?php _e( "Add Feed", "FeedSyndicateFeeds" );?>"
			           name="Upload" id="Upload"/></td>
		</tr>
		</tbody>
	</table>
	<?php

	}

}
