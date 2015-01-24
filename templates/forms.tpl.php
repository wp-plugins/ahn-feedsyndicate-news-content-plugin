<div id='tabs'> 
    <ul>
        <li><a href='#tabs-1'>All Feeds</a></li>
        <li><a href='#tabs-2'>Add New Feed</a></li>
    </ul>

    <div id='tabs-1'>
        <?php if (isset($_SESSION['edit_feed']) && $_SESSION['edit_feed'] == 1) { ?>
            <div id="Save_feeds" class="updated settings-error" ><p>Feed successfully updated.</p></div>
            <?php
            unset($_SESSION['edit_feed']);
        }
        ?>
        <div class='wrap' id='NewsML_Feeds'>
            <div class='icon32'><img src='<?php echo FeedSyndicateFeeds_URL; ?>/images/icon_big.png' /></div>
            <h2>NewsML Feeds</h2>
            <img src='<?php echo FeedSyndicateFeeds_URL; ?>/images/load.gif' id="delete_loading" class="feed_loading_image"/>
        </div>

        <div id="delete-confirm" title="Delete all feeds?">
            <p><span class="ui-icon ui-icon-alert"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>
        </div>

        <div id='FeedSyndicateLoader' title='Downloading news...'>
            <center><img src='<?php echo FeedSyndicateFeeds_URL; ?>/images/load.gif'/></center>
        </div>

        <div id='FeedSyndicateNotices' title='Finished!'>
        </div>

        <div id='FeedSyndicateEditFeed' title='Edit Feed'>
            <div id="Edit_feed_loading_wrap"><img src='<?php echo FeedSyndicateFeeds_URL; ?>/images/loading2.gif' id="Edit_feed_loading"/></div>
            <form method='post' name='FeedSyndicateFeedsEditForm' id='FeedSyndicateFeedsEditForm' action='#'>
                <?php wp_nonce_field('FeedSyndicateNewFeed', 'FeedSyndicateNonce'); ?>
                <table cellspacing="2" cellpadding="5" class="form-table edit-form-table">
                    <tbody>
                        <tr>
                            <td width="48%"><strong>Enter the NewsML Feed URL:</strong></td>
                            <td width="52%">
                                <input type="text" name="edit_form_feed_url" id="edit_form_feed_url"/>
                                <input type="hidden" name="edit_feed_array_index" id="edit_feed_array_index" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td width="48%"><strong>Title of NewsML Feed: </strong></td>
                            <td width="52%"><input type="text" name="edit_form_feed_title" id="edit_form_feed_title"/></td>
                        </tr>
                        <tr>
                            <td width="80"><strong>Store this feed in Category: </strong></td>
                            <td colspan="2"><?php
                                //wp_dropdown_categories('hide_empty=0&id=edit_form_cat');
                                $drop_cat = array(
                                    'orderby' => 'ID',
                                    'order' => 'ASC',
                                    'hide_empty' => 0,
                                    'hierarchical' => 1,
                                    'tab_index' => 10,
                                    'id' => 'edit_form_cat',
                                    'hide_if_empty' => false
                                );
                                wp_dropdown_categories($drop_cat);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td width="80"><strong>Assign posts to this author: </strong></td>
                            <td colspan="2"><?php wp_dropdown_users(array('who' => 'authors', 'id' => 'edit_user')); ?>
                            </td>
                        </tr>
                        <tr>
                            <td width="80" valign="top"><strong>Auto Publish: </strong></td>
                            <td colspan="2">
                                <select id="edit_form_feed_cron">
                                    <option name="form_feed_cron" value="never">&nbsp;Never Auto Publish</option>
                                    <?php
                                    foreach (wp_get_schedules() as $name => $data) {
                                        ?>
                                        <option name="form_feed_cron" value="<?php echo esc_attr($name); ?>">&nbsp;<?php echo esc_html($data['display']); ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>
                                <input class="button-primary" type="submit" value="Update" name="Upload" id="edit_Upload"/>
                                <img src='<?php echo FeedSyndicateFeeds_URL; ?>/images/load.gif' id="edit_submit_loading" class="feed_loading_image"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>

        <!--Custom dilaoge box for make a confirm action on deletion of a record-->
        <div id='FeedSyndicateFeedDelete' title='Confirm!'>
            Are you sure, you want to delete this?
        </div>

        <div id='FeedSyndicateData'>
            <?php do_action("FeedSyndicate_show_table"); ?>
        </div>
    </div>
    <div id='tabs-2'>
        <?php if (isset($_SESSION['add_new_feed']) && $_SESSION['add_new_feed'] == 1) { ?>
            <div id="Save_feeds" class="updated settings-error" ><p>Feed successfully saved.</p></div>
            <?php
            unset($_SESSION['add_new_feed']);
        }
        ?>
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
                        <td colspan="2"><?php
                            //wp_dropdown_categories('hide_empty=0&id=form_cat');
                            $drop_cat = array(
                                'orderby' => 'ID',
                                'order' => 'ASC',
                                'hide_empty' => 0,
                                'hierarchical' => 1,
                                'tab_index' => 10,
                                'id' => 'form_cat',
                                'hide_if_empty' => false
                            );
                            wp_dropdown_categories($drop_cat);
                            ?>
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
                            <select id="form_feed_cron">
                                <option name="form_feed_cron" value="never">&nbsp;Never Auto Publish</option>
                                <?php
                                foreach (wp_get_schedules() as $name => $data) {
                                    ?>
                                    <option name="form_feed_cron" value="<?php echo esc_attr($name); ?>">&nbsp;<?php echo esc_html($data['display']); ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input class="button-primary" type="submit" value="Add Feed" name="Upload" id="Upload"/>
                            <img src='<?php echo FeedSyndicateFeeds_URL; ?>/images/load.gif' id="submit_loading" class="feed_loading_image"/>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
