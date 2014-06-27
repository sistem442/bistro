<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
	die;
}
include "database_connect.php";
$query = "SELECT * 
		FROM log 
		WHERE date_time >='".date('Y-m-d', strtotime('today - 30 days'))."'";
$result = $mysqli->query($query);
//echo $query."</br>";

echo "
<a href='adminIndex.php'>Glavni meni</a></br>
Prikazuju se operacije nad bazom u poslednjih mesec dana.</br>
<table border='2px solid' cellpadding='10px'><tr><td>ID broj</td><td>Datum i vreme</td><td>Log zapis</td></tr>";
while ($obj=mysqli_fetch_object($result))
{
	echo "<tr><td>$obj->id</td><td>$obj->date_time</td><td>$obj->query</td></tr>";	
}
echo '</table>';