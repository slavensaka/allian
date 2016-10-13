<?php
// HARD WAY
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
if(empty($conference)){
	$data = $this->verified;
	$orderId = $this->orderId;
?>
	<Response>
		<Say voice="woman">Welcome to Allian interpreter conference service. <?php echo $data['msg']; ?></Say>
<?php // If session expired, or wrong user_code (error occured), voice message and hangup
	if(!$data['auth']){
		echo "<Hangup/></Response>";
		exit();
	}
	// Limit Excedded NE VIDIM DA SE KORISTI
	// if(!empty($conference)){ // used for add_new_memeber
	// 	$data['auto_start'] = "true";
	// 	$data['conf_tag'] = $conference;
	// 	echo "<Response>";
	// }
?>
		<Dial hangupOnStar="true" action='conferenceEndCallback?conf_tag=<?php echo $data['conf_tag']; ?>&amp;orderId=<?php echo $orderId; ?>'>
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
<?php
		if($data['auto_start'] == "true" && empty($member)){
			//<Redirect>addNewMember.php?conference=<?php echo trim($data['conf_tag']); </Redirect>
		}
?>
	</Response>
<?php
} else {
	echo"<Response><Hangup/></Response>";
}
?>