/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-30
 * Time: 2:00:07 AM
 * Filename: appDatabaseVoucher.js
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
    var slug = "appDatabaseVoucher",
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

    root.find("select[name=voucherExpiredType]").change(function (e){
        var byLimit = root.find(".voucherLimitContainer"),
            byDate = root.find(".voucherExpiredContainer");
        switch (e.target.value){
            case "byDate":
                byDate.show();
                byLimit.hide().find("input").val("");
                break;
            case "byLimit":
                byLimit.show();
                byDate.hide().find("input").val("");
                break;
            default:
                byDate.hide().find("input").val("");
                byLimit.hide().find("input").val("");
                break;
        }
    });

    root.find("#icms-table-appDatabaseVoucher").on("tb_act_custom",function(e,data){
        switch(data.extra){
            case "voucher":
                var param = "method=actionPage&app=app&slug="+slug+"&appslug=pos_cafe&act=getVoucherCode&id="+data.id;
                $.post("Services",param,function (res) {
                    jAlert("Nama Voucer: "+res.data.voucherName+"\nKode Voucer: "+res.data.voucherCode+"\nNilai Voucer: Rp. "+(parseFloat(res.data.voucherValue)).toLocaleString("id-ID"),"Voucer Info");
                },'json');
                break;
        }
    });

    root.find(".randomGenerator").click(function (e) {
        var target = $(this).data("target");
        root.find(target).val(randomString(11));
    });
});