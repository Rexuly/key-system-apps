<?php
$targetDir = "uploads/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$file = $_FILES["fileToUpload"];
$targetFile = $targetDir . basename($file["name"]);
$fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

// Allow only safe types
$allowedTypes = ["jpg", "jpeg", "png", "txt", "pdf", "json"];
if (!in_array($fileType, $allowedTypes)) {
    die("❌ File type not allowed.");
}

// Limit file size (5 MB)
if ($file["size"] > 5000000) {
    die("❌ File is too large.");
}

// Rename to avoid conflicts
$newName = uniqid("file_", true) . "." . $fileType;
$targetFile = $targetDir . $newName;

if (move_uploaded_file($file["tmp_name"], $targetFile)) {
    echo "✅ File uploaded successfully: " . htmlspecialchars($newName);
} else {
    echo "❌ Error uploading your file.";
}
?>
