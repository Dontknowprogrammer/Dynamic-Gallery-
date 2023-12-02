<?php

@include 'config.php';
@include 'navbar2.php';

session_start();

if(!isset($_SESSION['artist_name'])){
   header('location:login_form.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>artist page</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<div class="container">

   <div class="content">
      <h3>hi, <span>artist</span></h3>
      <h1>welcome <span><?php echo $_SESSION['artist_name'] ?></span></h1>
      <p>this is an artist page</p>
   </div>

</div>

</body>
</html>