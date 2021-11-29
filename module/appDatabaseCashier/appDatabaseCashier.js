/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-31
 * Time: 3:14:20 PM
 * Filename: appStoreSettingCashier.js
 */
$(function () {
    var slug = "appDatabaseCashier",
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