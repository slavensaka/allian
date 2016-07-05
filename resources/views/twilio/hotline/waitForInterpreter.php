<?php
// unlink("userdata/".$sid.".txt");
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Say>Please wait while we attempt to reach an interpreter for your call.</Say>
    <Say>Please continue to wait while we find the first available interpreter</Say>
    <Redirect method="POST">callout.php?pairid=<?php echo $this->pairid; ?></Redirect>
</Response>
<!-- TODO Redirectaj na https://alliantranslate.com/linguist/phoneapp/wait.php -->