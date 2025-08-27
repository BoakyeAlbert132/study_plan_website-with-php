<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit();
}
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $activity_description = $_POST['activity_description'];
    $study_date = $_POST['study_date'];
    $subject = $_POST['subject'];
    $study_time = $_POST['study_time'];
    $imageData = $_POST['image_data'];

    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    $decodedImage = base64_decode($imageData);

    $fileName = 'activity_' . uniqid() . '.png';
    $filePath = 'uploads/' . $fileName;

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if (file_put_contents($filePath, $decodedImage)) {
        $sql = "INSERT INTO activities (user_id, activity_description, image_path, study_date, subject, study_time) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $user_id, $activity_description, $filePath, $study_date, $subject, $study_time);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Activity saved successfully.']);
        } else {
            unlink($filePath);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save activity to database: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save image file.']);
    }
    $conn->close();
}
?>