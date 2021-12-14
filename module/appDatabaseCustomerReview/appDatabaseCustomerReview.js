/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2021-12-14
 * Time: 1:07:48 PM
 * Filename: appDatabaseCustomerReview.js
 */

$(function () {
    var slug = "appDatabaseCustomerReview",
        root = $(".icms-container").find("#" + slug + ".tab-pane");

    root.find("#icms-table-" + slug).parents(".icms-widget").on({
        'reload.ace.widget': function (e) {
            e.stopPropagation();
            var $box = $(this);
            nativeTable(slug, false, true, function (x) {
                if (x) {
                    setTimeout(function () {
                        $box.trigger('reloaded.ace.widget');
                        var $stars = root.find("#icms-table-" + slug).find(".rate"),
                            $color = $stars.data("hits")===5?"#26b539":($stars.data("hits")===4?"#87d44a":($stars.data("hits")===3?"#ffae38":($stars.data("hits")===2?"#fa6837":"#eb2228")));
                        $stars.raty({ readOnly: true, score: $stars.data("hits")}).find("i.fa").css({"font-size":"15px","width":"10px","height":"10px","color":$color});
                    }, parseInt(Math.random() * 500 + 500));
                }
            });
        },
        'reloaded.ace.widget': function (e) {
            var $stars = root.find("#icms-table-" + slug).find(".rate"),
                $color = $stars.data("hits")===5?"#26b539":($stars.data("hits")===4?"#87d44a":($stars.data("hits")===3?"#ffae38":($stars.data("hits")===2?"#fa6837":"#eb2228")));
            $stars.raty({ readOnly: true, score: $stars.data("hits")}).find("i.fa").css({"font-size":"15px","width":"10px","height":"10px","color":$color});
        }
    });

    root.find("#icms-table-" + slug).on("tb_act_custom",function (e,data) {
        var getAvatar = function (email){
            return 'https://www.gravatar.com/avatar/'+$.md5(email.trim().toLowerCase())+"?s=80&d=mp&r=g"
        };

        switch(data.extra){
            case "show":
                var param = {method:"actionPage",slug:slug,app:"app",appslug:"pos_cafe",act:"showReview",id:data.id};
                $.post("Services",param,function(res){
                    console.log(getAvatar(res.data.reviewEmail));
                },'json');
                break;
        }
    });

});