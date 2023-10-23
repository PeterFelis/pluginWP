<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css.css">
    <title>Plaatjes</title>
</head>

<body>

    <div class="container-fluid">
        <nav class="menu">
            <button id="open-menu-btn" class="menu-icon">&#9776; Open Menu</button>
            <ul class="menu-items">
                <li><a href="#">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#">Portfolio</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
    </div>

    <?php include_once('db.php');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, image_url, image_text FROM ImageDetails";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<div class='image-grid'>";
        while ($row = $result->fetch_assoc()) {
            echo "<div>";
            $image_url = $row["image_url"];
            $small_image_url = str_replace('original', 'image-small', $image_url);
            $medium_image_url = str_replace('original', 'image-medium', $image_url);
            $large_image_url = str_replace('original', 'image-large', $image_url);
            echo "<picture>";
            echo "<source srcset='" . $small_image_url . "' media='(max-width: 767px)'>";
            echo "<source srcset='" . $medium_image_url . "' media='(min-width: 768px) and (max-width: 1023px)'>";
            echo "<source srcset='" . $large_image_url . "' media='(min-width: 1024px)'>";
            echo "<img src='" . $image_url . "' alt='Fallback Image' loading='lazy' data-id='" . $row["id"] . "'>";
            echo "<p>" . $row["image_text"] . "</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>
    <script>
        // Track whether an image was double-clicked
        let dblClickedImageId = null;

        document.querySelectorAll('.image-grid img').forEach(img => {
            // Listen for a double click event on each image
            img.addEventListener('dblclick', function() {
                dblClickedImageId = this.dataset.id;
                console.log(dblClickedImageId);
            });
            img.addEventListener('click', function() {
                dblClickedImageId = null;
            });
        });

        document.addEventListener('keydown', function(e) {
            // Listen for a 'Del' key press
            console.log(e.key);
            if (e.key === 'Delete' && dblClickedImageId) {
                if (confirm('Are you sure you want to delete this image?')) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_image.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send('id=' + encodeURIComponent(dblClickedImageId));

                    xhr.onload = function() {
                        if (xhr.status == 200 && xhr.responseText == 'success') {
                            // Image deletion was successful. Remove the image element from the page.
                            document.querySelector(`img[data-id="${dblClickedImageId}"]`).parentElement.remove();
                        } else {
                            // Something went wrong.
                            alert('Failed to delete image.');
                        }
                    };
                } else { // Reset the dblClickedImageId
                    dblClickedImageId = null;
                }
            } else {
                // Reset the dblClickedImageId
                dblClickedImageId = null;
            }
        });

        const openMenuBtn = document.getElementById('open-menu-btn');
        const menuItems = document.querySelector('.menu-items');

        openMenuBtn.addEventListener('click', function() {
            menuItems.classList.toggle('show');
        });
    </script>
    </div>
</body>

</html>