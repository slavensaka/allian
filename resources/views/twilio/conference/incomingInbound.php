<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
header('Content-type: text/xml');
?>

<Response>
    <Say>
    	<Client>jenny</Client>
    	Thank you for calling Alliance Business Solutions phone interpreter services.
    	<?php echo $this->poruka ?>
    </Say>
</Response>