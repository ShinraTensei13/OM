<?php
/* Database connexion */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'omcttunijorani');

/* Connexion à la base de données */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
mysqli_set_charset($link, "utf8");
/* verifier connection */
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>
<?php
$db = new PDO('mysql:host=localhost;dbname=omcttunijorani;charset=utf8','root', '');
?>