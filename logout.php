<?php 
session_start();
session_reset();
session_destroy();
header("Refresh: 1; Location: http://localhost/");
?>