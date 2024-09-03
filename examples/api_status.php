<?php require_once '_parts/header.php'; ?>
<?php require_once '_parts/nav.php'; ?>
<?php

$response = $client->apiStatus();

echo $response->getBody();

?>
<?php require_once '_parts/footer.php'; ?>