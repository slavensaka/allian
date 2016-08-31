<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$real_queue = $this->real_queue;
$fullname = $this->fullname;
?>
<Response>
	<Say voice="woman">Greeting.  We are calling from ALLIAN language services.</Say>
	<Say voice="woman">You are joining a interpreted call invited by <?php echo $fullname ?>.</Say>
	<Dial hangupOnStar="true">
		<Conference waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical">
			<?php echo trim($real_queue); ?>
		</Conference>
	</Dial>
</Response>