<?php
header('Access-Control-Allow-Origin: chrome-extension://ilchkihclkdccpgappmbapajhgfbpegc'); // Replace with your actual extension ID
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$target_dir = "plaatjes/";
$base_filename = time();
$target_files = array(
  'image-small.jpg',
  'image-medium.jpg',
  'image-large.jpg'
);

$base64Image = $_POST["image"];
$base64Image = str_replace('data:image/jpeg;base64,', '', $base64Image);
$base64Image = str_replace(' ', '+', $base64Image);
$imageData = base64_decode($base64Image);

$filetype = finfo_open();
$mime_type = finfo_buffer($filetype, $imageData, FILEINFO_MIME_TYPE);

if ($mime_type == "image/jpeg") {
  $imageFileType = "jpg";
} elseif ($mime_type == "image/png") {
  $imageFileType = "png";
} elseif ($mime_type == "image/webp") {
  $imageFileType = "webp";
} else {
  exit('Unknown image type');
}

// Save the original image
$original_filename = $target_dir . $base_filename . '-original.' . $imageFileType;
if (!file_put_contents($original_filename, $imageData)) {
  exit("Sorry, there was an error uploading your file.");
}


// 24-10-2023
// tussengevoegd door chat om afbeelding in medialibrary van wp te zetten

// Nieuwe code begint hier
include_once(ABSPATH . 'wp-admin/includes/media.php');
include_once(ABSPATH . 'wp-admin/includes/file.php');
include_once(ABSPATH . 'wp-admin/includes/image.php');

$bestandspad = $original_filename;  // Zorg ervoor dat dit het volledige pad naar het bestand is
$file_array = array(
  'name'     => basename($bestandspad),
  'tmp_name' => $bestandspad,
);

// Upload het bestand naar de Media Library
$attachment_id = media_handle_sideload($file_array, 0);

if (is_wp_error($attachment_id)) {
  // Foutafhandeling
  echo $attachment_id->get_error_message();
} else {
  // Maak een nieuw bericht van het aangepaste berichttype
  $post_id = wp_insert_post(array(
    'post_type'   => 'afbeelding',
    'post_title'  => preg_replace('/\.[^.]+$/', '', basename($bestandspad)),
    'post_status' => 'publish',
  ));

  // Stel de geüploade afbeelding in als de uitgelichte afbeelding voor het bericht
  set_post_thumbnail($post_id, $attachment_id);
}

// ... Rest van je code ...





include_once('db.php');

$image_url = "https://felis.nl/" . $original_filename; // Replace with your server's actual URL
$text = mysqli_real_escape_string($conn, $_POST["text"]);
$tags = mysqli_real_escape_string($conn, $_POST["tags"]);

echo "Received tags: " . $tags;

$sql = "INSERT INTO ImageDetails (image_url, image_text, image_tags) VALUES ('$image_url', '$text', '$tags')"; // Make sure your table has a column for tags

if ($conn->query($sql) === TRUE) {
  echo "Record inserted successfully";
} else {
  echo "Error inserting record: " . $conn->error;
}

// Generate and save different image sizes
foreach ($target_files as $index => $target_file) {
  $image = imagecreatefromstring($imageData);
  $width = imagesx($image);
  $height = imagesy($image);

  $new_width = 0;
  $new_height = 0;

  // Calculate new dimensions based on the target file index
  switch ($index) {
    case 0: // Small size
      $new_width = 320;
      $new_height = round($height * (320 / $width));
      break;
    case 1: // Medium size
      $new_width = 640;
      $new_height = round($height * (640 / $width));
      break;
    case 2: // Large size
      $new_width = 1024;
      $new_height = round($height * (1024 / $width));
      break;
    default:
      break;
  }

  $resized_image = imagecreatetruecolor($new_width, $new_height);
  imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);



  $resized_filename = $target_dir . $base_filename . '-' . $target_file;
  switch ($imageFileType) {
    case 'jpg':
      imagejpeg($resized_image, $resized_filename, 90);
      break;
    case 'png':
      imagepng($resized_image, $resized_filename);
      break;
    case 'webp':
      imagewebp($resized_image, $resized_filename);
      break;
    default:
      break;
  }

  imagedestroy($image);
  imagedestroy($resized_image);
}

$conn->close();
