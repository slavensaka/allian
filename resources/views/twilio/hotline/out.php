<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$customerID = $this->customer['Type'];
$queue = $this->queue;
$stripe_queue = $this->stripe_queue;
print_r($stripe_queue);
if($customerID != 3 && $customerID != 4){
?>

<?php
	if($customerID == 2){ //Invoice
?>
		<Response>
		  	<Enqueue
		  		action="queuecallback.php?id=<?php echo $queue."&amp;from=".$this->from; ?>"
		  		waitUrl="waitForInterpreter.php?pairid=<?php echo $queue;?>">
		  			<?php echo $queue; ?>
		  	</Enqueue>
		</Response>
<?php
	}else{
  		if($customerID == 1){ //PAYPAL
?>
			<Response>
				<Enqueue
					action="queuecallback.php?id=<?php echo $stripe_queue."&amp;from=".$this->from; ?>"
					waitUrl="waitForInterpreter.php?pairid=<?php echo $stripe_queue;?>" >
						<?php echo $stripe_queue; ?>
				</Enqueue>
			</Response>
<?php
		} else if($customerID == 3){ ?>
			<Response>
				<Say>
					Your account is not yet verified by the admin... Please wait for the confirmation message. Thank you for calling Alliance Business Solutions phone interpreting line. Good bye.
				</Say>
			</Response>
<?php
		}
	}
}
?>