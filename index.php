<?php

include('config.php');
session_start();

if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_type'])) {
  header('location:login_form.php');
}

if ($_SESSION['user_type'] == 'admin') {
  header('location:admin_page.php');
} else {
  header('location:user_page.php');
}

?>
