/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-26
 * Time: 10:01:59 PM
 * Filename: appDatabaseCategory.js
 */
$(function () {
    var slug = "appDatabaseCategory",
        root = $(".icms-container").find("#" + slug + ".tab-pane");

    root.find("#iform-appDatabaseCategory").on("submitted",function(e){ //Handle Submitted Trigger
        nativeSelect(slug);
    }).on("resetted",function(e){ //Handle On Resetted

    });

    root.find("#icms-table-appDatabaseCategory").on("tb_act_delete",function(e){
        nativeSelect(slug);
    });

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

    root.find("select[name=categoryType]").change(function (e){
        var target = root.find("select[name=categoryParentId]");
        switch (e.target.value){
            case "child":
                target.show();
                target.parent(".categoryParentIdContainer").show();
                break;
            default:
                target.hide();
                target.parent(".categoryParentIdContainer").hide();
                break;
        }
    });

    root.find("select[name=categoryParentId]").change(function (e){
        if (e.target.value!==""){
            root.find("#categoryAdditionalInfo").val($("#"+e.target.id+" option:selected").text());
        }else{
            root.find("#categoryAdditionalInfo").val("");
        }
    });
});