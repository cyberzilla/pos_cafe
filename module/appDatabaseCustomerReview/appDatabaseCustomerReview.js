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
                        var $stars = root.find("#icms-table-" + slug).find(".rate");
                        $.each($stars,function(i,stars){
                            var color = $(stars).data("hits")===5?"#26b539":($(stars).data("hits")===4?"#87d44a":($(stars).data("hits")===3?"#ffae38":($(stars).data("hits")===2?"#fa6837":"#eb2228")));
                            $(stars).raty({ readOnly: true, score: $(stars).data("hits")}).find("i.fa").css({"font-size":"15px","width":"10px","height":"10px","color":color});
                        });
                    }, parseInt(Math.random() * 500 + 500));
                }
            });
        },
        'reloaded.ace.widget': function (e) {
            var $stars = root.find("#icms-table-" + slug).find(".rate");
                $.each($stars,function(i,stars){
                    var color = $(stars).data("hits")===5?"#26b539":($(stars).data("hits")===4?"#87d44a":($(stars).data("hits")===3?"#ffae38":($(stars).data("hits")===2?"#fa6837":"#eb2228")));
                    $(stars).raty({ readOnly: true, score: $(stars).data("hits")}).find("i.fa").css({"font-size":"15px","width":"10px","height":"10px","color":color});
                });
        }
    });

    root.find("#icms-table-" + slug).on("tb_act_custom",function (e,data) {
        var getAvatar = function (email){
                return 'https://www.gravatar.com/avatar/'+$.md5(email.trim().toLowerCase())+"?s=80&d=mp&r=g"
            },
            rating = function (el,hits){
                var color = hits==="5"?"#26b539":(hits==="4"?"#87d44a":(hits==="3"?"#ffae38":(hits==="2"?"#fa6837":"#eb2228")));
                $(el).raty({ readOnly: true, score: hits}).find("i.fa").css({"font-size":"15px","width":"10px","height":"10px","color":color});
            },
            nameContainer = data.selector.parents("tr").children()[2].firstChild;

        switch(data.extra){
            case "show":
                var param = {method:"actionPage",slug:slug,app:"app",appslug:"pos_cafe",act:"showReview",id:data.id};
                $.post("Services",param,function(res){
                    $.alerts.showIcon=false;
                    jAlert('<img class="icms-logo" src="'+getAvatar(res.data.reviewEmail)+'" alt="Logo" style="width:70px;float:left;"><div style="margin-left:80px;"><b style="font-size: 13px;">'+res.data.reviewName+'</b>\n<div class="rating" data-hits="'+res.data.reviewHits+'"></div><p><i>'+res.data.reviewContent+'</i></p></div>',res.data.reviewDateTime);
                    rating($("#popup_container").find(".rating"),res.data.reviewHits);
                    $(nameContainer).removeClass("readed red animate__animated animate__headShake animate__infinite");
                    $.alerts.showIcon=true;
                },'json');
                break;
        }
    });

});