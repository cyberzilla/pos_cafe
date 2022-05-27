/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2022-05-27
 * Time: 2:46:33 PM
 * Filename: appDatabaseWifi.js
 */

function randomString(length) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

$(function () {
    var slug = "appDatabaseWifi",
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

    root.find(".randomGenerator").click(function (e) {
        var target = $(this).data("target"),
            pass = root.find("#wifiPassword");
        root.find(target).val(randomString(7).toLowerCase());
        pass.val($(target).val());
    });

    root.one("page_loaded",function(){
        var data = "method=actionPage&slug=appDatabaseWifi&app=app&appslug=pos_cafe&act=loadParam";
       $.post("Services",data,function(res){
           console.log(res);
       },'json');
    });
});