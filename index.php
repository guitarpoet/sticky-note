<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<title>Notes</title>
        <link rel="stylesheet" type="text/css" href="css/style.css" media="screen"/>
	<style type="text/css">
		#content {
			width: 600px;
			height: 600px;
			margin:auto;
		}
	</style>
	<script type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery-1.4.2.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.8.6.js"></script>
	<script type="text/javascript" src="js/jquery.format-1.1.js"></script>
	<script type="text/javascript" src="js/jquery.stickynote.js"></script>
	<script type="text/javascript">
		var currentPage = 0;
		$(function() {
			function createNote() {
			}
			$.fn.stickynote.beforeDelete = function(id) {
				return confirm("Are you OK?!");
			}
			function getNotes() {
				$.get('notes.php', {
					page: currentPage
				},function(data) {
					currentPage++;
					$(data.results).each(function(){
						$('#content').stickynote({
							text: this.message,
							author: this.author,
							time: this.time,
							id: this.id
						});
					});
				}, 'json');
			}
			$('#more_note').click(function(){
				getNotes();
			});
			$('#create_note').click(function(){
				$('#content').stickynote({
					author: 'Jack'
				});
			});
			getNotes();
		});
	</script>
</head>
<body>
	<button id="more_note">More notes</button>
	<button id="create_note">Create note</button>
	<div id="content"></div>
	<div id="confirm">
		<h1>Hello</h1>
	</div>
</body>
</html>
