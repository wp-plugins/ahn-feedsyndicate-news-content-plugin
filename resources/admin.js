var AJAX = 'feed_syndicate';

/*
 * Main Functions
 */

function removeFeed(feed_id, from) {
    var data = {
        action: AJAX,
        type: 'remove_feed_handler',
        feed: feed_id,
        nonce: jQuery("#FeedSyndicateNonce").val()
    };
    if (from != "all") {
        jQuery("#FeedSyndicateFeedDelete").dialog({
            modal: true,
            minHeight: 150,
            height: "auto",
            resizable: false,
            closeText: "",
            buttons: {
                'Delete': function () {
                    jQuery("#delete_loading").show();
                    jQuery.post(ajaxurl, data, function (response) {
                        jQuery("#FeedSyndicateData").html(response);
                        jQuery("#delete_loading").hide();
                        jQuery("#delete_feeds").remove();
                        jQuery('<div id="delete_feeds" class="updated settings-error" ><p>Feed successfully deleted.</p></div>').insertBefore("#delete_loading");
                        jQuery("#delete_feeds").fadeOut(3000);
                    });
                    jQuery(this).dialog('close');
                },
                'Cancel': function () {
                    jQuery(this).dialog('close');
                }
            }
        });
    } else {
        jQuery.post(ajaxurl, data, function (response) {
            jQuery("#FeedSyndicateData").html(response);
            jQuery("#delete_loading").hide();
            jQuery("#delete_feeds").remove();
            jQuery('<div id="delete_feeds" class="updated settings-error" ><p>Feed successfully deleted.</p></div>').insertBefore("#delete_loading");
            jQuery("#delete_feeds").fadeOut(3000);
        });
    }

}

function updateFeed(feed_id, shouldPublish) {

    jQuery("#FeedSyndicateLoader").dialog({
        modal: true,
        minHeight: 120,
        height: "auto",
        resizable: false,
        closeText: ""
    });
    var data = {
        action: AJAX,
        type: 'update_feed_handler',
        feed: feed_id,
        publish: shouldPublish,
        nonce: jQuery("#FeedSyndicateNonce").val()
    };
    jQuery.post(ajaxurl, data, function (response) {

        jQuery("#FeedSyndicateNotices").html(response);
        jQuery("#FeedSyndicateLoader").dialog("close");
        jQuery("#FeedSyndicateNotices").dialog({
            modal: true,
            minHeight: 120,
            height: "auto",
            resizable: false,
            closeText: ""
        });
    });
}

function editFeed(feed_id) {

    var data = {
        action: AJAX,
        type: 'get_specific_feed_for_edit',
        feed: feed_id,
        nonce: jQuery("#FeedSyndicateNonce").val()
    };
    jQuery.post(ajaxurl, data, function (response) {
        jQuery("#FeedSyndicateEditFeed").show();
        jQuery("#FeedSyndicateEditFeed").dialog({
            modal: true,
            minHeight: 200,
            minWidth: 600,
            resizable: false,
            closeText: ""
        });
        var data = JSON.parse(response);
        jQuery("#edit_form_feed_url").val(data['feed_url']);
        jQuery("#edit_form_feed_title").val(data['feed_title']);
        jQuery("#edit_feed_array_index").val(data['index']);
        jQuery('#edit_form_feed_publish').val(data['publish']);
        jQuery("#edit_form_feed_publish").change();
        jQuery("#edit_form_cat option").each(function () {
            var cat = jQuery(this).attr('value');
            if (cat == data['cat']) {
                jQuery(this).attr("selected", "selected");
            }
        });
        jQuery("#edit_user option").each(function () {
            var user = jQuery(this).attr('value');
            if (user == data['user']) {
                jQuery(this).attr("selected", "selected");
            }
        });
        jQuery("#edit_form_feed_cron").val(data["cron"]);
    });
    //form validation rules for edit feed form
    jQuery("#FeedSyndicateFeedsEditForm").validate({
        rules: {
            edit_form_feed_url: "required",
            edit_form_feed_title: "required"
        },
        messages: {
            edit_form_feed_url: " Required field",
            edit_form_feed_title: " Required field"
        },
        submitHandler: function (form) {
            jQuery("#edit_submit_loading").show();
            var data = {
                feed: feed_id,
                action: AJAX,
                type: 'Edit_feed_handler',
                feed_url: jQuery("#edit_form_feed_url").val(),
                feed_title: jQuery("#edit_form_feed_title").val(),
                cat: jQuery("#edit_form_cat").val(),
                publish: jQuery("#edit_form_feed_publish").val(),
                cron: jQuery("#edit_form_feed_cron").val(),
                nonce: jQuery("#FeedSyndicateNonce").val(),
                user: jQuery("#edit_user").val(),
                index: jQuery("#edit_feed_array_index").val()
            };
            jQuery.post(ajaxurl, data, function (response) {
                jQuery("#FeedSyndicateData").html(response);
                jQuery("#edit_submit_loading").hide();
                jQuery("#FeedSyndicateEditFeed").dialog('close');
            });
        }
    });
}

/*
 * Auxilary Functions
 */

function displayErrors() {
    jQuery("#tabs-1").find(".error").hide();
    var wordpress_error = jQuery("#tabs-1").find(".error").html();
    if (wordpress_error != undefined) {
        var error_html = "<div class='error'>" + wordpress_error + "</div>";
        jQuery(error_html).insertAfter(".update-nag");
        jQuery(".error").css("margin-left", "2px !important");
    }
}

function publish(id) {
    var el = document.getElementById(id);
    el.onchange = publish_handler;
}

function publish_handler() {

    var cron_id = this.id.split("_");
    cron_id[cron_id.length - 1] = "cron";
    cron_id = cron_id.join("_");
    cron_el = document.getElementById(cron_id);

    switch (this.value)
    {
        case "publish":
        case "draft":
            cron_el.removeAttribute("disabled");
            break;

        case "no":
            cron_el.setAttribute("disabled", true);
            break;
    }
}

function publish_slider(id) {
    jQuery(id).slider({
        min: 1,
        max: 9,
        step: 1
    });
    slider_ticks(id);
}

function buttons() {
    jQuery("body").on("click", "#FeedSyndicateRemoveAll", function () {
        jQuery("#delete-confirm").dialog({
            resizable: false,
            minHeight: 150,
            height: "auto",
            modal: true,
            closeText: "",
            buttons: {
                "Delete all feeds": function () {
                    removeFeed("all", "all");
                    jQuery(this).dialog("close");
                },
                Cancel: function () {
                    jQuery(this).dialog("close");
                }
            }
        });
    });
    jQuery("body").on("click", "#FeedSyndicatePublishAll", function () {
        updateFeed("all", "publish");
    });
    jQuery("body").on("click", "#FeedSyndicateDraftAll", function () {
        updateFeed("all", "draft");
    });
    jQuery("#FeedSyndicateFeedsForm").validate({
        rules: {
            form_feed_url: "required",
            form_feed_title: "required"
        },
        messages: {
            form_feed_url: " Required field",
            form_feed_title: " Required field"
        },
        submitHandler: function (form) {
            jQuery("#submit_loading").show();
            var data = {
                action: AJAX,
                type: 'new_feed_handler',
                feed_url: jQuery("#form_feed_url").val(),
                feed_title: jQuery("#form_feed_title").val(),
                cat: jQuery("#form_cat").val(),
                publish: jQuery("#form_feed_publish").val(),
                cron: jQuery("#form_feed_cron").val(),
                nonce: jQuery("#FeedSyndicateNonce").val(),
                user: jQuery("#user").val()
            };
            jQuery.post(ajaxurl, data, function (response) {
                jQuery("#form_feed_url").val('');
                jQuery("#form_feed_title").val('');
                jQuery("#FeedSyndicateData").html(response);
                // location.reload();
                jQuery("#submit_loading").hide();
            });
        }
    });
}

function slider_ticks(id) {
    if (id.charAt(0) === "#") {
        id = id.substr(1);
    }

    var slider = document.getElementById(id);
    var max = jQuery("#form_feed_cron_slider").slider("option", "max");
    var spacing = 100 / (max - 1);

    if (slider) {
        var to_delete = document.getElementsByClassName("ui-slider-tick-mark");
        if (to_delete) {
            for (i = 0; i < to_delete.length - 1; i++) {
                to_delete[i].parentNode.removeNode(to_delete[i]);
            }
        }

        var text_values = ["5 min", "10 min", "15 min", "30 min", "1 Hr", "4 Hr", "6 Hr", "12 Hr", "24 Hr"];

        for (var i = 0; i < max; i++) {
            var tick = slider.appendChild(document.createElement("span"));
            tick.style.left = (spacing * i) + "%";
            tick.setAttribute("class", "ui-slider-tick");
            var tick_text = tick.appendChild(document.createElement("span"));
            tick_text.setAttribute("class", "ui-slider-tick-text");
            tick_text.innerHTML = text_values[i];
        }
    }
}

window.onload = function () {

    jQuery("#FeedSyndicateEditFeed").hide();
    jQuery("#FeedSyndicateFeedDelete").hide();
    jQuery("#delete-confirm").hide();
    jQuery("#delete_loading").hide();
    jQuery("#feed_loading_image").hide();
    jQuery("#submit_loading").hide();
    jQuery("#edit_submit_loading").hide();
    jQuery("#FeedSyndicateLoader").hide();
    jQuery("#Save_feeds").fadeOut(5000);

    buttons();

    publish("form_feed_publish");
    publish("edit_form_feed_publish");

    displayErrors();
    jQuery("#tabs").tabs();

    //Auto publish cron set to 30 minutes
    setInterval(function () {
        var data = {
            action: AJAX,
            type: 'auto_update_feed_handler',
            feed: 'all',
            publish: "publish"
        };
        jQuery.post(ajaxurl, data);
    }, 1800000);
};