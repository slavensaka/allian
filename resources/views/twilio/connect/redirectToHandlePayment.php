<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$IPID = $this->IPID;
$PairID = $this->PairID;
$real_queue=$this->real_queue;
$pairarray = $this->pairarray;
$Previous=$this->Previous;
$times = $this->times;
$next = $this->next;
// $IPID = $this->IPID;
// $pairarray = $this->pairarray;
// $times = $this->times;
// $next = $this->next;
?>

	<Response>
		<Dial timeout="1" action='redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $pairarray; ?>&amp;times=<?php echo $times; ?>&amp;Previous=<?php echo $next; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;'>
			<Queue
				url = 'redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $pairarray; ?>&amp;times=<?php echo $times; ?>&amp;Previous=<?php echo $next; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;'
			>
				<?php

						echo $next;

				?>
			</Queue>
		</Dial>
		<Hangup/>
	</Response>