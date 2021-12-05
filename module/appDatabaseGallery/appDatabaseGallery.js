/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2021-12-05
 * Time: 8:07:07 AM
 * Filename: appDatabaseGallery.js
 */
$(function () {
    var slug = "appDatabaseGallery",
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

    root.find('#galleryImages').ace_file_input({
        style: 'well',
        btn_choose: 'Tarik file ke sini atau klik untuk upload',
        btn_change: null,
        no_icon: 'ace-icon fa fa-cloud-upload',
        droppable: true,
        thumbnail: 'small',
        icon_remove:'fa fa-trash-o',
        before_change:function (files, dropped){
            var acceptfiles = [],
                denyfile = [],
                allowExt = {"jpg":["image/jpg","image/jpeg"], "jpeg":"image/jpeg", "png":"image/png","gif":"image/gif","bmp":"image/bmp"},
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
                        });
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

    root.find(".icms-form").on("submitted",function (e) {
        nativeTable(slug,false,true);
    });
});