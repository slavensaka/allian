<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$real_queue = $this->real_queue;
?>
<Response>
	<Say voice="woman">Hello. Welcome to Alliance Business Solutions LLC conference service.</Say>
	<Say voice="woman">You are invited to join a conference. Redirecting now.</Say>
	<Dial hangupOnStar="true">
		<Conference waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical">
			<?php echo trim($real_queue); ?>
		</Conference>
	</Dial>
</Response>