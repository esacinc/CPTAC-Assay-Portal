<?php
function maintenance() {
  $app = \Slim\Slim::getInstance();
  $app->render(
    'maintenance.php'
  );
}
?>