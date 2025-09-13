<?php

$CRUD = true;

if (isset($_GET['CRUD'])) {
    header("Location: CRUD/inicio.php");

}
else {
    session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: Chat_system/room.php");
    exit;
} else {
    header("Location: Chat_system/Userspace/login.php");
    exit;
}
    
}



