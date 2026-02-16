<?php 
function render_page($message, $is_error = false) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Status</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
        <div class="container" style="text-align: center;">
            <h2 style="<?php echo $is_error ? 'color: #DC2626;' : 'color: #059669;'; ?>">
                <?php echo $is_error ? 'Error' : 'Success'; ?>
            </h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="../frontend/login.html" class="btn" style="margin-top: 1rem; display: inline-block;">Back to Login</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

 $email = $_POST['email'] ?? '';
 $password = $_POST['password'] ?? '';

 if( !$email || !$password ){
     render_page('Please fill all the required fields!', true);
 }
 if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
    render_page("Please enter a valid email address", true);
 }

    $conn = new mysqli('localhost','root','','Auth');
    if($conn->connect_error){
        render_page('Connection Failed : '.$conn->connect_error, true);
    }else{
        $stmt = $conn->prepare("select * from users where email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt_result = $stmt->get_result();
        if($stmt_result->num_rows > 0){
            $data = $stmt_result->fetch_assoc();
    
            if( password_verify($password, $data['password']) ){
                render_page("Login Successful");
            }else{
                render_page("Invalid Email or Password", true);
            }
        }else{
            render_page("Invalid Email or Password", true);
        }
    }
?>