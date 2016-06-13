<?php
define('PATH', __DIR__);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<link rel="stylesheet" href="">
</head>
<body>


Hello,
<br><br>
A registered Alliance Business Solutions language services client
<strong>
	%FName% %LName%
</strong>
has requested that the access for the Telephone Interpreter Hotline be provided to you.
<br><br>
For immediate access to a interpreter please call the number below and use the username and password noted below.
<br><br>


<div class="PrintArea area1 both" id="html_card_us">
	<div id="heading" style="width:280px; border-bottom:0px solid #CCC !important; border:1px solid #cccccc; font:Arial, Helvetica, sans-serif;  margin:0; padding:10px; font-weight:bold;">Alliance Business Solutions</div>
	<div id="card" style="width:300px; height:130px; background:#f5f5f5; border:1px solid #cccccc;">
		<div id="info" style="margin:20px 5px; font-size:9px; font-weight:bold;background:#f5f5f5">
			<table width="100%"   cellpadding="5" cellspacing="0" border="0" style="font-size:12px; background:#f5f5f5">
			<tr>
					<td width="55%" class="title" style="font-weight:bold !important;">Registered User Hotline</td>
					<td width="45%">%tel%</td>
			</tr>
			<tr>
					<td class="title"  style="font-weight:bold !important">Telephonic User ID</td>
					<td>%telephonicUserId%</td>
			</tr>
			<tr>
					<td class="title"  style="font-weight:bold !important">Telephonic Password</td>
					<td>%telephonicPassword%</td>
			</tr>
			</table>
		</div>
	</div>
</div>

<br><strong>If you have any questions, please email us at any time.</strong><br><br>Alliance Business Solutions<br>-Client Services<br>E: %csEmail%.<br>



</body>
</html>