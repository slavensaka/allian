<?php
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$vcode = $this->vcode;
$fullname = $this->fullname;
?>
<Response>
	<Say voice="woman">Greeting. We are calling from ALLIAN language services.</Say>
	<Say voice="woman">You are joining a interpreted call invited by <?php echo $fullname ?>.</Say>
	<Dial hangupOnStar="true">
		<Conference waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical">
			<?php echo trim($vcode); ?>
		</Conference>
   </Dial>
</Response>