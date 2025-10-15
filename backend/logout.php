<?php
session_start();
session_destroy();
header("Location: /PekoPop/pages/login.php");
exit();