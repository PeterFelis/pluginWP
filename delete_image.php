<?php
include_once('db.php');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "SELECT image_url FROM ImageDetails WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $stmt->bind_result($image_url);
                $stmt->fetch();

                // Extract file path from URL
                $file_path = parse_url($image_url, PHP_URL_PATH);
                $file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path;

                // Delete the file
                if (unlink($file_path)) {
                    // File deletion successful. Proceed to delete the record from the database.
                    $stmt->close();

                    // Delete the record from the database
                    $sql = "DELETE FROM ImageDetails WHERE id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("i", $id);
                        if ($stmt->execute()) {
                            echo 'success';
                        } else {
                            echo 'error';
                        }
                        $stmt->close();
                    }
                } else {
                    echo 'Error deleting image file';
                }
            } else {
                echo 'Error executing statement';
            }
            $stmt->close();
        } else {
            echo 'Error preparing delete statement';
        }
    }
}

$conn->close();
