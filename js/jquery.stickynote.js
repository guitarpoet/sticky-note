(function($) {
	$.fn.stickynote = function(options) {
		var opts = $.extend({}, $.fn.stickynote.defaults, options);
		return this.each(function() {
			$this = $(this);
			$.fn.stickynote.createNote($.meta ? $.extend({}, opts, $this.data()) : opts);
		});
	};
	$.fn.stickynote.defaults = {
		size: 'large',
		color: '#000000',
		time: new Date(),
		author: 'nobody'
	};
	$.fn.stickynote.createNote = function(o) {
		var content = $(document.createElement('textarea'));
		var note = $(document.createElement('div')).addClass('jStickyNote').css('cursor','move');
		if(!o.text){
			note.append(content);
			var create_note = $(document.createElement('div')).addClass('jSticky-create').attr('title','Create Sticky Note');
			create_note.click(function(){
				var message = $(this).parent().find('textarea').val();
				$.get('notes.php', {
					author: o.author,
					message: message,
				       	time: $.format.date(new Date(), 'yyyy-MM-dd HH:mm:ss'),
					type: 'create'
				}, function(data){
					var note_textarea = $(document.createElement('p')).css('color',o.color).html(message);
					create_note.parent().find('textarea').before(note_textarea).remove(); 
					create_note.parent().data('id', data.id);
					console.info($.format.date(new Date(), 'yyyy-MM-dd HH:mm:ss'));
					note_textarea.before($(document.createElement('p')).addClass('title').html(o.author + '<br> (' + $.format.date(new Date(), 'yyyy-MM-dd HH:mm:ss') + ')'));
					create_note.remove();						
				}, 'json')
			})
		}	
		else {
			note.append($(document.createElement('p')).addClass('title').html(o.author + '<br> (' + o.time + ')'));
			note.append($(document.createElement('p')).css({color: o.color}).text(o.text));					
		}
		
		var delete_note = $(document.createElement('div')).addClass('jSticky-delete');
		
		delete_note.click(function(e){
			var id = $(this).parent().data('id');
			if(!$.fn.stickynote.beforeDelete || $.fn.stickynote.beforeDelete(id)) {
				$.get('notes.php', {
					id: id, 
					type: 'delete'
				}, function(){
					delete_note.parent().remove();
				})
			}
		})
		
		var note_wrap = $(document.createElement('div')).css({'position':'absolute','top':'0','left':'0'})
			.append(note).append(delete_note).append(create_note);	
		switch(o.size){
			case 'large':
				note_wrap.addClass('jSticky-large');
				break;
			case 'small':
				note_wrap.addClass('jSticky-medium');
				break;
		}		
		if(o.containment){
			note_wrap.draggable({ containment: '#'+o.containment, scroll: false ,start: function(event, ui) {
				if(o.ontop)
					$(this).parent().append($(this));
			}});	
		}	
		else{
			var left = Math.random(1)*$('body').width() - 245;
			var top = Math.random(1) * $('body').height() - 248;
			if(!o.text) {
				left = ($('body').width() - 225) / 2;
				top = ($('body').height() - 228) / 2;
			}
			note_wrap.draggable({ scroll: false ,start: function(event, ui) {
				if(o.ontop)
					$(this).parent().append($(this));
			}}).css('left', left < 0? 20 : left).css('top', top < 0? 20 : top);
		}
		note_wrap.data('id', o.id);
		$('#content').append(note_wrap);
		if(!o.text)
			note_wrap.find('textarea').focus();
	};
})(jQuery);
