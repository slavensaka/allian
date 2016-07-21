<?php
// unlink("userdata/".$sid.".txt");
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$pairid = $this->pairid;
//
?>
<Response>
    <Say>Please wait while we attempt to reach an interpreter for your call.</Say>
    <Say>Please continue to wait while we find the first available interpreter</Say>
    <!-- Redirect -->
    <Redirect method="POST">https://alliantranslate.com/linguist/phoneapp/callout.php?pairid=<?php echo $pairid; TU</Redirect>
</Response>