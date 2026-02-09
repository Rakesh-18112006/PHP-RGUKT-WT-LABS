
<?php 
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if( !$email || !$password || !$username ){
    die('Please fill all the required fields!');
}
if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
    die('Invalid email format');
}

if ( strlen($password) < 6 ) {
    die('Password must be at least 6 characters long'); 
}

$password = password_hash($password, PASSWORD_BCRYPT);

$conn = new mysqli('localhost','root','','Auth');
if($conn->connect_error){
    die('Connection Failed : '.$conn->connect_error);
}else{
    $stmt = $conn->prepare("insert into users(username, email, password) values(?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
    echo "Registration Successful";
    $stmt->close();
    $conn->close();
}   

?>

