var smoke={smoketimeout:[],init:false,zindex:1000,i:0,bodyload:function(id){var ff=document.createElement('div');ff.setAttribute('id','smoke-out-'+id);ff.className='smoke-base';ff.style.zIndex=smoke.zindex;smoke.zindex++;document.body.appendChild(ff);},newdialog:function(){var newid=new Date().getTime();newid=Math.random(1,99)+ newid;if(!smoke.init){smoke.listen(window,"load",function(){smoke.bodyload(newid);});}else{smoke.bodyload(newid);}
return newid;},forceload:function(){},build:function(e,f){smoke.i++;f.stack=smoke.i;e=e.replace(/\n/g,'<br />');e=e.replace(/\r/g,'<br />');var prompt='',ok='OK',cancel='Cancel',classname='',buttons='',box;if(f.type==='prompt'){prompt='<div class="dialog-prompt">'+'<input class="form-control" id="dialog-input-'+f.newid+'" type="text" '+(f.params.value?'value="'+ f.params.value+'"':'')+' />'+'</div>';}
if(f.params.ok){ok=f.params.ok;}
if(f.params.cancel){cancel=f.params.cancel;}
if(f.params.classname){classname=f.params.classname;}
if(f.type!=='signal'){buttons='<div class="dialog-buttons">';if(f.type==='alert'){buttons+='<button class="btn btn-primary" id="alert-ok-'+f.newid+'">'+ok+'</button>';}else if(f.type==='prompt'||f.type==='confirm'){if(f.params.reverseButtons){buttons+='<button class="btn btn-primary" id="'+f.type+'-ok-'+f.newid+'">'+ok+'</button>'+'<button class="btn btn-default" id="'+f.type+'-cancel-'+f.newid+'" class="cancel">'+cancel+'</button>';}else{buttons+='<button class="btn btn-primary" id="'+f.type+'-cancel-'+f.newid+'" class="cancel">'+cancel+'</button>'+'<button class="btn btn-default" id="'+f.type+'-ok-'+f.newid+'">'+ok+'</button>';}}
buttons+='</div>';}
box='<div id="smoke-bg-'+f.newid+'" class="smokebg"></div>'+'<div class="dialog smoke '+classname+'">'+'<div class="dialog-inner">'+
e+
prompt+
buttons+'</div>'+'</div>';if(!smoke.init){smoke.listen(window,"load",function(){smoke.finishbuild(e,f,box);});}else{smoke.finishbuild(e,f,box);}},finishbuild:function(e,f,box){var ff=document.getElementById('smoke-out-'+f.newid);ff.className='smoke-base smoke-visible  smoke-'+ f.type;ff.innerHTML=box;while(ff.innerHTML===""){ff.innerHTML=box;}
if(smoke.smoketimeout[f.newid]){clearTimeout(smoke.smoketimeout[f.newid]);}
smoke.listen(document.getElementById('smoke-bg-'+f.newid),"click",function(){smoke.destroy(f.type,f.newid);if(f.type==='prompt'||f.type==='confirm'){f.callback(false);}else if(f.type==='alert'&&typeof f.callback!=='undefined'){f.callback();}});switch(f.type){case'alert':smoke.finishbuildAlert(e,f,box);break;case'confirm':smoke.finishbuildConfirm(e,f,box);break;case'prompt':smoke.finishbuildPrompt(e,f,box);break;case'signal':smoke.finishbuildSignal(e,f,box);break;default:throw"Unknown type: "+ f.type;}},finishbuildAlert:function(e,f,box)
{smoke.listen(document.getElementById('alert-ok-'+f.newid),"click",function(){smoke.destroy(f.type,f.newid);if(typeof f.callback!=='undefined'){f.callback();}});document.onkeyup=function(e){if(!e){e=window.event;}
if(e.keyCode===13||e.keyCode===32||e.keyCode===27){smoke.destroy(f.type,f.newid);if(typeof f.callback!=='undefined'){f.callback();}}};},finishbuildConfirm:function(e,f,box)
{smoke.listen(document.getElementById('confirm-cancel-'+ f.newid),"click",function()
{smoke.destroy(f.type,f.newid);f.callback(false);});smoke.listen(document.getElementById('confirm-ok-'+ f.newid),"click",function()
{smoke.destroy(f.type,f.newid);f.callback(true);});document.onkeyup=function(e){if(!e){e=window.event;}
if(e.keyCode===13||e.keyCode===32){smoke.destroy(f.type,f.newid);f.callback(true);}else if(e.keyCode===27){smoke.destroy(f.type,f.newid);f.callback(false);}};},finishbuildPrompt:function(e,f,box)
{var pi=document.getElementById('dialog-input-'+f.newid);setTimeout(function(){pi.focus();pi.select();},100);smoke.listen(document.getElementById('prompt-cancel-'+f.newid),"click",function(){smoke.destroy(f.type,f.newid);f.callback(false);});smoke.listen(document.getElementById('prompt-ok-'+f.newid),"click",function(){smoke.destroy(f.type,f.newid);f.callback(pi.value);});document.onkeyup=function(e){if(!e){e=window.event;}
if(e.keyCode===13){smoke.destroy(f.type,f.newid);f.callback(pi.value);}else if(e.keyCode===27){smoke.destroy(f.type,f.newid);f.callback(false);}};},finishbuildSignal:function(e,f,box)
{smoke.smoketimeout[f.newid]=setTimeout(function(){smoke.destroy(f.type,f.newid);},f.timeout);},destroy:function(type,id){var box=document.getElementById('smoke-out-'+id),okButton=document.getElementById(type+'-ok-'+id),cancelButton=document.getElementById(type+'-cancel-'+id);box.className='smoke-base';if(okButton){smoke.stoplistening(okButton,"click",function(){});document.onkeyup=null;}
if(cancelButton){smoke.stoplistening(cancelButton,"click",function(){});}
smoke.i=0;box.innerHTML='';if(box.parentNode){box.parentNode.removeChild(box);}},alert:function(e,f,g){if(typeof f!=='object'){f=false;}
var id=smoke.newdialog();smoke.build(e,{type:'alert',callback:g,params:f,newid:id});},signal:function(e,f){if(typeof f==='undefined'){f=5000;}
var id=smoke.newdialog();smoke.build(e,{type:'signal',timeout:f,params:false,newid:id});},confirm:function(e,f,g){if(typeof g!=='object'){g=false;}
var id=smoke.newdialog();smoke.build(e,{type:'confirm',callback:f,params:g,newid:id});},prompt:function(e,f,g){if(typeof g!=='object'){g=false;}
var id=smoke.newdialog();return smoke.build(e,{type:'prompt',callback:f,params:g,newid:id});},listen:function(e,f,g){if(e.addEventListener){return e.addEventListener(f,g,false);}
if(e.attachEvent){return e.attachEvent('on'+f,g);}
return false;},stoplistening:function(e,f,g){if(e.removeEventListener){return e.removeEventListener("click",g,false);}
if(e.detachEvent){return e.detachEvent('on'+f,g);}
return false;}};if(!smoke.init){smoke.listen(window,"load",function(){smoke.init=true;});}