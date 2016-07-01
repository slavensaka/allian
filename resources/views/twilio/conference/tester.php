
<html>
<head>
	<title>TTS Tester</title>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script type="text/javascript" src="http://static.twilio.com/libs/twiliojs/1.0/twilio.js"></script>
	<script type="text/javascript">
	// Initialize Twilio Client
	Twilio.Device.setup("<?php echo $this->escape($this->genToken) ?>");
	$(document).ready(function() {
		// When someone clicks the submit button, speak.
		$("#submit").click(function() {
			speak();
		});
	});

	function speak() {
		// Get the values of the form.
		var dialogue = $("#dialogue").val();
		var voice = $('input:radio[name=voice]:checked').val();
		// Disable the button while we're speaking.
		$('#submit').attr('disabled', 'disabled');
		// Send the form values to our TTS Tester app and use
		// Twilio Client to speak the text in the browser.
		Twilio.Device.connect({ 'dialogue' : dialogue, 'voice' : voice });
	}

	// When we're done speaking the text, enable the button again.
	Twilio.Device.disconnect(function (conn) {
		$('#submit').removeAttr('disabled');
	});

	</script>
</head>
<body>
<p>
<label for="dialogue">Text to be spoken</label>
<input type="text" id="dialogue" name="dialogue" size="50">
</p>
<p>
<label for="voice-male">Male Voice</label>
<input type="radio" id="voice-male" name="voice" value="1" checked="checked">
<label for="voice-female">Female Voice</label>
<input type="radio" id="voice-female" name="voice" value="2">
</p>
<p>
<input type="button" id="submit" name="submit" value="Speak to me">
</p>
</body>
</html>


