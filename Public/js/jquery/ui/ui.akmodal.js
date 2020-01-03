/**
 * @author Leo Ning 2008-12-24
 */
jQuery.extend({
	akOpen:function(url,title){
		$.showAkModal(url,title);
	},
	akModal:function(nvaurl,title){
		$.showAkModal(nvaurl,title);
	},
	showAkModal:function(navurl,title){
		$('body').append("<div id='ak_load' style='text-align:center;width:32px;height:32px;display:none;z-index:1000;position: absolute;'><div id='ak_loadding'></div></div>");
		$('body').append("<div id='ak_modal_div'><div id='ak_modal_title'>"
		+"<table id='ak_table'><tr><td align='left'><b>"+title+"</b></td><td align='right'><a id = 'ak_close' title='关闭'></a></td></tr></table>"
		+"</div><div id='ak_content'>"
		+"<iframe id='ak_iframe' width='100%' height='100%' frameborder='0' marginwidth='0' marginheight='0' name='ak_iframe' src='"+navurl+"'></iframe>"
		+"</div></div>");

		var win_width =$(window).width();
		var scrollToLeft=$(window).scrollLeft();
		var win_height =$(window).height();
		var scrollToBottom=$(window).scrollTop();

		$('#ak_load').css({left:(((win_width/2-20))+scrollToLeft)+'px',top:(((win_height/2-20))+scrollToBottom)+'px'});

		$.dimScreen("fast", 0.7, function() {
			$('#ak_load').show();
		});

		$('#ak_close').click( function() {
			$('#ak_modal_div').fadeOut(500,function(){
				$('#ak_modal_div').remove();
				$.dimScreenStop();
			});
		});
	},
	akClose:function(redirect_url){
		if('undefined' == typeof redirect_url){
			$.akModalRemove();
		}else{
			$.akModalHideAndRedirect(redirect_url);
		}
	},
	akModalRemove:function(){
		$('#ak_modal_div').fadeOut(500,function(){
			$.dimScreenStop();
			$('#ak_modal_div').remove();
		});
		$(document).unbind('keypress');
	},
	akModalHideAndRedirect:function(redirect_url){
		$(document).unbind('keypress');
		$('#ak_modal_div').fadeOut(500,function(){
			$.dimScreenStop();
		});
		window.location = redirect_url;
	},	
	akOnload:function(width,height){
		width = (!width) ? 400:width;
		height = (!height) ? 300:height;
		var win_width =$(window).width();
		var scrollToLeft=$(window).scrollLeft();
		var win_height =$(window).height();
		var scrollToBottom=$(window).scrollTop();
		$('#ak_content').css({width:width,height:height}).end();
		if($.browser.msie){
			var ak_border = parseInt($('#ak_modal_div').css('border-left-width'),10) + parseInt($('#ak_modal_div').css('border-left-width'),10);
			$('#ak_modal_div').css('width',width+ak_border+'px');
		}
		$('#ak_table').css('width',width-2+'px');
		$('#ak_modal_div').css({left:(((win_width/2-width/2))+scrollToLeft)+'px',top:(((win_height/2-height/2))+scrollToBottom)+'px'});
		$(document).bind('keypress',function(event){			
			if(event.keyCode == 27){
				$.akModalRemove();
			}
		});
		$('#ak_load').hide('fast',function(){
			$('#ak_load').remove();
			$('#ak_modal_div').fadeIn(500,function(){
				var handle = $('#ak_modal_title').get(0);
				var dragBody = $('#ak_modal_div').get(0);
				dragBody.onDragStart = function (left,top,pageX,pageY,e){
					if(e.target.id == 'ak_close'){
						return false;
					}
					$('#ak_iframe').hide();
					//$('#ak_content').slideUp('fast');
					$('#ak_modal_div').css('opacity',.8);
					$('#ak_modal_title').addClass('move');
				};
				dragBody.onDragEnd = function(){
					$('#ak_modal_title').removeClass('move');
					//$('#ak_content').slideDown('fast',function(){ 
						$('#ak_iframe').show();
					//});					
					$('#ak_modal_div').css('opacity',1);
				}
				akDrag.init(handle,dragBody);
			});
		});
	},
	akOnclose:function(callback){
		if('function' === typeof callback){
			callback();
		}
	}
});
////////////////////////////dimScreen///////////////////////////
jQuery.extend({
	dimScreen: function(speed, opacity, callback) {
		if(jQuery('#__dimScreen').size() > 0) return;

		if(typeof speed == 'function') {
			callback = speed;
			speed = null;
		}
		if(typeof opacity == 'function') {
			callback = opacity;
			opacity = null;
		}
		if(speed < 1) {
			var placeholder = opacity;
			opacity = speed;
			speed = placeholder;
		}
		if(opacity >= 1) {
			var placeholder = speed;
			speed = opacity;
			opacity = placeholder;
		}
		speed = (speed > 0) ? speed : 500;
		opacity = (opacity > 0) ? opacity : 0.5;
		return jQuery('<div></div>').attr({
			id: '__dimScreen'
			,fade_opacity: opacity
			,speed: speed
		}).css({
			background: '#ccc'
			,height: $(document).height() + 'px'
			,left: '0px'
			,opacity: 0
			,position: 'absolute'
			,top: '0px'
			,width: $(document).width() + 'px'
			,zIndex: 999
		}).appendTo(document.body).fadeTo(speed, 0.7, callback);
	},
	//stops current dimming of the screen
	dimScreenStop: function(callback) {
		var x = jQuery('#__dimScreen');
		var opacity = x.attr('fade_opacity');
		var speed = x.attr('speed');
		x.fadeOut(speed, function() {
			x.remove();
			if(typeof callback == 'function') callback();
		});
	}
});
////////////////////////////Drag Lib///////////////////////////
var akDrag={
"obj":null,
"init":function(handle,dragBody,proxy, e){
	if (e == null) {
		handle.onmousedown = akDrag.start;
	}
	handle.root = dragBody;
	if(isNaN(parseInt(handle.root.style.left,10))) handle.root.style.left = "0px";
	if(isNaN(parseInt(handle.root.style.top,10)))  handle.root.style.top  = "0px";
	if('function' != typeof dragBody.onDragStart)handle.root.onDragStart = new Function();
	if('function' != typeof dragBody.onDragEnd) handle.root.onDragEnd   = new Function();
	if('function' != typeof dragBody.onDrag) handle.root.onDrag      = new Function();
	if (e !=null) {
		var handle = akDrag.obj = handle;
		e = akDrag.fixEvent(e);
		var top  = parseInt(handle.root.style.top,10);
		var left = parseInt(handle.root.style.left,10);
		handle.root.onDragStart(left,top,e.pageX,e.pageY);
		handle.lastMouseX = e.pageX;
		handle.lastMouseY = e.pageY;
		document.onmousemove = akDrag.drag;
		document.onmouseup   = akDrag.end;
	}
},
"start":function(e){
	var handle = akDrag.obj = this;
	e = akDrag.fixEvent(e);
	akDrag.obj.event = e;
	var top  = parseInt(handle.root.style.top,10);
	var left = parseInt(handle.root.style.left,10);
	//一般情况下 left top 在初始的时候都为0
	if(false === handle.root.onDragStart(left,top,e.pageX,e.pageY,e)){
		akDrag.obj           = null;
		return ;
	}
	handle.lastMouseX 	 = e.pageX;
	handle.lastMouseY 	 = e.pageY;
	document.onmousemove = akDrag.drag;
	document.onmouseup 	 = akDrag.end;
	return false;
},
"drag":function(e){//这里的this为document 所以拖动对象只能保存在akDrag.obj里

	e = akDrag.fixEvent(e);
	var handle = akDrag.obj;
	var mouseY = e.pageY;
	var mouseX = e.pageX;
	var top    = parseInt(handle.root.style.top,10);
	var left   = parseInt(handle.root.style.left,10);
	//这里的top和left是handle.root距离浏览器边框的上边距和左边距

	var currentLeft,currentTop;
	currentLeft = left + mouseX - handle.lastMouseX;
	currentTop  = top  + mouseY - handle.lastMouseY;
	//上一瞬间的上边距加上鼠标在两个瞬间移动的距离 得到现在的上边距
	handle.root.style.left = currentLeft + "px";
	handle.root.style.top  = currentTop  + "px";
	//更新当前的位置
	handle.lastMouseY = mouseY;
	handle.lastMouseX = mouseX;
	//保存这一瞬间的鼠标值 用于下一次计算位移
	handle.root.onDrag(currentLeft,currentTop,e.pageX,e.pageY);//调用外面对应的函数
	return false;
},
"end":function(){
	document.onmousemove = null;
	document.onmouseup   = null;
	akDrag.obj.root.onDragEnd(parseInt(akDrag.obj.root.style.left,10),parseInt(akDrag.obj.root.style.top,10));
	akDrag.obj           = null;
},
"fixEvent":function(e){//格式化事件参数对象
	if(typeof e == "undefined"){
		e  		 = window.event;
		e.target = e.srcElement;
	}
	if(typeof e.layerX == "undefined") e.layerX = e.offsetX;
	if(typeof e.layerY == "undefined") e.layerY = e.offsetY;
	if(typeof e.pageX  == "undefined") e.pageX  = e.clientX + document.body.scrollLeft - document.body.clientLeft;
	if(typeof e.pageY  == "undefined") e.pageY  = e.clientY + document.body.scrollTop - document.body.clientTop;
	return e;
}
};