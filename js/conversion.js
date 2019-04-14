$(document).ready(function () {
	var actionsExtract = {
		init: function () {
            /*var App = OCA.Files_External.App;
            console.log(OC.MountConfig);

            OCA.Files_External.StatusManager.getMountPointList(function() {
                 //console.log(this);
            });
    var fileList;*/
    

    //fileList = App.initList($('#app-content-extstoragemounts'));

			OCA.Files.fileActions.registerAction({
				name: 'convert',
				displayName: 'Convert into',
				mime: 'video',
				permissions: OC.PERMISSION_READ,
				type: OCA.Files.FileActions.TYPE_DROPDOWN,
				iconClass: 'icon-extract',
				actionHandler: function (filename, context) {
                    var self = this;
                    var override = false;
                    var title = "Titre";
                    $('body').append(
                        '<div id="linkeditor_overlay" class="oc-dialog-dim"></div>'
                        +'<div id="linkeditor_container" class="oc-dialog" style="position: fixed;">'
                            +'<div id="linkeditor">'
                        +'</div>'
                    );
                    $('#linkeditor').append(
                    '<div class="urledit push-bottom">'
                    +'<a class="oc-dialog-close" id="btnClose"></a>'
                        +'<h2 class="oc-dialog-title">' + filename + '</h2>'
                        +'<p class="urldisplay" id="text">'
                            +t('video_converter', 'Choose the output format')
                            +' <em></em>'
                        +'</p>'
                        +'<div class="sk-circle" style="display:none" id="loading"><div class="sk-circle1 sk-child"></div><div class="sk-circle2 sk-child"></div><div class="sk-circle3 sk-child"></div><div class="sk-circle4 sk-child"></div><div class="sk-circle5 sk-child"></div><div class="sk-circle6 sk-child"></div><div class="sk-circle7 sk-child"></div><div class="sk-circle8 sk-child"></div><div class="sk-circle9 sk-child"></div><div class="sk-circle10 sk-child"></div><div class="sk-circle11 sk-child"></div><div class="sk-circle12 sk-child"></div></div>'
                        + '<div id="checkOverride">'
                        +'<input type="checkbox" id="override" name="override">'
                        +'<label for="override">override ?</label>'
                        +'</div>'
                    +'</div>'
                    +'<div class="oc-dialog-buttonrow boutons">'
                        +'<a class="button primary" id="mp4">' + t('video_converter', '.MP4') + '</a>'
                        +'<a class="button primary" id="avi">' + t('video_converter', '.AVI') + '</a>'
                        +'</div>'
                );
                var finished = false;
                document.getElementById("btnClose").addEventListener("click", function(){
                    close();
                    finished = true;
                });  
                document.getElementById("override").addEventListener("click", function (){
                    override = !override;
                }); 
                document.getElementById("linkeditor_overlay").addEventListener("click", function(){
                    close();
                    finished = true;
                }); 
                document.getElementById("avi").addEventListener("click", function ($element){
                    console.log($element.target.id);
                    if (context.fileInfoModel.attributes.mountType == "external"){
                        var data = {
                            nameOfFile: filename,
                            directory: '/'+context.dir.split('/').slice(2).join('/'),
                            external : 1,
                            type: $element.target.id,
                            override : override
                        };
                    }else{
                        var data = {
                            nameOfFile: filename,
                            directory: context.dir,
                            external : 0,
                            type: $element.target.id,
                            override : override
                        };
                    }
                    console.log("test");
                    $.ajax({
                        type: "POST",
                        async: "true",
                        url: OC.filePath('video_converter', 'ajax','convertHere.php'),
                        data: data,
                        beforeSend: function() {
                            document.getElementById("loading").style.display= "block";
                            document.getElementById("checkOverride").style.display= "none";
                            document.getElementById("text").style.display= "none";

                        },
                        success: function() {
                            context.fileList.reload();
                            close();
                        }
                    });
                }); 
                document.getElementById("mp4").addEventListener("click", function ($element){
                    console.log($element.target.id);
                    if (context.fileInfoModel.attributes.mountType == "external"){
                        var data = {
                            nameOfFile: filename,
                            directory: '/'+context.dir.split('/').slice(2).join('/'),
                            external : 1,
                            type: $element.target.id,
                            override : override
                        };
                    }else{
                        var data = {
                            nameOfFile: filename,
                            directory: context.dir,
                            external : 0,
                            type: $element.target.id,
                            override : override
                        };
                    }
                    $.ajax({
                        type: "POST",
                        async: "false",
                        url: OC.filePath('video_converter', 'ajax','convertHere.php'),
                        data: data,
                        beforeSend: function() {
                            document.getElementById("loading").style.display= "block";
                            document.getElementById("checkOverride").style.display= "none";
                            document.getElementById("text").style.display= "none";

                        },
                        success: function() {
                            context.fileList.reload();
                           close();
                        }
                    });
                });
            }
            });
            
        }
    }
    function close(){
        $('#linkeditor_container').remove();
        $('#linkeditor_overlay').remove();
    }
	actionsExtract.init();
});