<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$real_queue = $this->real_queue;
$IPID = $this->IPID;
$array = $this->array;
$pair1 = $this->pair1;
//Dodaj u action na live http://alliantranslate.com/linguist/phoneapp/
?>
<Response>
	<Say>
		Your being connected to a new conference.
	</Say>
	<Dial hangupOnStar="true"
		action='handlepayment.php?IPID=<?php echo $IPID ; ?>&amp;pairarray=<?php echo $array;?>&amp;times=60&amp;Previous=<?php echo $pair1 ; ?>'
	>
		<Conference
			waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical"
		>
			<?php echo $real_queue; ?>
		</Conference>
	</Dial>
</Response>