<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$real_queue = $this->real_queue;
$IPID = $this->IPID;
$array = $this->array;
$pair1 = $this->pair1;
?>
<Response>
	<Dial hangupOnStar="true" >
		<Conference
			waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical"
			statusCallback="connectNowQueueCallback?"
			action='http://alliantranslate.com/linguist/phoneapp/handlepayment.php?IPID=<?php echo $IPID ; ?>&amp;pairarray=<?php echo $array;?>&amp;times=60&amp;Previous=<?php echo $pair1 ;?>'
		>
			<?php echo $real_queue; ?>
		</Conference>
	</Dial>
</Response>