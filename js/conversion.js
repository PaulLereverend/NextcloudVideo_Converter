$(document).ready(function () {
	var actionsExtract = {
		init: function () {
			OCA.Files.fileActions.registerAction({
				name: 'convert',
				displayName: 'Convert into',
				mime: 'video',
				permissions: OC.PERMISSION_READ,
				type: OCA.Files.FileActions.TYPE_DROPDOWN,
				iconClass: 'icon-convert',
				actionHandler: function (filename, context) {

                    var a = context.$file[0].children[1].children[0].children[0].innerHTML;
                    var b = 'background-repeat:no-repeat;margin-right:1px;display: block;width: 40px;height: 32px;white-space: nowrap;border-image-repeat: stretch;border-image-slice: initial;background-size: 32px;';
                    var position = 30;
                    var output = [a.slice(0, position), b, a.slice(position)].join('');

                    var self = this;
                    var override = false;
                    var preset = "medium";
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
                        +'<h2 class="oc-dialog-title" style="display:flex;margin-right:30px;">' 
                        +output
                        +filename 
                        + '</h2>'
                        +'<div class="sk-circle" style="display:none" id="loading"><div class="sk-circle1 sk-child"></div><div class="sk-circle2 sk-child"></div><div class="sk-circle3 sk-child"></div><div class="sk-circle4 sk-child"></div><div class="sk-circle5 sk-child"></div><div class="sk-circle6 sk-child"></div><div class="sk-circle7 sk-child"></div><div class="sk-circle8 sk-child"></div><div class="sk-circle9 sk-child"></div><div class="sk-circle10 sk-child"></div><div class="sk-circle11 sk-child"></div><div class="sk-circle12 sk-child"></div></div>'
                        + '<div id="checkOverride">'
                        +'<p class="urldisplay" id="labelPreset" style="display:inline-block; margin-right:5px;">'
                        +'Preset'
                        +'</p>'
                        + '<select id="preset">'
                        +'<option value="ultrafast">UltraFast</option>'
                        +'<option value="superfast">SuperFast</option>'
                        +'<option value="veryfast">VeryFast</option>'
                        +'<option value="faster">Faster</option>'
                        +'<option value="fast">Fast</option>'
                        +'<option value="medium" selected>Medium (default)</option>'
                        +'<option value="slow">Slow</option>'
                        +'<option value="veryslow">VerySlow</option>'
                        +'</select>'
                        +'<br>'
                        +'<p id="note" style="margin-bottom:20px;">note: faster means bigger size</p>'
                        +'<input type="checkbox" id="override" name="override">'
                        +'<label for="override">override</label>'
                        +'</div>'
                        +'<p class="urldisplay" id="text">'
                            +t('video_converter', 'Choose the output format')
                            +' <em></em>'
                        +'</p>'
                    +'</div>'
                    +'<div class="oc-dialog-buttonrow boutons" id="buttons">'
                        +'<a class="button primary" id="mp4">' + t('video_converter', '.MP4') + '</a>'
                        +'<a class="button primary" id="avi">' + t('video_converter', '.AVI') + '</a>'
                        +'<a class="button primary" id="m4v">' + t('video_converter', '.M4V') + '</a>'
                        +'<a class="button primary" id="webm">' + t('video_converter', '.WEBM') + '</a>'

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
                document.getElementById("preset").addEventListener("change", function (element){
                    console.log(element.srcElement.value);
                    preset = element.srcElement.value;
                }); 
                document.getElementById("linkeditor_overlay").addEventListener("click", function(){
                    close();
                    finished = true;
                }); 
                document.getElementById("avi").addEventListener("click", function ($element){
                    if (context.fileInfoModel.attributes.mountType == "external"){
                        var data = {
                            nameOfFile: filename,
                            directory: '/'+context.dir.split('/').slice(2).join('/'),
                            external : 1,
                            type: $element.target.id,
                            preset: preset,
                        };
                    }else{
                        var data = {
                            nameOfFile: filename,
                            directory: context.dir,
                            external : 0,
                            type: $element.target.id,
                            preset: preset,
                        };
                    }
                    $.ajax({
                        type: "POST",
                        async: "true",
                        url: OC.filePath('video_converter', 'ajax','convertHere.php'),
                        data: data,
                        beforeSend: function() {
                            document.getElementById("loading").style.display= "block";
                            document.getElementById("checkOverride").style.display= "none";
                            document.getElementById("text").style.display= "none";
                            document.getElementById("preset").style.display= "none";
                            document.getElementById("labelPreset").style.display= "none";
                            document.getElementById("note").style.display= "none";
                            document.getElementById("buttons").setAttribute('style', 'display: none !important');
                        },
                        success: function() {
                            this.filesClient = OC.Files.getClient();
                            if (override){
                                this.filesClient.remove(context.dir+"/"+filename);
                            }
                            context.fileList.reload();
                            close();
                        }
                    });
                }); 
                document.getElementById("mp4").addEventListener("click", function ($element){
                    if (context.fileInfoModel.attributes.mountType == "external"){
                        var data = {
                            nameOfFile: filename,
                            directory: '/'+context.dir.split('/').slice(2).join('/'),
                            external : 1,
                            type: $element.target.id,
                            preset: preset,
                        };
                    }else{
                        var data = {
                            nameOfFile: filename,
                            directory: context.dir,
                            external : 0,
                            type: $element.target.id,
                            preset: preset,
                        };
                    }
                    $.ajax({
                        type: "POST",
                        async: "true",
                        url: OC.filePath('video_converter', 'ajax','convertHere.php'),
                        data: data,
                        beforeSend: function() {
                            document.getElementById("loading").style.display= "block";
                            document.getElementById("checkOverride").style.display= "none";
                            document.getElementById("text").style.display= "none";
                            document.getElementById("preset").style.display= "none";
                            document.getElementById("labelPreset").style.display= "none";
                            document.getElementById("note").style.display= "none";
                            document.getElementById("buttons").style.display= "none";

                        },
                        success: function() {
                            this.filesClient = OC.Files.getClient();
                            if (override){
                                this.filesClient.remove(context.dir+"/"+filename);
                            }
                            context.fileList.reload();
                            close();
                        }
                    });
                }); 
                document.getElementById("m4v").addEventListener("click", function ($element){
                    if (context.fileInfoModel.attributes.mountType == "external"){
                        var data = {
                            nameOfFile: filename,
                            directory: '/'+context.dir.split('/').slice(2).join('/'),
                            external : 1,
                            type: $element.target.id,
                            preset: preset,
                        };
                    }else{
                        var data = {
                            nameOfFile: filename,
                            directory: context.dir,
                            external : 0,
                            type: $element.target.id,
                            preset: preset,
                        };
                    }
                    $.ajax({
                        type: "POST",
                        async: "true",
                        url: OC.filePath('video_converter', 'ajax','convertHere.php'),
                        data: data,
                        beforeSend: function() {
                            document.getElementById("loading").style.display= "block";
                            document.getElementById("checkOverride").style.display= "none";
                            document.getElementById("text").style.display= "none";
                            document.getElementById("preset").style.display= "none";
                            document.getElementById("labelPreset").style.display= "none";
                            document.getElementById("note").style.display= "none";
                            document.getElementById("buttons").style.display= "none";

                        },
                        success: function() {
                            this.filesClient = OC.Files.getClient();
                            if (override){
                                this.filesClient.remove(context.dir+"/"+filename);
                            }
                            context.fileList.reload();
                            close();
                        }
                    });
                });
                document.getElementById("webm").addEventListener("click", function ($element){
                    if (context.fileInfoModel.attributes.mountType == "external"){
                        var data = {
                            nameOfFile: filename,
                            directory: '/'+context.dir.split('/').slice(2).join('/'),
                            external : 1,
                            type: $element.target.id,
                            preset: preset,
                        };
                    }else{
                        var data = {
                            nameOfFile: filename,
                            directory: context.dir,
                            external : 0,
                            type: $element.target.id,
                            preset: preset,
                        };
                    }
                    $.ajax({
                        type: "POST",
                        async: "true",
                        url: OC.filePath('video_converter', 'ajax','convertHere.php'),
                        data: data,
                        beforeSend: function() {
                            document.getElementById("loading").style.display= "block";
                            document.getElementById("checkOverride").style.display= "none";
                            document.getElementById("text").style.display= "none";
                            document.getElementById("preset").style.display= "none";
                            document.getElementById("labelPreset").style.display= "none";
                            document.getElementById("note").style.display= "none";
                            document.getElementById("buttons").style.display= "none";

                        },
                        success: function() {
                            this.filesClient = OC.Files.getClient();
                            if (override){
                                this.filesClient.remove(context.dir+"/"+filename);
                            }
                            context.fileList.reload();
                            close();
                        }
                    });
                });
            }
            });
            
        },
    }
   
    function close(){
        $('#linkeditor_container').remove();
        $('#linkeditor_overlay').remove();
    }
	actionsExtract.init();
});