/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-02
 * Time: 4:16:11 AM
 * Filename: appStoreCashier.js
 */

$(function () {
    var slug = "appStoreCashier",
        root = $(".icms-container").find("#" + slug + ".tab-pane");

    hotkeys('ctrl+q,ctrl+a,ctrl+z,ctrl+enter', function (event, handler){
        switch (handler.key) {
            case 'ctrl+q':
                root.find("#cartQuantity").focus();
                break;
            case 'ctrl+a':
                root.find("#productName").focus();
                break;
            case 'ctrl+z':
                var cash = root.find("#orderPricePayment"),
                    po = root.find("#orderDownPayment");
                if(cash.parents(".row").is(':visible')){
                    cash.focus();
                }
                if(po.parents(".row").is(':visible')){
                    po.focus();
                }
                break;
            case "ctrl+enter":
                var btnPay = root.find(".pricePaymentButton");
                    if(btnPay.prop("disabled")===false){
                        btnPay.click();
                    }
                break;

                default:
                break;
        }
    });

    hotkeys.filter = function(event){
        var tagName = (event.target || event.srcElement).tagName;
        hotkeys.setScope(/^(INPUT|TEXTAREA|SELECT)$/.test(tagName) ? 'input' : 'other');
        return true;
    }

    root.find("[data-action=close_cashier]").click(function (e) {
        var check = getData({key:"icms-cashier"}),
            content,pettycashLastCash=0,itemname,groupPrices="",
            param = {method:"actionPage",slug:slug,appslug:"pos_cafe",app:"app",act:"precloseCashier",cashierId: check.pettycashCashierId,pettycashDateTime:check.pettycashDateTime,pettycashStartCash:check.pettycashStartCash};
        $.post("Services",param,function(res) {
            var tutupKasir = function(){
                if (res.content.allItem !== null) {
                    content = '<div class="closeCashierContainer"><div class="ccHeader"><table><tr><th>Kasir&nbsp;</th><td>:</td><td>&nbsp;' + check.cashierName + '</td></tr><tr><th>Buka Kasir&nbsp;</th><td>:</td><td>&nbsp;' + check.pettycashDateTime + '</td></tr></table></div><div class="ccBody" style="margin: 5px 0"><table class="table table-hover table-bordered table-striped no-margin"><tr><th>Nama Menu</th><th class="text-center">Terjual</th></tr>';
                    $.each(res.content.allItem, function (i, item) {
                        itemname = Array.isArray(item.name) ? (item.name).join(" ") : item.name;
                        content += '<tr><td>' + itemname + '</td><td class="text-center">' + item.qty + ' x</td></tr>';
                    });

                    $.each(res.content.groupPrices, function (i, item) {
                        groupPrices += '<tr><th class="text-right">' + (item.orderType === "card" ? "Non-Tunai" : (item.orderType === "cash" ? "Tunai" : "Lain-lain")) + '</th><td>:</td><th class="text-right">' + (parseFloat(item.totalPrice)).toLocaleString("id-ID") + '</th></tr>';
                    });
                    content += '</table></div><div class="ccFooter"><table style="width: 100%;"><tr><th class="text-right">Kas Awal</th><td>:</td><th class="text-right">' + (parseFloat(res.content.startCash)).toLocaleString("id-ID") + '</th></tr>' + groupPrices + '<tr><th class="text-right">Total Pemasukan</th><td>:</td><th class="text-right">' + (parseFloat(res.content.totalPrices)).toLocaleString("id-ID") + '</th></tr></table></div></div>';
                    pettycashLastCash = res.content.totalPrices;
                } else {
                    content = "Tidak ada data penjualan";
                }
                $.alerts.showIcon = false;
                jCustomConfirm(content, "Tutup Kasir", "Tutup Kasir", "Batal", function (r) {
                    if (r) {
                        var param = "method=actionPage&slug=appStoreCashier&app=app&appslug=pos_cafe&act=closeCashier&cashierId=" + check.pettycashCashierId + "&cashierUsersId=" + check.cashierUsersId + "&datetime=" + check.pettycashDateTime + "&pettycashStartCash=" + check.pettycashStartCash + "&pettycashLastCash=" + (parseFloat(pettycashLastCash) + parseFloat(check.pettycashStartCash));
                        $.post("Services", param, function (res2) {
                            if (res2.status === "success") {
                                var readyPrint = {
                                    "storeName": check.receiptStoreName,
                                    "storeAddress": check.receiptStoreAddress,
                                    "storePhone": check.receiptStorePhone,
                                    "storeCashier": "KASIR: " + check.cashierName
                                }, mergeObj, contents;

                                // readyPrint['storePrintType'] = "Close";
                                readyPrint['storePrintType'] = "CC:1:PR1";

                                mergeObj = Object.assign(readyPrint, res2.content);
                                contents = JSON.stringify({"content": mergeObj});
                                location.href = 'jprint:' + btoa(contents);

                                deleteKey({key: "icms-cashier"});
                                deleteKey({key: "icms-invoice-number"});
                                root.find(".preload-content").fadeIn(500);
                                root.find(".original-content").hide();
                                root.find(".no-access").hide();
                                $.gritter.add({
                                    title: 'Tutup Kasir',
                                    text: res2.messageSuccess,
                                    class_name: 'gritter-success'
                                });
                                paymentKeypress(0, 0);
                                root.find(".icms-receipt").fadeOut(500);
                                root.find("#iform-appStoreCashier").fadeOut(500);
                                root.find("#totalPrice").autoNumeric("set", 0);
                                deleteKey({key: "icms-voucher"});
                                root.find(".tableVoucher").html(0).data("price", 0);
                                root.find("#orderPricePayment").val("");
                                root.find("#orderVoucherCode").val("");
                            } else {
                                $.gritter.add({
                                    title: "Tutup Kasir",
                                    text: res2.messageSuccess,
                                    class_name: 'gritter-error'
                                });
                            }
                        }, 'json');
                    }
                });
                $.alerts.showIcon = true;
                $("#popup_container").find('.ccBody').ace_scroll('modify', {
                    size: 200,
                    observeContent: true,
                    touchDrag: true,
                    touchSwipe: true,
                    styleClass: 'scroll-margin scroll-dark'
                });
            }
            if (res.preorder > 0 ) {
                jCustomConfirm("Terdapat "+res.preorder+" transaksi preorder yang belum selesai, Apakah anda akan tetap menutup kasir?","Tutup Kasir","Lanjutkan","Batalkan",function(r){
                   if(r){
                       tutupKasir();
                   }
                });
            } else {
                tutupKasir();
            }
        },'json');
    });

    function runClock(prefix,element,hour12=false){
        var clock = function(prefix,el,hour12){el.html(prefix+(new Date()).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit',second:'2-digit', hour12: hour12}));}
        setInterval(function(){clock(prefix,element,hour12)},1000);
    }

    function getDateNow(format=false,strip=false){
        var today = new Date();
        if(format){
            if(strip){
                return today.getFullYear()+"-"+String(today.getMonth() + 1).padStart(2, '0')+"-"+String(today.getDate()).padStart(2, '0');
            }else{
                return String(today.getDate()).padStart(2, '0')+"/"+String(today.getMonth() + 1).padStart(2, '0')+"/"+today.getFullYear();
            }
        }else{
            return String(today.getDate()).padStart(2, '0')+String(today.getMonth() + 1).padStart(2, '0');
        }
    }

    root.one("page_loaded",function (e,res) {
        var check = getData({key:"icms-cashier"}),
            uActive = getData({key:"icms-users-active"}),
            merge = getData({key:"icms-invoice-merge"}),
            pettyParam = {method:"actionPage",slug:slug,app:"app",appslug:"pos_cafe",act:"checkCashierId",cashierUsersId:uActive};
        if(check!==null){
            if(parseInt(check.cashierUsersId)===parseInt(uActive)){
                root.find(".preload-content").hide();
                root.find(".no-access").hide();
                root.find(".original-content").fadeIn(500);
                $.post("Services",pettyParam,function(res){
                    var result = {};
                    for (const entry of res.config) {
                        result[entry.configSlug] = entry.configContent;
                    }
                    result['pettycashDateTime'] = res.pettycashDateTime;
                    if(res.pettycashType==="open"){
                        root.find(".preload-content").hide();
                        root.find(".no-access").hide();
                        root.find(".original-content").fadeIn(500);
                    }else{
                        deleteKey({key:"icms-cashier"});
                        root.find(".original-content").hide();
                        root.find(".no-access").hide();
                        root.find(".preload-content").fadeIn(500).find("#pettycashStartCash").focus();
                        root.find("#pettycashCashierId").val(result.pettycashCashierId);
                        root.find("#cashierUsersId").val(result.cashierUsersId);
                        root.find("#usersFullName").val(result.cashierName);
                    }
                },'json');
            }else{
                $.post("Services",pettyParam,function(res){
                    var result = {};
                    for (const entry of res.config) {
                        result[entry.configSlug] = entry.configContent;
                    }
                    result['pettycashDateTime'] = res.pettycashDateTime;
                    if(res.pettycashType==="open"){
                        pushData({key:"icms-cashier",data: result,replace:true});
                        root.find(".preload-content").hide();
                        root.find(".no-access").hide();
                        root.find(".original-content").fadeIn(500);
                    }else{
                        deleteKey({key:"icms-cashier"});
                        root.find(".original-content").hide();
                        root.find(".no-access").hide();
                        root.find(".preload-content").fadeIn(500).find("#pettycashStartCash").focus();
                        root.find("#pettycashCashierId").val(result.pettycashCashierId);
                        root.find("#cashierUsersId").val(result.cashierUsersId);
                        root.find("#usersFullName").val(result.cashierName);
                    }
                },'json');
            }
        }else{
            $.post("Services",pettyParam,function(res){
                var result = {};
                for (const entry of res.config) {
                    result[entry.configSlug] = entry.configContent;
                }
                result['pettycashDateTime'] = res.pettycashDateTime;
                if(res.pettycashAccess===true){
                    if(res.pettycashType==="open"){
                        pushData({key:"icms-cashier",data: result,replace:true});
                        root.find(".preload-content").hide();
                        root.find(".no-access").hide();
                        root.find(".original-content").fadeIn(500);
                    }else{
                        deleteKey({key:"icms-cashier"});
                        root.find(".original-content").hide();
                        root.find(".no-access").hide();
                        root.find(".preload-content").fadeIn(500).find("#pettycashStartCash").focus();
                        root.find("#pettycashCashierId").val(result.pettycashCashierId);
                        root.find("#cashierUsersId").val(result.cashierUsersId);
                        root.find("#usersFullName").val(result.cashierName);
                    }
                }else{
                    root.find(".no-access").fadeIn(500);
                    root.find(".preload-content").hide();
                }
            },'json');
        }

        root.find(".cashierDate").html("TGL: "+getDateNow(true));
        runClock("JAM: ",root.find(".cashierTime"));

        if(merge!==null){
            root.find("#cancelOrClear").html('<i class="fa fa-times"></i> Batal Gabung');
        }
    });

    root.one("nativeTable_first_loaded",function (e,res) {
        reloadTable(res);
    });

    root.find("#iform-appStoreCashier").on("pre_submitted",function (e,res){
        //Update Invoice Before Show Digital Receipt
        root.find("#icms-table-"+slug).trigger("reload.ace.widget");
    });

    root.find("#iform-appStoreCashier").on("submitted",function (e,res) {
        var check = getData({key:"icms-cashier"}),
            merge = getData({key:"icms-invoice-merge"}),
            invoice = getData({key:"icms-invoice-number"}),mergeObj,content;

        var readyPrint = {
            "storeName": check.receiptStoreName,
            "storeAddress": check.receiptStoreAddress,
            "storePhone":check.receiptStorePhone,
            "storeCashier":"KASIR: "+check.cashierName,
            "storeInvoice":"INVOICE: "+(merge===null?invoice[0]:merge.invoice),
            "storeFooter":[check.receiptStoreFoot1,check.receiptStoreFoot2]
        }

        var triggerReload = function(){
            //Trigger Order Type
            root.find("#orderType").val("cash").trigger("change");
            //Restore Direct print to checked
            root.find("#directPrint").prop("checked",true);
            nativeTable(slug,false,true,function(e,res){
                deleteKey({key:"icms-voucher"});
                reloadTable(res);
            });
            pushData({key:"icms-invoice-number",data: [res.invoice],replace:true});
            $.gritter.add({
                title: 'Berhasil',
                text: "Order Sukses",
                class_name: 'gritter-success'
            });
        }

        if(res.content.storeStatus==="PRE-ORDER"){
            // readyPrint['storePrintType'] = "Kitchen";
            readyPrint['storePrintType'] = "KR:1:PR1;"+"KR:1:PR2;"+"KRF:1:PR2;"+"KRD:1:PR2";
            mergeObj = Object.assign(readyPrint, res.content);
            content = JSON.stringify({"content":mergeObj});
            location.href='jprint:'+btoa(content);
            triggerReload();
        }else{
            if (res.extra.directPrint!=="on"){
                //Only Cashier
                // readyPrint['storePrintType'] = "Store";
                readyPrint['storePrintType'] = "SR:1:PR1;"+"KR:1:PR1";
                mergeObj = Object.assign(readyPrint, res.content);
                content = JSON.stringify({"content":mergeObj});
                location.href='jprint:'+btoa(content);
                triggerReload();
            }else{
                //Direct Print both Store and Kitchen
                // readyPrint['storePrintType'] = "Both";
                readyPrint['storePrintType'] = "SR:1:PR1;"+"KR:1:PR1;"+ "KR:1:PR2;"+ "KRF:1:PR2;"+ "KRD:1:PR2";

                mergeObj = Object.assign(readyPrint, res.content);
                content = JSON.stringify({"content":mergeObj});
                location.href='jprint:'+btoa(content);
                triggerReload();
            }
        }

        if(merge!==null){
            deleteKey({key:"icms-invoice-merge"});
            root.find("#cancelOrClear").html('<i class="fa fa-trash"></i> Bersihkan');
            root.find("#orderType").val("").trigger("change");
            root.find("#orderCustomerName").val("-");
            root.find("#orderRequestType").val("").trigger("change");
            root.find("#orderAdditionalInfo").val("-");
        }

        //reset form
        root.find("#orderType").val("").trigger("change");
        root.find("#orderCustomerName").val("-");
        root.find("#orderTable").val("").trigger("change");
        root.find("#orderAdditionalInfo").val("-");
    });

    root.find("#iform-pettycash").on("submitted",function(e,data){
        $.when($(this).parents(".preload-content").fadeOut(500)).done(function(){
            var result = {};
            for (const entry of data.config) {
                result[entry.configSlug] = entry.configContent;
            }
            result['pettycashDateTime'] = data.data.pettycashDateTime;
            root.find(".cashierName").html("KASIR: "+result.cashierName);
            root.find(".receiptStoreInvoiceCode").html("INVOICE: "+data.invoice);
            root.find(".receiptStoreName").html(result.receiptStoreName);
            root.find(".receiptStoreAddress").html(result.receiptStoreAddress);
            root.find(".receiptStorePhone").html(result.receiptStorePhone);
            root.find(".receiptStoreFoot1").html(result.receiptStoreFoot1);
            root.find(".receiptStoreFoot2").html(result.receiptStoreFoot2);
            root.find(".original-content").fadeIn(500);
            pushData({key:"icms-cashier",data:result,replace:true});
            pushData({key:"icms-invoice-number",data:[data.invoice],replace:true});
        });
    });

    function paymentKeypress(price,total){
        var kembali = (isNaN(parseFloat(price))?0:parseFloat(price))-total;
        root.find(".tableBayar").html(((isNaN(parseFloat(price))?0:parseFloat(price))).toLocaleString("id-ID"));
        if(parseFloat(kembali)<0){
            kembali = 0;
            root.find(".pricePaymentButton").prop("disabled",true);
        }else{
            parseFloat(kembali);
            root.find(".pricePaymentButton").prop("disabled",false);
        }
        root.find(".tableKembali").html((kembali).toLocaleString("id-ID"));
    }

    function preorderKeypress(dp,total){
        var sisa = (isNaN(parseFloat(dp))?total:total-parseFloat(dp));
        root.find(".tableDP").html((isNaN(parseFloat(dp))?0:parseFloat(dp)).toLocaleString("id-ID")).data("price",isNaN(parseFloat(dp))?0:parseFloat(dp));
        root.find(".tableSisa").html((sisa).toLocaleString("id-ID")).data("price",sisa);
    }

    root.find("#orderPricePayment").on("autoNumeric_press",function(e,data){
        var totalPrice = root.find(".tableTotalPrice").data("price");
        paymentKeypress(data,totalPrice);
    });

    root.find("#orderDownPayment").on("autoNumeric_press",function(e,data){
        var totalPrice = root.find(".tableTotalPrice").data("price");
        preorderKeypress(data,totalPrice);
    });

    root.find("#icms-table-" + slug).parents(".icms-widget").on({
        'setting.ace.widget':function (e) {
            var param = "method=actionPage&slug=appStoreCashier&app=app&appslug=pos_cafe&act=clearCart",
                merge = getData({key:"icms-invoice-merge"});
            if(merge===null){
                jConfirm("Apakah anda akan menghapus produk yang telah diinput?","Bersihkan Produk",function (r) {
                    if(r){
                        $.post("Services",param,function (res) {
                            if(res.status==="success"){
                                $.gritter.add({
                                    title: 'Bersihkan Produk',
                                    text: res.messageSuccess,
                                    class_name: 'gritter-success'
                                });
                            }else{
                                $.gritter.add({
                                    title: "Gagal",
                                    text: res.messageSuccess,
                                    class_name: 'gritter-error'
                                });
                            }
                            pushData({key:"icms-invoice-number",data:[res.invoice],replace:true});
                            root.find("#icms-table-" + slug).parents(".icms-widget").trigger("reload.ace.widget");
                            root.find("#directPrint").prop("checked",true);
                            root.find("#orderType").val("").trigger("change");
                            root.find("#orderCustomerName").val("-");
                            root.find("#orderTable").val("").trigger("change");
                            root.find("#orderAdditionalInfo").val("-");
                        },'json');
                    }
                });
            }else{
                jConfirm("Apakah anda akan membatalkan gabung order untuk invoice "+merge.invoice+"?","Gabung Order",function(r){
                    if(r){
                        deleteKey({key:"icms-invoice-merge"});
                        root.find("#cancelOrClear").html('<i class="fa fa-trash"></i> Bersihkan');
                        root.find("#orderType").val("").trigger("change");
                        root.find("#orderCustomerName").val("-");
                        root.find("#orderRequestType").val("").trigger("change");
                        root.find("#orderAdditionalInfo").val("-");
                        jAlert("Sukses menghapus cache gabung order","Gabung Order");
                    }
                });
            }
        },
        'reload.ace.widget': function (e,download) {
            e.stopPropagation();
            var $box = $(this);
            nativeTable(slug, false, true, function (x,res) {
                reloadTable(res);
                if (x) {
                    setTimeout(function () {
                        $box.trigger('reloaded.ace.widget');
                        if(download){
                            root.trigger("download_invoice");
                        }
                    }, parseInt(Math.random() * 500 + 500));
                }
            });
        },
        'reloaded.ace.widget':function (e) {
            e.stopPropagation();
        }
    });

    root.find("#icms-table-" + slug).on("tb_act_delete",function(e,res){
        reloadTable(res.data);
    });

    function reloadTable(res){
        var check = getData({key:"icms-cashier"}),
            merge = getData({key:"icms-invoice-merge"}),
            invoice = getData({key:"icms-invoice-number"}),
            voucher = getData({key:"icms-voucher"}),
            subSelling=0, subDiscount=0, cartQty=0, subTotalRaw=0,cartContent="",tax=0;

        if(invoice){
            if(invoice[0]!==res.invoice){
                invoice[0] = res.invoice;
                pushData({key:"icms-invoice-number",data: [res.invoice],replace:true});
            }
        }else{
            invoice ={};
            invoice[0] = res.invoice;
            pushData({key:"icms-invoice-number",data: [res.invoice],replace:true});
        }

        if(res.alldata!==null){
            root.find(".icms-receipt").fadeIn(500);
            root.find("#iform-appStoreCashier").fadeIn(500);
            subTotalRaw = parseFloat(res.subtotalraw);
            subSelling = parseFloat(res.subselling);
            subDiscount = parseFloat(res.subdiscount);
            $.each(res.alldata,function(i,item){
                cartQty += parseInt(item.cartQty);
                cartContent += '<tr><td class="text-left valign-top">'+(i+1)+'.</td><td class="text-left" colspan="2">'+item.productName+'</td><td class="text-right valign-top">'+(parseFloat(item.subTotalRaw)).toLocaleString("id-ID")+'</td></tr><tr><td></td><td>'+item.productCode+"</td><td>"+item.cartQty+"X"+(parseFloat(item.priceSellingPrice)).toLocaleString("id-ID")+'</td><td></td></tr>';
            });
            tax = (10/100)*subSelling;
            subSelling = subSelling+tax;
            if(voucher!==null){
                var param = {method:"actionPage",app:"app",appslug:"pos_cafe",act:"checkVoucherCode",slug:slug,code:voucher[0]};
                $.post("Services",param,function (res) {
                    if(res.data.voucherStatus==="valid" && res.data.voucherActive==="on"){
                        paymentKeypress(root.find("#orderPricePayment").autoNumeric("get"),subSelling-parseFloat(res.data.voucherValue));
                        root.find("#totalPrice").autoNumeric("set",subSelling-parseFloat(res.data.voucherValue));
                        root.find(".tableTotalPrice").html(root.find("#totalPrice").val()).data("price",subSelling-parseFloat(res.data.voucherValue)).data("oldprice",subSelling);
                        root.find(".tableVoucher").html((parseFloat(res.data.voucherValue)).toLocaleString("id-ID")).data("price",res.data.voucherValue);
                        root.find(".tableTax").html((tax).toLocaleString("id-ID")).data("price",tax);
                        root.find("#orderVoucherCode").val(res.data.voucherCode);
                        root.find("#orderVoucherValue").val(res.data.voucherValue);
                        root.find("#orderTotalPrice").val(subSelling-parseFloat(res.data.voucherValue));
                    }else{
                        paymentKeypress(root.find("#orderPricePayment").autoNumeric("get"),subSelling);
                        root.find("#totalPrice").autoNumeric("set",subSelling);
                        root.find(".tableTax").html((tax).toLocaleString("id-ID")).data("price",tax);
                        root.find(".tableTotalPrice").html(root.find("#totalPrice").val()).data("price",subSelling);
                        root.find("#orderTotalPrice").val(subSelling);
                    }
                },'json');
            }else{
                paymentKeypress(root.find("#orderPricePayment").autoNumeric("get"),subSelling);
                root.find("#totalPrice").autoNumeric("set",subSelling);
                root.find(".tableTax").html((tax).toLocaleString("id-ID")).data("price",tax);
                root.find(".tableTotalPrice").html(root.find("#totalPrice").val()).data("price",subSelling);
                root.find("#orderTotalPrice").val(subSelling);
            }

            root.find("#orderTaxValue").val(tax);
            if(check!==null){
                root.find(".cartContent").html(cartContent);
                root.find(".tableQty").html("QTY: "+cartQty);
                root.find(".tableDiscount").html(subDiscount.toLocaleString("id-ID"));
                root.find(".tableSubTotal").html(subTotalRaw.toLocaleString("id-ID"));
                root.find(".cashierName").html("KASIR: "+check.cashierName);
                root.find(".receiptStoreInvoiceCode").html("INVOICE: "+(merge===null?invoice[0]:merge.invoice));
                root.find(".receiptStoreName").html(check.receiptStoreName);
                root.find(".receiptStoreAddress").html(check.receiptStoreAddress);
                root.find(".receiptStorePhone").html(check.receiptStorePhone);
                root.find(".receiptStoreFoot1").html(check.receiptStoreFoot1);
                root.find(".receiptStoreFoot2").html(check.receiptStoreFoot2);
                //Complete Form
                root.find("#orderInvoice").val(merge===null?invoice[0]:merge.invoice);
                if(merge!==null){
                    root.find("#orderType").val("preorder").trigger("change");
                    root.find("#orderCustomerName").val(merge.customer);
                    //root.find("#orderRequestType").val(merge.request).trigger("change");
                    //controlOrder(root.find("#orderTable"),'dinein');
                    //root.find("#orderTable").val(merge.table);
                }
                root.find("#orderCashierId").val(check.pettycashCashierId);
                root.find("#orderPrice").val(subTotalRaw);
                root.find("#orderTotalDiscountValue").val(subDiscount);
                root.find('.icms-receipt').ace_scroll("end");
            }
        }else {
            paymentKeypress(0,0);
            root.find(".icms-receipt").fadeOut(500);
            root.find("#iform-appStoreCashier").fadeOut(500);
            root.find("#totalPrice").autoNumeric("set",0);
            deleteKey({key:"icms-voucher"});
            root.find(".tableVoucher").html(0).data("price",0);
            root.find("#orderPricePayment").val("");
            root.find("#orderVoucherCode").val("");
            root.find("#directPrint").prop("checked",true);
            root.find("#orderType").val("").trigger("change");
            root.find("#orderCustomerName").val("-");
            root.find("#orderTable").val("").trigger("change");
            root.find("#orderAdditionalInfo").val("-");
        }
    }

    root.find(".typeahead").on("clicked",function (e) {
        root.find(".typeahead").one("keyup",function (e){
            if(e.keyCode===13){
                var check = getData({key:"icms-cashier"}),
                    $form = $(this).parents("form"),
                    data = $form.serialize(),
                    param = $form.data("param"),
                    Qty = $form.find("#cartQuantity"),
                    checkQty = Qty.val()===""?0:Qty.val();
                if(checkQty>0){
                    $.post("Services",$.param(param)+"&"+data+"&cartCashierId="+check.pettycashCashierId,function(res){
                        if(res!==null){
                            if(res.status==="success"){
                                $.gritter.add({
                                    title: 'Berhasil',
                                    text: res.messageSuccess,
                                    class_name: 'gritter-success'
                                });
                            }else{
                                $.gritter.add({
                                    title: "Gagal",
                                    text: res.messageSuccess,
                                    class_name: 'gritter-error'
                                });
                            }
                            pushData({key:"icms-invoice-number",data: [res.invoice],replace:true});
                            root.find("#icms-table-" + slug).parents(".icms-widget").trigger("reload.ace.widget");
                        }else{
                            $.gritter.add({
                                title: "Gagal",
                                text: 'Stok kosong, silahkan cek stok produk anda!',
                                class_name: 'gritter-error'
                            });
                        }
                    },"json");
                    $(this).parents(".cashier-form").trigger('reset');
                }else{
                    Qty.focus();
                }
            }
        });
    });

    root.find('.icms-receipt').ace_scroll('modify',{
        size: 345,
        observeContent: true,
        touchDrag: true,
        touchSwipe: true,
        styleClass: 'scroll-margin scroll-dark'
    });

    function controlOrder(el,type){
        var optId;
        if(type!==undefined){
            $.each(el.find("option"),function (i,opt) {
                optId = ($(opt).data("cdata"))!==undefined?($(opt).data("cdata")).tableroot:""
                if(optId!=="" && optId!==type){
                    el.val("").find('option[value=""]').attr('selected',true);
                    $(opt).hide();
                }else{
                    el.val("").find('option[value=""]').attr('selected',true);
                    $(opt).show();
                }
            });

            if(optId===""){
                el.val("").find('option[value=""]').attr('selected',true);
            }
        }else{
            el.val("").find('option[value=""]').attr('selected',true);
            el.val("").find('option').attr('style',"display:block");
        }
    }

    root.find("#orderRequestType").change(function(e){
        controlOrder(root.find("select[name=orderTable]"),this.value);
    });

    root.find("#orderType").change(function (e) {
       var bayar = root.find(".orderPricePaymentContainer"),
           tbBayar = root.find(".tableBayar"),
           tbKembali = root.find(".tableKembali"),
           tbStatus = root.find(".tableStatus"),
           tbStatusPO = root.find(".tableStatusPO"),
           podisContainer = root.find(".po-disabled"),
           directPrint = root.find("#directPrint"),
           totalPrice = root.find(".tableTotalPrice").data("price");
        switch (e.target.value){
            case "cash":
                podisContainer.show();
                bayar.show();
                bayar.find("input").val("");
                tbBayar.html("0").data("price",0).parents("tr").show();
                tbKembali.html("0").data("price",0).parents("tr").show();
                tbStatus.html("LUNAS");
                tbStatusPO.html("");
                directPrint.prop("disabled",false);
                root.find(".pricePaymentButton").prop("disabled",true);
                root.find("#orderStatus").val("success");
                root.find("#orderPricePayment").val("");
                root.find("input[name=orderPricePayment]").val("");
                //controlOrder(root.find("select[name=orderRequestType]"));
                break;

            case "card":
                podisContainer.show();
                bayar.show();
                tbStatusPO.html("");
                directPrint.prop("disabled",false);
                tbBayar.html("0").data("price",0).parents("tr").show();
                tbKembali.html("0").data("price",0).parents("tr").show();
                tbStatus.html("LUNAS (NON TUNAI)");
                root.find(".pricePaymentButton").prop("disabled",true);
                root.find("#orderStatus").val("success");
                root.find("#orderPricePayment").autoNumeric("set",totalPrice);
                root.find("input[name=orderPricePayment]").val(totalPrice);
                paymentKeypress(totalPrice,totalPrice);
                //controlOrder(root.find("select[name=orderRequestType]"));
                break;

            case "preorder":
                podisContainer.hide();
                bayar.find("input").val("");
                bayar.hide();
                tbBayar.html("0").data("price",0).parents("tr").hide();
                tbKembali.html("0").data("price",0).parents("tr").hide();
                tbStatusPO.html("PRE-ORDER");
                directPrint.prop("disabled",true);
                preorderKeypress(0,totalPrice);
                root.find(".pricePaymentButton").prop("disabled",false);
                root.find("#orderStatus").val("process");
                root.find("#orderPricePayment").val("");
                root.find("input[name=orderPricePayment]").val("");
                //controlOrder(root.find("select[name=orderRequestType]"),"dinein");
                break;

            default:
                podisContainer.show();
                bayar.find("input").val("");
                bayar.hide();
                tbStatusPO.html("");
                directPrint.prop("disabled",false);
                tbBayar.html("0").data("price",0).parents("tr").hide();
                tbKembali.html("0").data("price",0).parents("tr").hide();
                tbStatus.html("LUNAS");
                root.find(".pricePaymentButton").prop("disabled",true);
                root.find("#orderStatus").val("success");
                root.find("#orderPricePayment").val("");
                root.find("input[name=orderPricePayment]").val("");
                //controlOrder(root.find("select[name=orderRequestType]"));
                break;
        }
    });

    root.find("#btnSearchProduct").click(function(e){
        var $this = this,
            param = {method:"actionPage",app:"app",appslug:"pos_cafe",act:"loadAllMenu",slug:slug};
        $.post("Services",param,function(res){
            $($this).icmsModal({
                padding:"10px",
                loadInFullscreen: true,
                background:"#b9d3f3",
                title:"Daftar Menu Roemah Lamdoek",
                content: JSON.stringify(res)
            });
        },'json');
    });

    root.find(".clearVoucherCode").click(function(e){
        var orderType = root.find("#orderType").val(),
            totalPrice = parseFloat(root.find("#totalPrice").autoNumeric("get")),
            returnPrice = root.find(".tableTotalPrice").data("oldprice");
        if(returnPrice!==undefined){
            root.find("#totalPrice").autoNumeric("set",returnPrice);
            root.find(".tableTotalPrice").html(root.find("#totalPrice").val()).data("price",returnPrice).removeData("oldprice");
            root.find(".tableVoucher").html(0).data("price",0);
            root.find("#orderVoucherValue").val(0);
            root.find("#orderTotalPrice").val(returnPrice);
            root.find("#orderVoucherCode").val("");
            deleteKey({key:"icms-voucher"});
            paymentKeypress(returnPrice,totalPrice);
            if(orderType==="card"){root.find("#orderPricePayment").autoNumeric("set",returnPrice);root.find("input[name=orderPricePayment]").val(returnPrice);}
        }
    });

    root.find(".checkVoucherCode").click(function (e) {
       var voucherCode = root.find($(this).data("send")),
            totalPrice = parseFloat(root.find("#totalPrice").autoNumeric("get")),
            param = {method:"actionPage",app:"app",appslug:"pos_cafe",act:"checkVoucherCode",slug:slug,code:voucherCode.val()},
           voucher = getData({key:"icms-voucher"}),returnPrice,
           orderType = root.find("#orderType").val(),
           cekVoucher = function () {
               $.post("Services",param,function (res) {
                   if(res.data!==null){
                       if(res.data.voucherStatus==="valid" && res.data.voucherActive==="on"){
                           jCustomConfirm("Kode Voucer: "+res.data.voucherCode+"\nNama Voucer: "+res.data.voucherName+"\nNilai Voucer: "+(parseFloat(res.data.voucherValue)).toLocaleString("id-ID")+"\nStatus: "+res.data.voucherStatus,"Cek Voucer","Gunakan","Batal",function(r){
                               if(r){
                                   var newPrice = totalPrice-parseFloat(res.data.voucherValue);
                                   root.find("#totalPrice").autoNumeric("set",newPrice);
                                   root.find(".tableTotalPrice").html(root.find("#totalPrice").val()).data("price",newPrice).data("oldprice",totalPrice);
                                   root.find(".tableVoucher").html((parseFloat(res.data.voucherValue)).toLocaleString("id-ID")).data("price",res.data.voucherValue);
                                   pushData({key:"icms-voucher",data:[res.data.voucherCode],replace:true});
                                   root.find("#orderVoucherCode").val(res.data.voucherCode);
                                   root.find("#orderVoucherValue").val(res.data.voucherValue);
                                   root.find("#orderTotalPrice").val(newPrice);
                                   paymentKeypress(newPrice,totalPrice);
                                   if(orderType==="card"){root.find("#orderPricePayment").autoNumeric("set",newPrice);root.find("input[name=orderPricePayment]").val(newPrice);}
                               }
                           });
                       }else{
                           jAlert("Maaf, Kode Voucer "+res.data.voucherCode+" telah kadaluarsa dan tidak bisa digunakan lagi!","Cek Voucer",function (r) {
                               if(r){
                                   returnPrice = root.find(".tableTotalPrice").data("oldprice");
                                   if(returnPrice!==undefined){
                                       root.find("#totalPrice").autoNumeric("set",returnPrice);
                                       root.find(".tableTotalPrice").html(root.find("#totalPrice").val()).data("price",returnPrice).removeData("oldprice");
                                       root.find(".tableVoucher").html(0).data("price",0);
                                       root.find("#orderVoucherValue").val(0);
                                       root.find("#orderTotalPrice").val(returnPrice);
                                       paymentKeypress(returnPrice,totalPrice);
                                       if(orderType==="card"){root.find("#orderPricePayment").autoNumeric("set",returnPrice);root.find("input[name=orderPricePayment]").val(returnPrice);}
                                   }
                               }
                           });
                       }
                   }else{
                       jAlert("Maaf, Kode Voucer "+voucherCode.val()+" tidak dapat ditemukan dalam sistem kami!","Cek Voucer",function (r) {
                           if(r){
                               returnPrice = root.find(".tableTotalPrice").data("oldprice");
                               if(returnPrice!==undefined){
                                   root.find("#totalPrice").autoNumeric("set",returnPrice);
                                   root.find(".tableTotalPrice").html(root.find("#totalPrice").val()).data("price",returnPrice).removeData("oldprice");
                                   root.find(".tableVoucher").html(0).data("price",0);
                                   root.find("#orderVoucherValue").val(0);
                                   root.find("#orderTotalPrice").val(returnPrice);
                                   paymentKeypress(returnPrice,totalPrice);
                                   if(orderType==="card"){root.find("#orderPricePayment").autoNumeric("set",returnPrice);root.find("input[name=orderPricePayment]").val(returnPrice);}
                               }
                           }
                       });
                   }
               },'json');
           };
       if(voucherCode.val()!==""){
           if(voucher===null){
               cekVoucher();
           }else{
               if(voucher[0]===voucherCode.val()){
                   jAlert("Ini adalah kode voucer yang anda gunakan saat ini","Cek Voucer");
               }else{
                   jCustomConfirm("Apakah anda akan mengganti kode voucher yang telah tersimpan untuk order ini?","Cek Voucer","Ganti","Batal",function(r){
                       if(r){
                           deleteKey({key:"icms-voucher"});
                           cekVoucher();
                       }else{
                           root.find("#orderVoucherCode").val(voucher[0]);
                       }
                   });
               }
           }
       }else{
           voucherCode.focus();
       }
    });

    root.find(".preorderList").click(function (e) {
        $('.addMainTab[data-slug="appStorePreorder"]').click();
    });

    root.find("#iform-appStoreCashier").on("prevent-status",function (e,data){
        var orderType = root.find("#orderType").val();
        if(orderType==="preorder"){
            if(data===false){
                root.find(".pricePaymentButton").prop("disabled",false);
            }
        }
    });

    function loadShortcut(){
        var param = {method:"actionPage",app:"app",appslug:"pos_cafe",act:"load_shortcutMenu",slug:slug},
            template="";
        $.post("Services",param,function(res){
            if(res.data!==null){
                $.each(res.data,function(i,item){
                    template += '<button class="btn btn-app btn-light btn-xs tooltip-success shortcutButton" data-rel="tooltip" data-placement="bottom" title data-original-title="'+item.productName+'" data-id="'+item.id+'"><strong>'+item.shortcutOrder+'</strong><br>'+item.productCode+'</button>';
                });
                root.find(".shortcutMenuContainer").parent(".row").show();
                root.find(".shortcutMenuContainer").html(template).show();
                root.find('[data-rel=tooltip]').tooltip();
                root.find(".shortcutButton").click(function(e){
                    var check = getData({key:"icms-cashier"}),
                        param = {"method":"actionPage",app:"app","slug":"appStoreCashier","appslug":"pos_cafe","act":"create_appStoreCashier","cartCashierId":check.pettycashCashierId,"cartProductId":$(this).data("id"),"cartQuantity":"1"};
                    $.post("Services",param,function(res){
                        if(res!==null){
                            if(res.status==="success"){
                                $.gritter.add({
                                    title: 'Berhasil',
                                    text: res.messageSuccess,
                                    class_name: 'gritter-success'
                                });
                            }else{
                                $.gritter.add({
                                    title: "Gagal",
                                    text: res.messageSuccess,
                                    class_name: 'gritter-error'
                                });
                            }
                            pushData({key:"icms-invoice-number",data: [res.invoice],replace:true});
                            root.find("#icms-table-" + slug).parents(".icms-widget").trigger("reload.ace.widget");
                        }else{
                            $.gritter.add({
                                title: "Gagal",
                                text: 'Stok kosong, silahkan cek stok produk anda!',
                                class_name: 'gritter-error'
                            });
                        }
                    },"json");
                });
            }else{
                root.find(".shortcutMenuContainer").parent(".row").hide();
            }
        },'json');
    }
    loadShortcut();
});