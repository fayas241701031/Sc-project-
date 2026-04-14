<?php
session_start();
session_unset();
session_destroy();
header('Location: /ecommerce-App/index.php');
exit;
