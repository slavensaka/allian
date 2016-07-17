<?php
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$v_code = $this->v_code;
?>
<Redirect method="POST">http://alliantranslate.com/linguist/twilio-conf-enhanced/conference.php?vcode=<?php echo $v_code; ?></Redirect>