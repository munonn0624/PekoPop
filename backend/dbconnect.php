<?php

$conn = mysqli_connect("localhost","root","","pekopop");

if (!$conn) {
    header("Location: /PekoPop/pages/error.php");
    exit;
}

