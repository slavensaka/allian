<?php
use Allian\Helpers\Mail;
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$IPID = $this->IPID;
$pairarray = $this->pairarray;
$times = $this->times;
$next = $this->next;
$real_queue=$this->real_queue;
// Mail::simpleLocalMail("nextCustomer.php sve varijable", 'IPID=' .$IPID . 'pariarray' . $pariarray . 'times' . $times . ' next:' . $next .' real_queue:' . $real_queue .' array:'. $array);
?>

<Response>
	<Say>Please wait until the next customer dials in or hang up the phone.</Say>
	<Dial timeout="1" action='redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $pairarray; ?>&amp;Previous=<?php echo $next; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;times=<?php echo $times ?>'>
		<Queue
			url = 'redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $pairarray; ?>&amp;Previous=<?php echo $next; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;'>
			<?php echo $next; ?>
		</Queue>
	</Dial>
	<Hangup/>
</Response>