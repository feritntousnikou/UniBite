<?php

session_start();
header("Content-Type: application/json");
 
session_unset();
 
echo json_encode(["success" => true]);
?>