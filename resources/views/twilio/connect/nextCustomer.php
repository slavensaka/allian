<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
$PairID = $this->PairID;
$real_queue = $this->real_queue;
$IPID = $this->IPID;
$array = $this->array;
$pair1 = $this->pair1;
?>

<?php if($PairID){ ?>
	<Response>
		<Say>
			You are now being connected to the client.
		</Say>
		<Dial timeout="60">
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

<?php }else { ?>
	<Response>
		<Say>Please wait until the next customer dials in or hang up the phone.</Say>
		<Dial timeout="1">
    		<Queue
				url = 'redirectToConference?IPID=<?php echo $IPID; ?>&amp;pairarray=<?php echo $array; ?>&amp;times=1&amp;Previous=<?php echo $pair1; ?>&amp;PairID=<?php echo $PairID; ?>&amp;real_queue=<?php echo $real_queue; ?>&amp;'
    		>
    			<?php echo $pair1; ?>
    		</Queue>
   		</Dial>
		<Hangup/>
	</Response>
<?php } ?>