<?php
use Allian\Helpers\Mail;
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$IPID = $this->IPID;
$pairarray = $this->array;
$pair1 = $this->pair1;
$real_queue=$this->real_queue;
?>

<Response>
	<Say>Please wait until the next customer dials in or hang up the phone.</Say>
	<Dial timeout="1" action='redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $pairarray; ?>&amp;Previous=<?php echo $pair1; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;times=1'>
		<Queue
			url = 'redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $pairarray; ?>&amp;Previous=<?php echo $pair1; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;'>
			<?php echo $pair1; ?>
		</Queue>
	</Dial>
	<Hangup/>
</Response>