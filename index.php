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
	<script type="text/javascript" src="js/jquery.stickynote.js"></script>
	<script type="text/javascript">
		var currentPage = 0;
		$(function() {
			$('#click').click(function(){
				$.get('notes.php', {
					page: currentPage
				},function(data) {
				}, 'json');
			});
			$('#click').stickynote();
		});
	</script>
</head>
<body>
	<button id="click">Click</button>
	<div id="content"></div>
</body>
</html>
