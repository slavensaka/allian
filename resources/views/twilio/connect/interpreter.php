<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$PairID = $this->PairID;
$real_queue = $this->real_queue;
$IPID = $this->IPID;
$array = $this->array;
$pair1 = $this->pair1;
?>
<Response>
	<Say>
		You are now being connected to the client.
	</Say>
		<Dial
			timeout="60"
			action='http://alliantranslate.com/linguist/phoneapp/handlepayment.php?IPID=<?php echo $IPID ; ?>&amp;pairarray=<?php echo $array;?>&amp;times=60&amp;Previous=<?php echo $pair1 ;?>'
		>
			<!-- // DONT USE action, USE THE CONF CALLBACK, REVERT TO queue INSTEAD OF real_queue, ITS NEEDED ONLY ON CONF -->
    		<Queue url='redirectToConference?IPID=<?php echo $IPID ; ?>&amp;pairarray=<?php echo $array;?>&amp;times=60&amp;Previous=<?php echo $pair1 ;?>'>
				<?php echo $real_queue ?>
    		</Queue>
		</Dial>
	<Hangup/>
</Response>