<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$PairID = $this->PairID;
$real_queue = $this->real_queue;
$Pairname = $this->Pairname;
?>

<Response>
	<Gather
		timeout="10"
		action="http://alliantranslate.com/testgauss/interpreter?PairID=<?php echo $PairID;?>&amp;real_queue=<?php echo $real_queue ?>"
		numDigits="1"
	>
		<Say>
    		New Telephonic interpreting project for language pair <?php echo $Pairname; ?>. If you are available with a good phone connection and in a quite place, to accept this call please press any key.  You will be connected to the client directly. If you are not available, please hang up the phone.
		</Say>
	</Gather>
	<Hangup/>
</Response>