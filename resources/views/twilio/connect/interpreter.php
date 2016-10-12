<?php
use Allian\Helpers\Mail;
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$PairID = $this->PairID;
$real_queue = $this->real_queue;
$IPID = $this->IPID;
$array = $this->array;
$pair1 = $this->pair1;
// $message = 'PairID=' .$PairID . ' Real_queue:' . $real_queue .' IPID:' . $IPID .' array:'. $array .' pair1:'. $pair1;
// Mail::simpleLocalMail("interpreter.php sve varijable", $message);
?>
<?php if(isset($PairID)){ ?>
	<Response>
		<Say>
			You are now being connected to the client.
		</Say>
		<Dial timeout="60" action='redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $array; ?>&amp;times=60&amp;Previous=<?php echo $pair1; ?>&amp;PairID=<?php echo $PairID; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;'>
			<Queue
				url = 'redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $array; ?>&amp;times=60&amp;Previous=<?php echo $pair1; ?>&amp;PairID=<?php echo $PairID; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;'
			>
				<?php
					if(isset($PairID)){
						echo $PairID;
					} else {
						echo $pair1;
					}
				?>
			</Queue>
		</Dial>
		<Hangup/>
	</Response>
<?php } ?>