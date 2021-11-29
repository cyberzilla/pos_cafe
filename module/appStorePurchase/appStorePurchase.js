/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-31
 * Time: 6:01:15 PM
 * Filename: appStorePurchase.js
 */
$(function () {
    var slug = "appStorePurchase",
        root = $(".icms-container").find("#" + slug + ".tab-pane");

    root.find("#icms-table-" + slug).parents(".icms-widget").on({
        'reload.ace.widget': function (e) {
            e.stopPropagation();
            var $box = $(this);
            nativeTable(slug, false, true, function (x) {
                if (x) {
                    setTimeout(function () {
                        $box.trigger('reloaded.ace.widget');
                    }, parseInt(Math.random() * 500 + 500));
                }
            });
        }
    });
});