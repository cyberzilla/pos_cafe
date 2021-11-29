/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-30
 * Time: 2:00:01 AM
 * Filename: appDatabaseDiscount.js
 */
$(function () {
    var slug = "appDatabaseDiscount",
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


    root.find("#discountByMinimalOrder").change(function (e){
        var target = root.find(".discountMinimalOrderContainer");
        if(this.checked){
            target.show().find("input").show();
        }else{
            target.hide().find("input").hide();
        }
    });

    root.find("select[name=discountType]").change(function (e){
        var target = root.find(".discountExpiredContainer");
        switch (e.target.value){
            case "temporary":
                target.show();
                break;
            default:
                target.hide();
                break;
        }
    });

    root.find("#priceSellingPrice").change(function (e) {
       if($(this).autoNumeric("get")!==""){
           root.find("#discountByPercent").prop("readonly",false).focus();
       } else{
           root.find("#discountByPercent").prop("readonly",true).val("");
       }
    });

    root.find("#discountByPercent").keyup(function(e){
        var price = parseFloat(root.find("#priceSellingPrice").autoNumeric("get")),
            percent = parseFloat($(this).autoNumeric("get")),
            discount = percent!==""?((percent/100)*price):0;
        if(!isNaN(discount)){
            root.find("#discountByPrice.autoNumeric").autoNumeric("set",discount);
        }else{
            root.find("#discountByPrice.autoNumeric").autoNumeric("set",0);
        }
    });
});