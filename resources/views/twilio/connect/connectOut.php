<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$customerType = $this->customer['Type'];
$CustomerID = $this->customer['CustomerID'];
$queue = $this->queue;
$from = $this->from;
if($customerType != 3 && $customerType != 4){
	if($customerType == 2){ //Invoice
?>
		<Response>
			<Say voice="woman">
				Welcome back to Alliance Business Solutions phone interpreting line.
			</Say>
		  	<Enqueue
		  		action="connectNowQueueCallback.php?id=<?php echo $queue . "&amp;from=" . $from . "&amp;customerType=" . $customerType . "&amp;CustomerID=" . $CustomerID ?>"
		  		waitUrl="waitForInterpreter.php?pairid=<?php echo $queue;?>">
		  			<?php echo $queue; ?>
		  	</Enqueue>
		</Response>
<?php
	}else{
  		if($customerType == 1){ //PAYPAL
?>
			<Response>
				<Say voice="woman">
					Welcome back to Alliance Business Solutions phone interpreting line.
				</Say>
				<Enqueue
					action="connectNowQueueCallback.php?id=<?php echo $queue . "&amp;from=" . $from . "&amp;customerType=" . $customerType . "&amp;CustomerID=" . $CustomerID ?>"
					waitUrl="waitForInterpreter.php?pairid=<?php echo $queue;?>" >
						<?php echo $queue; ?>
				</Enqueue>
			</Response>
<?php
		} else if($customerType == 3){ ?>
			<Response>
				<Say>
					Your account is not yet verified by the admin... Please wait for the confirmation message. Thank you for calling Alliance Business Solutions phone interpreting line. Good bye.
				</Say>
				<Hangup/>
			</Response>
<?php
		}
	}
}
?>