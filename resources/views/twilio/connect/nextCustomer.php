<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$IPID = $this->IPID;
$pairarray = $this->pairarray;
$times = $this->times;
$next = $this->next; ?>



	<Response>
		<Dial timeout="1" action='redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $array; ?>&amp;times=<?php echo $times; ?>&amp;Previous=<?php echo $next; ?>&amp;PairID=<?php echo $PairID; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;'>
			<Queue
				url = 'redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $array; ?>&amp;times=<?php echo $times; ?>&amp;Previous=<?php echo $next; ?>&amp;PairID=<?php echo $PairID; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;'
			>
				<?php

						echo $next;

				?>
			</Queue>
		</Dial>
		<Hangup/>
	</Response>