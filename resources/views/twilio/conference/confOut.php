<?php
// EASY WAY
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$v_code = $this->v_code;
?>
<Response>
	<Say voice="woman">Welcome to Allian interpreter conference service.</Say>
	<Redirect method="POST">
		http://alliantranslate.com/linguist/twilio-conf-enhanced/conference.php?Digits=1&vcode=<?php echo trim($v_code); ?>
	</Redirect>
</Response>