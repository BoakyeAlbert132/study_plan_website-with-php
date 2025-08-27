<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Study Plan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f4f8; }
        .neumorphic-card { background-color: #f0f4f8; border-radius: 30px; box-shadow: 10px 10px 20px #d9dce1, -10px -10px 20px #ffffff; padding: 40px; }
        .neumorphic-input { background: none; border: none; outline: none; flex-grow: 1; font-size: 16px; color: #333; padding: 5px 10px; box-shadow: inset 5px 5px 10px #d9dce1, inset -5px -5px 10px #ffffff; border-radius: 15px; }
        .neumorphic-button { background: linear-gradient(145deg, #7c3aed, #6d28d9); color: white; padding: 15px 30px; border-radius: 25px; font-weight: 600; box-shadow: 5px 5px 10px #d9dce1, -5px -5px 10px #ffffff; transition: all 0.3s ease; cursor: pointer; border: none; width: 100%; }
        .neumorphic-button:hover { box-shadow: inset 3px 3px 7px #5b21b6, inset -3px -3px 7px #9b72ff; transform: translateY(2px); }
        .activity-card { background-color: #f0f4f8; border-radius: 20px; box-shadow: 7px 7px 14px #d9dce1, -7px -7px 14px #ffffff; padding: 20px; transition: transform 0.3s ease-in-out; }
        .activity-card:hover { transform: translateY(-5px); }
        .video-container { position: relative; width: 100%; padding-top: 75%; }
        video, canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 15px; }
        .neumorphic-select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background: #f0f4f8;
            border: none;
            outline: none;
            padding: 10px 20px;
            border-radius: 15px;
            box-shadow: inset 5px 5px 10px #d9dce1, inset -5px -5px 10px #ffffff;
            color: #333;
        }
    </style>
</head>
<body class="p-8">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800">My Study Plan 📚</h1>
        <form id="logoutForm">
            <input type="hidden" name="logout" value="1">
            <button type="submit" class="neumorphic-button px-6 py-3">Logout</button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="neumorphic-card p-8 mb-8">
                <h2 class="text-2xl font-semibold mb-6 text-gray-700">Add a New Activity ✨</h2>
                <form id="activityForm">
                    <div class="space-y-6 mb-6">
                        <div class="neumorphic-input-wrapper">
                            <input type="date" id="activityDate" name="study_date" class="neumorphic-input w-full p-4 rounded-lg" required />
                        </div>
                        <div class="neumorphic-input-wrapper">
                            <select id="activitySubject" name="subject" class="neumorphic-select w-full p-4 rounded-lg">
                                <option value="">Select a Subject...</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Science">Science</option>
                                <option value="History">History</option>
                                <option value="Literature">Literature</option>
                                <option value="Coding">Coding</option>
                            </select>
                        </div>
                        <div class="neumorphic-input-wrapper">
                            <input type="time" id="activityTime" name="study_time" class="neumorphic-input w-full p-4 rounded-lg" required />
                        </div>
                        <div class="neumorphic-input-wrapper">
                             <textarea id="activityText" name="activity_description" rows="4" class="neumorphic-input w-full p-4 rounded-lg" placeholder="What are you studying today?" required></textarea>
                        </div>
                    </div>

                    <div class="mb-6 relative">
                        <div class="video-container rounded-lg overflow-hidden shadow-inner">
                            <video id="video" autoplay></video>
                            <canvas id="canvas" class="hidden"></canvas>
                        </div>
                        <div class="mt-4 flex space-x-4 justify-center">
                            <button type="button" id="startCamBtn" class="neumorphic-button w-auto">Start Camera</button>
                            <button type="button" id="captureBtn" class="neumorphic-button w-auto hidden">Capture Photo</button>
                        </div>
                    </div>
                    
                    <button type="submit" class="neumorphic-button">Save Activity</button>
                </form>
            </div>
            
            <div id="activitiesList" class="space-y-6">
                <h2 class="text-2xl font-semibold mb-6 text-gray-700">Your Activities</h2>
                <?php
                $user_id = $_SESSION['user_id'];
                $sql = "SELECT activity_description, image_path, study_date, subject, study_time FROM activities WHERE user_id = ? ORDER BY study_date DESC, study_time DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="activity-card flex flex-col md:flex-row items-center mb-4">';
                        echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="Activity Image" class="w-full md:w-24 h-48 md:h-24 rounded-lg object-cover md:mr-6 mb-4 md:mb-0 shadow-md">';
                        echo '<div>';
                        echo '<p class="text-gray-800 text-lg font-bold">' . htmlspecialchars($row['subject']) . '</p>';
                        echo '<p class="text-gray-800">' . htmlspecialchars($row['activity_description']) . '</p>';
                        echo '<p class="text-gray-500 text-sm mt-2">Date: ' . date('F j, Y', strtotime($row['study_date'])) . ' | Time: ' . date('h:i a', strtotime($row['study_time'])) . '</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-gray-500 text-center">No activities saved yet. Start by adding one!</p>';
                }
                $stmt->close();
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const startCamBtn = document.getElementById('startCamBtn');
        const captureBtn = document.getElementById('captureBtn');
        const activityForm = document.getElementById('activityForm');
        let stream;

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false });
                video.srcObject = stream;
                video.play();
                video.classList.remove('hidden');
                canvas.classList.add('hidden');
                startCamBtn.classList.add('hidden');
                captureBtn.classList.remove('hidden');
                captureBtn.textContent = 'Capture Photo';
            } catch (err) {
                console.error("Error accessing the camera: ", err);
                alert("Could not access camera. Please check your permissions.");
            }
        }

        startCamBtn.addEventListener('click', startCamera);

        captureBtn.addEventListener('click', () => {
            if (captureBtn.textContent === 'Retake Photo') {
                startCamera();
            } else {
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                video.classList.add('hidden');
                canvas.classList.remove('hidden');
                captureBtn.textContent = 'Retake Photo';
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
            }
        });

        activityForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(activityForm);
            const imageDataURL = canvas.toDataURL('image/png');

            if (imageDataURL === 'data:,') {
                alert('Please capture a photo first.');
                return;
            }

            formData.append('image_data', imageDataURL);

            fetch('save_activity.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Activity saved successfully!');
                    window.location.reload();
                } else {
                    alert('Error saving activity: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        document.getElementById('logoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('auth.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'index.html';
                }
            });
        });
    </script>
</body>
</html>