<?php
// Always show errors while developing (turn OFF in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    die('All fields are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email format');
}

if (strlen($password) < 6) {
    die('Password must be at least 6 characters long');
}

// ✅ Validate file upload properly
if (!isset($_FILES['proof'])) {
    die('Proof file is required');
}

$file = $_FILES['proof'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => 'File too large (php.ini upload_max_filesize).',
        UPLOAD_ERR_FORM_SIZE  => 'File too large (MAX_FILE_SIZE in form).',
        UPLOAD_ERR_PARTIAL    => 'File upload was partial. Try again.',
        UPLOAD_ERR_NO_FILE    => 'No file selected.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk (permissions).',
        UPLOAD_ERR_EXTENSION  => 'File upload blocked by a PHP extension.',
    ];
    $msg = $errors[$file['error']] ?? ('Unknown upload error: ' . $file['error']);
    die($msg);
}

// ✅ Security: allow only specific file types
$allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
$originalName = $file['name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
    die('Invalid file type. Allowed: jpg, jpeg, png, pdf');
}

// ✅ Ensure uploads directory exists
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        die('Failed to create uploads folder. Check permissions.');
    }
}

// ✅ Generate safe unique filename (prevents overwrite & weird names)
$safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
$uniqueName = $safeBase . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

$absolutePath = $uploadDir . $uniqueName;

// Store relative path in DB (better than absolute server path)
$relativePathForDB = 'uploads/' . $uniqueName;

// ✅ Move uploaded file
if (!is_uploaded_file($file['tmp_name'])) {
    die('Upload failed: temp file is not a valid uploaded file.');
}

if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
    die('Failed to move uploaded file. Check folder permissions: ' . $uploadDir);
}

// ✅ Hash password AFTER validation
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// ✅ DB insert (with error checks)
$conn = new mysqli('localhost', 'root', '', 'Auth');
if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error);
}

$stmt = $conn->prepare("INSERT INTO users (username, email, password, proof) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}

$stmt->bind_param("ssss", $username, $email, $hashedPassword, $relativePathForDB);

if (!$stmt->execute()) {
    die('DB insert failed: ' . $stmt->error);
}

echo "Registration Successful. File saved as: " . htmlspecialchars($relativePathForDB);
$webPath = "/php_Wt/backend/uploads/" . $uniqueName;
echo "<br><a href='$webPath' download>Download your file</a>";

$stmt->close();
$conn->close();

?>