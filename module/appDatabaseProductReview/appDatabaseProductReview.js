/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2021-12-14
 * Time: 4:04:02 PM
 * Filename: appDatabaseProductReview.js
 */
$(function () {
    var slug = "appDatabaseProductReview",
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
        },
        'reloaded.ace.widget': function (e) {
            var $stars = root.find("#icms-table-" + slug).find(".rate");
            $.each($stars,function(i,stars){
                var color = $(stars).data("hits")>=5?"#26b539":($(stars).data("hits")>=4?"#87d44a":($(stars).data("hits")>=3?"#ffae38":($(stars).data("hits")>=2?"#fa6837":"#eb2228")));
                $(stars).raty({ readOnly: true, score: $(stars).data("hits")}).find("i.fa").css({"font-size":"15px","width":"10px","height":"10px","color":color});
            });
        }
    });

    root.one("page_loaded",function(){
        nativeTable(slug, false, true, function (x) {
            if (x) {
                setTimeout(function () {
                    root.find("#icms-table-" + slug).parents(".icms-widget").trigger('reloaded.ace.widget');
                }, parseInt(Math.random() * 500 + 500));
            }
        });
    });
});