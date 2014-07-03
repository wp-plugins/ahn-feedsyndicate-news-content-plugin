<div id='tabs'>
    <ul>
        <li><a href='#tabs-2'>Add New Feed</a></li>
        <li><a href='#tabs-1'>All Feeds</a></li>
    </ul>
    <div id='tabs-1'>
        <div class='wrap' id='NewsML_Feeds'>
            <div class='icon32'><img src='<?php echo FeedSyndicateFeeds_URL; ?>/images/icon_big.png' /></div>
            <h2>NewsML Feeds</h2>
        </div>

        <div id="delete-confirm" title="Delete all feeds?">
            <p><span class="ui-icon ui-icon-alert"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>
        </div>

        <div id='FeedSyndicateLoader' title='Downloading news...'>
            <center><img src='<?php echo FeedSyndicateFeeds_URL; ?>/images/load.gif'/></center>
        </div>

        <div id='FeedSyndicateNotices' title='Finished!'>
        </div>

        <!--Custom dilaoge box for make a confirm action on deletion of a record-->
        <div id='FeedSyndicateFeedDelete' title='Comfirm!'>
            Are you sure, you want to delete this?
        </div>
		
		<div id='FeedSyndicateData'>
			<?php $this->show_table(); ?>
		</div>
    </div>
	
    <div id='tabs-2'>
        <form method='post' name='FeedSyndicateFeedsForm' id='FeedSyndicateFeedsForm' action='#'>
            <?php wp_nonce_field('FeedSyndicateNewFeed', 'FeedSyndicateNonce'); ?>

            <div class="wrap">
                <div id="icon-edit" class="icon32 icon32-posts-topic"><br/></div>
                <h2>Add new feed</h2>
            </div>
			
            <table cellspacing="2" cellpadding="5" class="form-table">
                <tbody>
                    <tr>
                        <td width="21%"><strong>Enter the NewsML Feed URL:</strong></td>
                        <td width="79%"><input type="text" name="form_feed_url" id="form_feed_url"/></td>
                    </tr>
                    <tr>
                        <td width="21%"><strong>Title of NewsML Feed: </strong></td>
                        <td width="79%"><input type="text" name="form_feed_title" id="form_feed_title"/></td>
                    </tr>
                    <tr>
                        <td width="80"><strong>Store this feed in Category: </strong></td>
                        <td colspan="2"><?php wp_dropdown_categories('hide_empty=0&id=form_cat'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="80"><strong>Assign posts to this author: </strong></td>
                        <td colspan="2"><?php wp_dropdown_users(array('who' => 'authors', 'id' => 'user')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="80" valign="top"><strong>Auto Publish: </strong></td>
                        <td colspan="2">
                            <input type="radio" name="form_feed_cron" value="never" checked/>&nbsp;Never<br/>
                            <?php foreach (wp_get_schedules() as $name => $data) { ?>
                                <input type="radio" name="form_feed_cron" value="<?php echo esc_attr($name); ?>"/>&nbsp;<?php echo esc_html($data['display']); ?><br/>
                            <?php } ?>

                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td><input class="button-primary" type="submit" value="Add Feed" name="Upload" id="Upload"/></td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
