<?php
require_once "pdo.php";
session_start();
if (!isset($_SESSION['name']) || strlen($_SESSION['name']) < 1||!isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) < 1)
{
  die('ACCESS DENIED');
  return;
}
// Guardian: Make sure that profile_id is present
if (!isset($_REQUEST['profile_id'])) {
  $_SESSION["error"] = "Missing profile_id";
  header( 'Location: index.php' ) ;
  return;
}

// for profile data read
$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz and user_id=:uid");
$stmt->execute(array(":xyz" => $_REQUEST['profile_id'],":uid"=> $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = "Could Not load Profile";
    header( 'Location: index.php' ) ;
    return;
}

// for position data read
$stmt1 = $pdo->prepare("SELECT * FROM position where profile_id = :xyz ORDER BY rank");
$stmt1->execute(array(":xyz" => $_REQUEST['profile_id']));
$row1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);


$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$em = htmlentities($row['email']);
$he = htmlentities($row['headline']);
$su = htmlentities($row['summary']);
$pi = htmlentities($row['profile_id']);

// handle incomimg data
if (isset($_POST['first_name'])&& isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline'])&& isset($_POST['summary']))
{
  // data validate for profile
    if (strlen($_POST['first_name'])< 1 || strlen($_POST['last_name'])< 1 || strlen($_POST['email'])< 1 || strlen($_POST['headline'])< 1 || strlen($_POST['summary'])< 1)
    {
        $_SESSION["error"] = "All fields are required";
        header("Location: edit.php?profile_id=".$_POST['profile_id']);
        return;
    }

    $email = $_POST["email"];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $_SESSION["error"] = "Email must have an at-sign (@)";
            header("Location: edit.php?profile_id=".$_POST['profile_id']);
            return;
        }

// update data to profile
    $sql = "UPDATE profile SET first_name = :first_name, last_name = :last_name,
            email = :email, headline = :headline, summary=:summary
            WHERE profile_id = :profile_id AND user_id=:uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':uid'=> $_SESSION['user_id'],
        ':first_name' => $_POST['first_name'],
        ':last_name' => $_POST['last_name'],
        ':email' => $_POST['email'],
        ':headline' => $_POST['headline'],
        ':summary' => $_POST['summary'],
        ':profile_id' => $_POST['profile_id']));


//for valodate position
        for ($i=1; $i <= 9; $i++) {
          if (!isset($_POST['year'.$i])) continue;
          if (!isset($_POST['desc'.$i])) continue;
          $year=$_POST['year'.$i];
          $desc=$_POST['desc'.$i];
          if (strlen($year)==0||strlen($desc)==0) {
            $_SESSION["error"] = "All fields required!";
            header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
            return;
          }
          if (!is_numeric($year)) {
            $_SESSION["error"] = "Position year must be numeric!";
            header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
            return;
          }}

          // update data to profile position
// Clear out the old position entries
$stmt = $pdo->prepare('DELETE FROM Position
    WHERE profile_id=:pid');
$stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

// Insert the position entries
$rank = 1;
for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['year'.$i]) ) continue;
    if ( ! isset($_POST['desc'.$i]) ) continue;
    $year = $_POST['year'.$i];
    $desc = $_POST['desc'.$i];

    $stmt = $pdo->prepare('INSERT INTO Position
        (profile_id, rank, year, description)
    VALUES ( :pid, :rank, :year, :desc)');
    $stmt->execute(array(
        ':pid' => $_REQUEST['profile_id'],
        ':rank' => $rank,
        ':year' => $year,
        ':desc' => $desc)
    );
    $rank++;
}

$_SESSION['success'] = 'Record updated';
header( 'Location: index.php' ) ;
return;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Sayed Makhdum Ullah- autosdb</title>
<script
  src="https://code.jquery.com/jquery-3.2.1.js"
  integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE="
  crossorigin="anonymous"></script>

<script type="text/javascript" src="engine1/jquery.js"></script>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Editing profile for <?=htmlentities($_SESSION['name']); ?> </h1>
<?php
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
 ?>
<form method="post" action="edit.php">
<input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']) ?>">
<p>first_name:
<input type="text" name="first_name" size="60" value="<?= $fn ?>"></p>
<p>last_name:
<input type="text" name="last_name" size="60" value="<?= $ln ?>"></p>
<p>email:
<input type="text" name="email" size="30" value="<?= $em ?>"></p>
<p>headline:
<input type="text" name="headline" size="60" value="<?= $he ?>"></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80" value=""><?= $su ?></textarea></p>


<?php
echo '<p>Position: <input type="submit" id="addPos" value="+"/>'."\n";
echo '<div id="position_fields">'."\n";
$countPos=0;
if ( $row1 == false ) {
    echo '<p style="color:blue">No position Retrived for this profile yet.You can add now</p>';
    }else
      {
   foreach($row1 as $row) {
    $countPos++;
    echo'<div id="position'.$countPos.'"> ';
    echo ('<p>Year: <input type="text" name="year'.$countPos.'" size="10" value="'. htmlentities($row['year']).'">'."\n");
    echo ('<input type="button" value="-" ');
    echo ('onclick="$(\'#position' .$countPos. '\').remove();return false;">'."\n");
    echo  "</p>\n";
    echo ('<textarea name="desc'.$countPos.'" rows="8" cols="80">'.htmlentities($row['description'])."\n".'</textarea></p>');
    echo'</div>'."\n";
   }}
 ?>
</div>
</p>
<p><input type="submit" value="Save"/>
<a href="index.php">Cancel</a></p>
</form>

<script>
countPos = <?=$countPos ?>;
// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });
});
</script>

</div>
</body>
</html>
