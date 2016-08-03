<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
// Retrieve the customerType, CustomerID, queue, from information
$custType = $this->customer['Type'];
$CustomerID = $this->customer['CustomerID'];
// Queue is the PairID retrieved from the database, it's based on the languages L1&L2
$queue = $this->queue;
$from = $this->from;
// Type where the account is not verified or something, don't allow access
if($custType != 3 && $custType != 4){
	if($custType == 2){ //Invoice
?>
		<Response>
			<Say voice="woman">
				Welcome back to Alliance Business Solutions phone interpreting line.
			</Say>
		  	<Enqueue
		  		action="connectNowQueueCallback?id=<?php echo $queue . "&amp;from=" . $from . "&amp;customerType=" . $custType . "&amp;CustomerID=" . $CustomerID ?>"
		  		waitUrl="waitForInterpreter?pairid=<?php echo $queue;?>">
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
					action="connectNowQueueCallback?id=<?php echo $queue . "&amp;from=" . $from . "&amp;customerType=" . $custType . "&amp;CustomerID=" . $CustomerID ?>"
					waitUrl="waitForInterpreter?pairid=<?php echo $queue;?>" >
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
			We have sent an e mail to your mail ID. Please visit the link in mail and fill out credit card details for using this service. Thank you for calling Alliance Business Solutions phone interpreting line. Good bye.
		</Say>
		<Hangup/>
	</Response>
<?php
}
?>