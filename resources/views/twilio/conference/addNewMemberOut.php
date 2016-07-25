<?php
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$v_code = $this->v_code;
?>
<Response>
	<Say voice="woman">Welcome to Allian interpreter conference service.</Say>
	<Say voice="woman">Please wait while we connect you to a conference call.</Say>
	<Dial hangupOnStar="true" >
		<Conference waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical">
			<?php echo trim($v_code); ?>
		</Conference>
   </Dial>
</Response>