/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-27
 * Time: 6:19:37 AM
 * Filename: appDatabaseProfile.js
 */
$(function () {
    var slug = "appDatabaseProfile",
        root = $(".icms-container").find("#" + slug + ".tab-pane"),
        apps = getData({key:"icms-app-list"});

    if(apps.libs['timeago']!==undefined){initAssets(apps.libs['timeago']);}

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

    timeago.render(document.querySelectorAll("#" + slug + ".tab-pane"+" .joined, #" + slug + ".tab-pane"+" .lastOnline"));

    root.find("button.password").click(function(event) {
        var input = $(this).parents('.input-group').find('input');
        input.toggleClass("show-password");
        if(input.hasClass("show-password")){
            $(this).html("<i class='fa fa-eye-slash'></i>");
            input.attr('type', 'text');
        }else{
            $(this).html("<i class='fa fa-eye'></i>");
            input.attr('type', 'password');
        }
    });

    root.find(".btn.update_profile").click(function(e){
        var param = $(this).data("param");
        param['id'] = $(this).data("id");
        $.post("Services",param,function (res) {
            populateForm({
                slug:slug,
                data:res.data,
                id:param.id,
                form:""
            });
        },'json');
        $(this).parents(".main-col").removeClass("col-lg-12").addClass("col-lg-9");
        root.find(".update-col").fadeIn(500);
    });

    root.find(".btn.close_update_profile").click(function(e){
        $(this).parents(".update-col").fadeOut(500, function() {
            root.find(".main-col").removeClass("col-lg-9").addClass("col-lg-12");
        });
    });

    root.find('#usersAvatar').ace_file_input({
        style: 'well',
        btn_choose: 'Tarik file ke sini atau klik untuk upload',
        btn_change: null,
        no_icon: 'ace-icon fa fa-image',
        droppable: true,
        thumbnail: 'fit',
        icon_remove:null,
        before_change:function (files, dropped){
            var acceptfiles = [],
                denyfile = [],
                allowExt = {"jpg":["image/jpg","image/jpeg"], "jpeg":"image/jpeg", "png":"image/png","gif":"image/gif","bmp":"image/bmp","webp":"image/webp"},
                getExt = function(filename){
                    return filename.split('.').pop();
                }
            $.each(files,function(i,file){
                switch (typeof allowExt[getExt((file.name).toLowerCase())]) {
                    case "object":
                        $.each(allowExt[getExt((file.name).toLowerCase())],function (j,mime) {
                            if(mime===file.type){
                                acceptfiles.push(file);
                            }
                        })
                        break;
                    case "string":
                        acceptfiles.push(file);
                        break;
                    default:
                        denyfile.push(file.name);
                        break;
                }
            });

            if(denyfile.length>0){
                $.gritter.add({
                    title: "File Dilarang",
                    text: denyfile.join("<br>"),
                    class_name: 'gritter-error'
                });
            }
            return acceptfiles;
        }
    });

    // root.find('#profile-feed-1').ace_scroll({
    //     size: 320,
    //     mouseWheelLock: true,
    //     alwaysVisible : true
    // });
});