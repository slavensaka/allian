<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$real_queue = $this->real_queue;
?>
<Response>
	<Dial hangupOnStar="true" >
		<Conference
			waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical"
			statusCallback="connectNowQueueCallback?"
		>
			<?php echo $real_queue; ?>
		</Conference>
	</Dial>
</Response>