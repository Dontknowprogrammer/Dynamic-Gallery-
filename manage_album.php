<?php

@include 'config.php'; // Include your database configuration
@include 'navbar.php';

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location: login_form.php'); // Redirect to the login page if the admin is not logged in
}

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'dgs';
$connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle album creation, renaming, and removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_album'])) {
        $newAlbumName = mysqli_real_escape_string($connection, $_POST['new_album_name']);

        // Perform the album creation operation in your database
        $sql = "INSERT INTO albums (name,created_at) VALUES ('$newAlbumName',CURRENT_TIMESTAMP)";
        if (mysqli_query($connection, $sql)) {
            echo "Album created successfully";
        } else {
            echo "Album creation failed";
        }
    } elseif (isset($_POST['action'])) {
        if ($_POST['action'] === 'rename_album') {
            // Handle album renaming
            $newAlbumName = mysqli_real_escape_string($connection, $_POST['new_album_name']);
            $albumId = (int)$_POST['album_id'];

            // Perform the renaming operation in your database
            $sql = "UPDATE albums SET name = '$newAlbumName' WHERE album_id = $albumId";
            if (mysqli_query($connection, $sql)) {
                echo "Renaming successful";
            } else {
                echo "Renaming failed";
            }
        } elseif ($_POST['action'] === 'remove_album') {
            // Handle album removal
            $albumId = (int)$_POST['album_id'];
        
            // Retrieve images for the selected album from the database
            $sql = "SELECT image_id FROM images WHERE album_id = $albumId";
            $result = mysqli_query($connection, $sql);
        
            if ($result) {
                // Delete each image file from the server
                while ($row = mysqli_fetch_assoc($result)) {
                    $imageId = $row['image_id'];
                    $imageFileName = "image_$imageId.*";
                    $imageFilePath = __DIR__ . "C:/xampp/htdocs/dgs/images/$imageFileName";
                    $imageFilePath = str_replace('/', DIRECTORY_SEPARATOR, $imageFilePath);
                    if (file_exists($imageFilePath)) {
                        if (unlink($imageFilePath)) {
                            echo "File $imageFileName deleted successfully.<br>";
                        } else {
                            echo "Error deleting file $imageFileName.<br>";
                        }
                    } //else {
                        //echo "File $imageFileName does not exist.<br>";
                    //}
                }
        
                // Remove images from the database
                $sql = "DELETE FROM images WHERE album_id = $albumId";
                if (mysqli_query($connection, $sql)) {
                    // Remove the album from the database
                    $sql = "DELETE FROM albums WHERE album_id = $albumId";
                    if (mysqli_query($connection, $sql)) {
                        echo "Removal successful";
                    } else {
                        echo "Error removing album";
                    }
                } else {
                    echo "Error removing images";
                }
            } else {
                echo "Error fetching images for removal";
            }
        }
    }
}

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchQuery = mysqli_real_escape_string($connection, $_POST['search_query']);

    // Search for albums
    $searchAlbumsSql = "SELECT name, created_at FROM albums WHERE name LIKE '%$searchQuery%'";
    $searchAlbumsResult = mysqli_query($connection, $searchAlbumsSql);
    
    // Search for images
    $searchImagesSql = "SELECT i.image_id, i.image_data, a.name AS album_name 
                        FROM images i
                        JOIN albums a ON i.album_id = a.album_id
                        WHERE a.name LIKE '%$searchQuery%'";
    $searchImagesResult = mysqli_query($connection, $searchImagesSql);

    // Display search results for albums
    if ($searchAlbumsResult && mysqli_num_rows($searchAlbumsResult) > 0) {
        echo "<h3>Search Results for Albums:</h3>";
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($searchAlbumsResult)) {
            echo "<li>{$row['name']} (Created at: {$row['created_at']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No matching albums found.</p>";
    }

    // Display search results for images
    if ($searchImagesResult && mysqli_num_rows($searchImagesResult) > 0) {
        echo "<h3>Search Results for Images:</h3>";
        echo "<div class='image-container'>";
        while ($row = mysqli_fetch_assoc($searchImagesResult)) {
            $base64Image = $row['image_data'];
            $imageSrc = "data:image/*;base64," . $base64Image;
            $albumName = $row['album_name'];
            echo "<img src='$imageSrc' alt='Album Image'>";
            echo "<p>Album: $albumName</p>";
        }
        echo "</div>";
    } else {
        echo "<p>No matching images found.</p>";
    }
}

// Retrieve the list of albums from the database
$albums = [];
$sql = "SELECT album_id, name,created_at FROM albums";
$result = mysqli_query($connection, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $albums[] = $row;
    }
}

//code to upload images
if (isset($_POST['upload_image'])) {
    $album_id = $_POST['album_id'];
    
    if ($_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['image_file']['tmp_name']);
        $base64Image = base64_encode($imageData);

        // Insert the Base64 encoded image data into the database
        $sql = "INSERT INTO images (album_id, image_data) VALUES (?, ?)";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "is", $album_id, $base64Image);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Image uploaded successfully');</script>";
        } else {
            echo "Image upload failed";
        }
    } else {
        echo "Error uploading the image";
    }
}

// Function to create a zip file for an album
function createAlbumZip($albumId, $connection)
{
    // Retrieve images for the selected album from the database
    $sql = "SELECT * FROM images WHERE album_id = $albumId";
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
     
        $zipFileName = "album_" . $albumId . ".zip";
        $zip = new ZipArchive();
        if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
       
        // Loop through images and save them to the temporary directory
        while ($row = mysqli_fetch_assoc($result)) {
            $base64Image = $row['image_data'];
            $imageData = base64_decode($base64Image);
            $imageName = "image_".$row['image_id'].".jpg";
            $zip->addFromString($imageName, $imageData);
        }


    }
    else{
    
        echo "Failed to create zip file";
        exit(); 
}
   
        $zip->close();
return $zipFileName;
   
}

}


// Function to delete a directory and its contents
function deleteDirectory($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object)) {
                    deleteDirectory($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        rmdir($dir);
    }
}

// Download album button handling
if (isset($_POST['download_album'])) {
    $downloadAlbumId = $_POST['download_album_id'];
    $zipFileName = createAlbumZip($downloadAlbumId, $connection);

    if (file_exists($zipFileName)) {
        // Check file permissions
        if (is_readable($zipFileName)) {
            // Proceed with download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Length: ' . filesize($zipFileName));
            ob_clean();
            flush();
            readfile($zipFileName);
            unlink($zipFileName);
            exit();
        } else {    
            echo 'File is not readable. Check permissions.';
        }
    } else {
        echo 'File does not exist.';
    }
}
//code to delete images
if (isset($_POST['delete_image'])) {
    $imageId = (int)$_POST['image_id'];

    // Retrieve the image data for the selected image
    $sql = "SELECT image_data FROM images WHERE image_id = $imageId";
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $base64Image = $row['image_data'];

        // Delete the image file from the server
        $imageFileName = "image_$imageId.*";
        $imageFilePath = __DIR__ . "C:/xampp/htdocs/dgs/images/$imageFileName";
        $imageFilePath = str_replace('/', DIRECTORY_SEPARATOR, $imageFilePath);

        if (file_exists($imageFilePath)) {
            if (unlink($imageFilePath)) {
                echo "File $imageFileName deleted successfully.<br>";
            } else {
                echo "Error deleting file $imageFileName.<br>";
            }
         } //else {
        //     echo "File $imageFileName does not exist.<br>";
        // }

        // Remove the image from the database
        $deleteSql = "DELETE FROM images WHERE image_id = $imageId";
        if (mysqli_query($connection, $deleteSql)) {
            echo "Image deleted successfully.";
        } else {
            echo "Error deleting image from the database.";
        }
    } else {
        echo "Error fetching image data.";
    }
}

// Displaying images with delete and download option
if (isset($_POST['view_images'])) {
    $viewAlbumId = $_POST['view_album_id'];


    // Retrieve images for the selected album from the database
    $sql = "SELECT image_id, image_data FROM images WHERE album_id = $viewAlbumId";
    $result = mysqli_query($connection, $sql);

    if ($result) {
        echo "<h2>Images in the selected album</h2>";
        echo "<div class='image-container'>";
        echo "<div id='imageCarousel' class='carousel slide' data-ride='carousel'>";
        echo "<div class='carousel-inner'>";

        $firstImage = true;
        while ($row = mysqli_fetch_assoc($result)) {
            $base64Image = $row['image_data'];
            $imageSrc = "data:image/*;base64," . $base64Image;
            $imageId = $row['image_id'];

            // Add each image to the Carousel with delete and download option
            echo "<div class='carousel-item" . ($firstImage ? " active" : "") . "'>";
            echo "<img class='d-block w-100' src='$imageSrc' alt='Album Image'>";

            // Add buttons for each image on the same line
            echo "<div class='carousel-caption d-none d-md-block'>";
            echo "<form class='d-inline' method='POST' action='manage_album.php'>";
            echo "<input type='hidden' name='image_id' value='$imageId'>";
            echo "<button type='submit' name='delete_image' class='btn btn-danger'>Delete</button>";
            echo "</form>";

            echo "<a href='$imageSrc' download='image.jpg' class='btn btn-primary ml-2'>Download</a>";
            
            // Custom close button (cross)
            echo "<button type='button' class='btn btn-secondary ml-2' onclick='closeImageViewer()'>&times;</button>";
            
            echo "</div>";

            echo "</div>";

            $firstImage = false;
        }

        echo "</div>";
        echo "<a class='carousel-control-prev' href='#imageCarousel' role='button' data-slide='prev'>";
        echo "<span class='carousel-control-prev-icon' aria-hidden='true'></span>";
        echo "<span class='sr-only'>Previous</span>";
        echo "</a>";
        echo "<a class='carousel-control-next' href='#imageCarousel' role='button' data-slide='next'>";
        echo "<span class='carousel-control-next-icon' aria-hidden='true'></span>";
        echo "<span class='sr-only'>Next</span>";
        echo "</a>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "Error fetching images";
    }
}

// JavaScript to close the image viewer
echo "<script>";
echo "function closeImageViewer() {";
    echo "  window.location.href = 'manage_album.php';";
    echo "}";    
echo "</script>";

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE-edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Albums</title>

     <!-- Bootstrap CSS -->
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>

    <!-- Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/style3.css">
   <link rel="stylesheet" href="css/style4.css">


</head>


<body>
<div class="container">
   <div class="content">

    <h2>Search Albums and Images</h2>
    <form method="post" action="manage_album.php">
        <label for="search_query">Search:</label>
        <input type="text" name="search_query" id="search_query" placeholder="Enter search query">
        <button type="submit" name="search">Search</button>
    </form>   
   <h2>Create New Album</h2>
      <form method="post" action="manage_album.php">
         <input type="text" name="new_album_name" placeholder="New Album Name" required>
         <button type="submit" name="create_album">Create Album</button>
      </form>
      <h2>Manage Albums</h2>
               <form method="post" action="manage_album.php">
               <select name="album_id" id="album_id" required>
                <?php foreach ($albums as $album) : ?>
                    <option value="<?php echo $album['album_id']; ?>"><?php echo $album['name']; ?></option>
                <?php endforeach; ?>
                </select>
                  <input type="text" name="new_album_name" placeholder="New Album Name">
                  <button type="submit" name="action" value="rename_album">Rename</button>
                  <button type="submit" name="action" value="remove_album">Remove</button>
                  <!-- <button type="submit" name="submit">Apply</button> -->
               </form>
               <!--form for uploading image-->
               <form method="POST" enctype="multipart/form-data" action="manage_album.php">
                <h2>Upload Image</h2>
    <label for="image_file">Select Image to Upload:</label>
    <input type="file" name="image_file" id="image_file" required>

    <!-- Dropdown menu for selecting the album -->
    <label for="album_id">Select Album:</label>
    <select name="album_id" id="album_id" required>
    <?php
      foreach ($albums as $album) {
         echo "<option value='" . $album['album_id'] . "'>" . $album['name'] . "</option>";
      }
      ?>
    </select>

    <button type="submit" name="upload_image">Upload Image</button>
    </form>
    <form method="POST" action="manage_album.php">
    <h2>View Images</h2>
    <label for="view_album_id">Select Album to View Images:</label>
    <select name="view_album_id" id="view_album_id" required>
        <?php foreach ($albums as $album) : ?>
            <option value="<?php echo $album['album_id']; ?>"><?php echo $album['name']; ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="view_images" action="manage_album.php">View Images</button>
</form>
<!--Form for downloading the album-->
<form method="POST" action="manage_album.php">
    <h2>Download Album</h2>
    <label for="download_album_id">Select Album to Download:</label>
    <select name="download_album_id" id="download_album_id" required>
        <?php foreach ($albums as $album) : ?>
            <option value="<?php echo $album['album_id']; ?>"><?php echo $album['name']; ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="download_album">Download Album</button>
</form>
   </div>
</div>

</body>
</html>