<?php

header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$CustomerID = $this->CustomerID;
if(!empty($CustomerID)){
?>
	<Response>
    	<Say>Welcome. We have found the customer id. The customers id is <?php echo $CustomerID ?></Say>
    	<Say>Good Job</Say>
	</Response>
<?php
} else {
?>
	<Response>
    	<Say>Welcome. No customer id was present in the request. Please try again with diffrent parameters.</Say>
    	<Say>Goodbye</Say>
	</Response>
<?php
}
?>


