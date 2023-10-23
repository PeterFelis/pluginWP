<?php

// connect to the database
$conn = mysqli_connect('sql214.hostingdiscounter.nl', 'peter', 'ErjED8nWfP', 'company');

if ($conn->connect_errno) {
    echo "Failed to connect to MySQL: " . $conn->connect_error;
    exit();
}
