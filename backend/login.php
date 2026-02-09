
<?php 
 $email = $_POST['email'] ?? '';
 $password = $_POST['password'] ?? '';

 if( !$email || !$password ){
     die('Please fill all the required fields!');
 }
 if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
    die("please enter a valid email address");
 }

    $conn = new mysqli('localhost','root','','Auth');
    if($conn->connect_error){
        die('Connection Failed : '.$conn->connect_error);
    }else{
        $stmt = $conn->prepare("select * from users where email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt_result = $stmt->get_result();
        if($stmt_result->num_rows > 0){
            $data = $stmt_result->fetch_assoc();
    
            if( password_verify($password, $data['password']) ){
                echo "Login Successful";
            }else{
                echo "Invalid Email or Password";
            }
        }else{
            echo "Invalid Email or Password";
        }
    }
?>