<html>
<head>
	<title>Cyclone</title>
	<link rel="stylesheet" href="./css/index.css" type="text/css">
	<script type='text/javascript' src='./js/jquery.min.js'></script>
	<script type='text/javascript' src='./js/util_general.js'></script>
	<script type='text/javascript'>
		function fOnBodyLoad()
		{
			fConsoleLog('fOnBodyLoad');
			$('#btn_cron').click( function(){ fOnBtnCron($(this)) } );
			$('#btn_rule').click( function(){ fOnBtnRule($(this)) } );
			$('#btn_location').click( function(){ fOnBtnLocation($(this)) } );
		}
		function fOnBtnCron(sender)
		{
			window.location = 'cron.php';
		}
		function fOnBtnRule(sender)
		{
			window.location = 'ui_rules.php';			
		}
		function fOnBtnLocation(sender)
		{
			window.location = 'srv_ext_trigger_test.html';			
		}
	</script>
</head>
<body onLoad="fOnBodyLoad();">

<img src='images/clock.png' id='btn_cron' class='img_button'> Cron<br/>
<br/>
<img src='images/list.png' id='btn_rule' class='img_button'> Rules<br/>
<br/>
<img src='images/location.png' id='btn_location' class='img_button'> External Trigger<br/>

<?php



?>


</body>
</html>