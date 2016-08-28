<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$PairID = $this->PairID;
$real_queue = $this->real_queue;
$Pairname = $this->Pairname;
$IPID = $this->IPID;
?>

<Response>
	<Gather
		timeout="10"
		numDigits="1"
		action='interpreter?<?php echo "PairID=" . $PairID . "&amp;" . "real_queue=" . $real_queue . "&amp;" . "IPID=" . $IPID; ?>'
	>
	<?php mail("slavensakacic@gmail.com", "callRandomHandleHandle.php", "Ovo je PairID: $PairID, OVoj je real_queue: $real_queue Ovo je PairName: $PairName, Ovo je IPID : $IPID"); ?>
		<Say>
    		New Telephonic interpreting project for language pair <?php echo $Pairname; ?>. If you are available with a good phone connection and in a quite place, to accept this call please press any key.  You will be connected to the client directly. If you are not available, please hang up the phone.
		</Say>
	</Gather>
	<Hangup/>
</Response>