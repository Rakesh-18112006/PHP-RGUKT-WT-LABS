<?php
// Always show errors while developing (turn OFF in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

function render_page($title, $message, $is_error = false, $extra_html = '') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?></title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
        <div class="container" style="text-align: center;">
            <h2 style="<?php echo $is_error ? 'color: #DC2626;' : 'color: #059669;'; ?>">
                <?php echo htmlspecialchars($title); ?>
            </h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <?php echo $extra_html; ?>
            <div style="margin-top: 1.5rem;">
                <a href="../frontend/login.html" class="btn">Back to Login</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    render_page('Registration Failed', 'All fields are required', true);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render_page('Registration Failed', 'Invalid email format', true);
}

if (strlen($password) < 6) {
    render_page('Registration Failed', 'Password must be at least 6 characters long', true);
}

// ✅ Validate file upload properly
if (!isset($_FILES['proof'])) {
    render_page('Registration Failed', 'Proof file is required', true);
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
    render_page('Upload Failed', $msg, true);
}

// ✅ Security: allow only specific file types
$allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
$originalName = $file['name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
    render_page('Registration Failed', 'Invalid file type. Allowed: jpg, jpeg, png, pdf', true);
}

// ✅ Ensure uploads directory exists
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        render_page('Server Error', 'Failed to create uploads folder. Check permissions.', true);
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
    render_page('Upload Failed', 'Upload failed: temp file is not a valid uploaded file.', true);
}

if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
    render_page('Upload Failed', 'Failed to move uploaded file. Check folder permissions.', true);
}

// ✅ Hash password AFTER validation
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// ✅ DB insert (with error checks)
$conn = new mysqli('localhost', 'root', '', 'Auth');
if ($conn->connect_error) {
    render_page('Database Error', 'Connection Failed: ' . $conn->connect_error, true);
}

$stmt = $conn->prepare("INSERT INTO users (username, email, password, proof) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    render_page('Database Error', 'Prepare failed: ' . $conn->error, true);
}

$stmt->bind_param("ssss", $username, $email, $hashedPassword, $relativePathForDB);

if (!$stmt->execute()) {
    render_page('Registration Failed', 'DB insert failed: ' . $stmt->error, true);
}

$webPath = "/php_Wt/backend/uploads/" . $uniqueName;
$downloadLink = '<a href="' . htmlspecialchars($webPath) . '" class="btn" download>Download your file</a>';

render_page('Registration Successful', "Registration Successful. File saved as: " . htmlspecialchars($relativePathForDB), false, $downloadLink);

$stmt->close();
$conn->close();

?>