$(document).ready(function () {
	var actionsExtract = {
		init: function () {
			OCA.Files.fileActions.registerAction({
				name: 'convert',
				displayName: 'Convert into',
				mime: 'video',
				permissions: OC.PERMISSION_UPDATE,
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
                    var priority = "0";
                    var title = "Titre";
                    var vcodec = null;
                    var vbitrate = null;
                    var scaling = null;
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
                        +'<div style="text-align:center; display:none; margin-top: 10px;" id="noteLoading">'
                        +'<p>note: it can be very long depending on your hardware and the preset you choose. You can safely close this window</p>'
                        +'</div>'
                        +'<div id="params">'
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
                        +'<p id="note">note: faster means worse quality or bigger size</p>'
                        +'<br>'
                        +'<p class="urldisplay" id="labelPreset" style="display:inline-block; margin-right:5px;">'
                        +'Priority'
                        +'</p>'
                        + '<select id="priority" style="margin-bottom: 10px;">'
                        +'<option value="-10">High</option>'
                        +'<option value="0">Normal (default)</option>'
                        +'<option value="10" selected>Low</option>'
                        +'</select>'
                        +'<br>'
                        +'<p class="urldisplay" id="labelPreset" style="display:inline-block; margin-right:5px;">'
                        +'Codec'
                        +'</p>'
                        +'<select id="vcodec" style="margin-bottom: 10px;">'
                        +'<option value="none">Auto</option>'
                        +'<option value="x264">H264</option>'
                        +'<option value="x265">HEVC</option>'
                        +'</select>'
                        +'<p class="urldisplay" id="labelPreset" style="display:inline-block; margin-right:5px;">'
                        +'Target bitrate'
                        +'</p>'
                        +'<select id="vbitrate" style="margin-bottom: 10px;">'
                        +'<option value="none">Auto</option>'
                        +'<option value="1">1k</option>'
                        +'<option value="2">2k</option>'
                        +'<option value="3">3k</option>'
                        +'<option value="4">4k</option>'
                        +'<option value="5">5k</option>'
                        +'<option value="6">6k</option>'
                        +'<option value="7">7k</option>'
                        +'</select>'
                        +'<p class="urldisplay" id="labelPreset" style="display:inline-block; margin-right:5px;">'
                        +'kbit/s'
                        +'</p>'
                        +'<br>'
                        +'<p class="urldisplay" id="labelPreset" style="display:inline-block; margin-right:5px;">'
                        +'Scale to'
                        +'</p>'
                        +'<select id="scale" style="margin-bottom: 10px;">'
                        +'<option value="none">Keep</option>'
                        +'<option value="wxga">WXGA (1280x720)</option>'
                        +'<option value="hd">HD (1368x768)</option>'
                        +'<option value="fhd">FHD (1920x1080)</option>'
                        +'<option value="uhd">4K (3840x2160)</option>'
                        +'<option value="600">Keep aspect 600 (Wx600)</option>'
                        +'<option value="720">Keep aspect 720 (Wx720)</option>'
                        +'<option value="1080">Keep aspect 1080 (Wx1080)</option>'
                        +'</select>'
                        +'</div>'
                        +'<p class="urldisplay" id="text" style="display: inline; margin-right: 10px;">'
                            +t('video_converter', 'Choose the output format')
                            +' <em></em>'
                        +'</p>'
                        +'<div id="overrideDiv">'
                        +'<input type="checkbox" id="override" name="override">'
                        +'<label for="override">override</label>'
                        +'</div>'
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
                document.getElementById("priority").addEventListener("change", function (element){
                    console.log(element.srcElement.value);
                    priority = element.srcElement.value;
                });
                document.getElementById("vcodec").addEventListener("change", function (element){
                    console.log(element.srcElement.value);
                    vcodec = element.srcElement.value;
                    if (vcodec === "none"){
                        vcodec = null;
                    }
                });
                document.getElementById("vbitrate").addEventListener("change", function (element){
                    vbitrate = element.srcElement.value;
                    if (vbitrate === "none"){
                        vbitrate = null;
                    }
                });
                document.getElementById("scale").addEventListener("change", function (element){
                    scaling = element.srcElement.value;
                    if (scaling === "none"){
                        scaling = null;
                    }
                });
                document.getElementById("linkeditor_overlay").addEventListener("click", function(){
                    close();
                    finished = true;
                }); 
                var fileExt = filename.split('.').pop();
                var types = ['avi', 'mp4', 'm4v', 'webm'];
                types.forEach(type => {
                    if(type == fileExt){
                        document.getElementById(type).setAttribute('style', 'background-color: lightgray; border-color:lightgray;');
                    }else{
                        document.getElementById(type).addEventListener("click", function ($element){
                            if (context.fileInfoModel.attributes.mountType == "external"){
                                var data = {
                                    nameOfFile: filename,
                                    directory: context.dir,
                                    external : 1,
                                    type: $element.target.id,
                                    preset: preset,
                                    priority: priority,
                                    codec: vcodec,
                                    vbitrate: vbitrate,
                                    scale: scaling,
                                    override : override,
                                    mtime: context.fileInfoModel.attributes.mtime,
                                };
                            }else{
                                var data = {
                                    nameOfFile: filename,
                                    directory: context.dir,
                                    external : 0,
                                    type: $element.target.id,
                                    preset: preset,
                                    priority: priority,
                                    codec: vcodec,
                                    vbitrate: vbitrate,
                                    scale: scaling,
                                    shareOwner : context.fileList.dirInfo.shareOwnerId,
                                };
                            }
                            var tr = context.fileList.findFileEl(filename);
                            context.fileList.showFileBusyState(tr, true);
                            $.ajax({
                                type: "POST",
                                async: "true",
                                url: OC.filePath('video_converter', 'ajax','convertHere.php'),
                                data: data,
                                beforeSend: function() {
                                    document.getElementById("loading").style.display= "block";
                                    document.getElementById("noteLoading").style.display= "block";
                                    document.getElementById("overrideDiv").style.display= "none";
                                    document.getElementById("params").style.display= "none";
                                    document.getElementById("text").style.display= "none";
                                    document.getElementById("preset").style.display= "none";
                                    document.getElementById("vcodec").style.display= "none";
                                    document.getElementById("vbitrate").style.display= "none";
                                    document.getElementById("scale").style.display= "none";
                                    document.getElementById("labelPreset").style.display= "none";
                                    document.getElementById("note").style.display= "none";
                                    document.getElementById("buttons").setAttribute('style', 'display: none !important');
                                },
                                success: function(element) {
                                    element = element.replace(/null/g, '');
                                    console.log(element);
                                    response = JSON.parse(element);
                                    if(response.code == 1){
                                        this.filesClient = OC.Files.getClient();
                                        if (override){
                                            this.filesClient.remove(context.dir+"/"+filename);
                                        }
                                        close();
                                        context.fileList.reload();
                                    }else{
                                        context.fileList.showFileBusyState(tr, false);
                                        close();
                                        OC.dialogs.alert(
                                            t('video_converter', response.desc),
                                            t('video_converter', 'Error converting '+filename)
                                        );
                                    }
                                }
                            });
                        });
                    }
                     
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
