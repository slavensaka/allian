<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
?>
<Response>
        <Say voice="woman">
        	Welcome to Allian interpreter conference service.
        	<!-- Please enter verification code and then press star. -->
        </Say>
        <Dial hangupOnStar="true" >
			<Conference waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical">
				<?php
				if(empty($conference)) {
					echo trim($data['conf_tag']); //conference_schedule conf_tag = 4832815500
				} else {
					echo $conference;
				}
				?>
			</Conference>
		</Dial>
</Response>

