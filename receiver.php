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

  //hiermee kan het formaat op de afbeelding gezet worden voor test. code even bewaren
  // Add text based on the image size
  // $size_text = "";
  // switch ($index) {
  //   case 0: // Small size
  //     $size_text = "Small";
  //     break;
  //   case 1: // Medium size
  //     $size_text = "Medium";
  //     break;
  //   case 2: // Large size
  //     $size_text = "Large";
  //     break;
  //   default:
  //     break;
  // }

  // // Set text color
  // $text_color = imagecolorallocate($resized_image, 255, 255, 255); // White color

  // // Set font size
  // $font_size = 4;

  // // Set text position
  // $text_x = 10;
  // $text_y = 20;

  // // Write text on the image
  // imagestring($resized_image, $font_size, $text_x, $text_y, $size_text, $text_color);

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
