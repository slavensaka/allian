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
		Please wait until the next customer dials in or hang up the phone.
	</Say>
	<Dial
		timeout="1"
		action='http://alliantranslate.com/linguist/phoneapp/handlepayment.php?IPID=<?php echo $IPID; ?>&amp;real_queue=<?php echo $real_queue ?>&amp;pairarray=<?php echo $array;?>&amp;times=1&amp;Previous=<?php echo $pair1 ;?>'
	>
    <?php
		if(isset($PairID)){
			echo "<Queue>" . $PairID . "</Queue>";
		}else{
		   	echo "<Queue>" . $pair1 . "</Queue>";
		}
	?>
   	</Dial>
	<Hangup/>
</Response>