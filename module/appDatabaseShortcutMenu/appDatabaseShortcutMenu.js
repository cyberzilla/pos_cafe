/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2021-11-15
 * Time: 5:45:17 AM
 * Filename: appDatabaseShortcutMenu.js
 */
$(function () {
    var slug = "appDatabaseShortcutMenu",
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