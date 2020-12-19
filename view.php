<?php
require_once "pdo.php";
session_start();

if (!isset($_SESSION['name']) || strlen($_SESSION['name']) < 1||!isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) < 1)
{
  die('ACCESS DENIED');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

$stmt1 = $pdo->prepare("SELECT * FROM position where profile_id = :xyz ORDER BY rank");
$stmt1->execute(array(":xyz" => $_GET['profile_id']));
$row1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head><title>Sayed Makhdum Ullah-  autosdb</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Profile Information</h1>

<?php
if (isset($_SESSION['name']))
{
    echo "<h3>Greetings: ";
    echo $_SESSION['name'];
    echo "</h3>\n";
}
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) {
    echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
    unset($_SESSION['success']);
}

    echo "First Name:   ".htmlentities($row['first_name']);
    echo "<br>";
    echo "\n Last Name:".htmlentities($row['last_name']);
        echo "<br>";
    echo "\n Email:".htmlentities($row['email']) ;
        echo "<br>";
    echo "\n Headline:".(htmlentities($row['headline']));
        echo "<br>";
    echo "\n Summary:".htmlentities($row['summary']) ;
        echo "<br>";
    echo "<h4>Position</h4>";
    if ( $row1 == false ) {
      echo '<p style="color:red">No position defined</p>';
      }else
        {
     foreach($row1 as $row) {
     echo ("  <ul><li>"."Year: ".htmlentities($row['year'])."   Rank:".htmlentities($row['rank'])."   Description: ".htmlentities($row['description'])."</li></ul>");
     }}
?>
<p><a href="index.php">Done</a></p>

  </div>
</body>
</html>
