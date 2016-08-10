<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');

$custType = $this->customer['Type'];
$CustomerID = $this->customer['CustomerID'];
$queue = $this->queue;
$real_queue = $this->real_queue;
$from = $this->from;
// Type where the account is not verified or something, don't allow access
if($custType != 3 && $custType != 4){
	if($custType == 2){ // Invoice
?>
		<Response>
			<Say voice="woman">
				Welcome back to Alliance Business Solutions phone interpreting line.
			</Say>
		  	<Enqueue
		  		action="connectNowQueueCallback?<?php echo
		  			"id=" . $queue .
		  			"&from=" . $from .
		  			"&customerType=" . $custType .
		  			"&CustomerID=" . $CustomerID;
		  		?>"
		  		waitUrl="waitForInterpreter?<?php echo
		  			"pairid=" . $queue .
		  			"&real_queue=" . $real_queue;
		  		?>"
		  	>
		  			<?php echo $queue; ?>
		  	</Enqueue>
		</Response>
<?php
	} else if($custType == 1){ //PAYPAL
?>
			<Response>
			<Say voice="woman">
				Welcome back to Alliance Business Solutions phone interpreting line.
			</Say>
		  	<Enqueue
		  		action="connectNowQueueCallback?<?php echo
		  			"id=" . $queue .
		  			"&from=" . $from .
		  			"&customerType=" . $custType .
		  			"&CustomerID=" . $CustomerID;
		  		?>"
		  		waitUrl="waitForInterpreter?<?php echo
		  			"pairid=" . $queue .
		  			"&real_queue=" . $real_queue;
		  		?>"
		  	>
		  			<?php echo $queue; ?>
		  	</Enqueue>
		</Response>
<?php
	}
} else if($custType == 3){
?>
	<Response>
		<Say>
			Your account is not yet verified by the admin... Please wait for the confirmation message. Thank you for calling Alliance Business Solutions phone interpreting line. Good bye.
		</Say>
		<Hangup/>
	</Response>
<?php
} else {
?>
	<Response>
		<Say>
			Incorrect customer Type. Good bye.
		</Say>
		<Hangup/>
	</Response>
<?php
}
?>