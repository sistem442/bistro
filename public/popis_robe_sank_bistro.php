<?php
include "database_connect.php";
$result = $mysqli->query("SELECT * FROM artikli WHERE status = 'aktivan' ORDER BY lokacija,poredak ASC   ");

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Popis robe u sanku - bistro</title>
<style>
	td{font-size:11px;border: solid 1px;}
</style>
</head>

<body>
<table cellpadding="0px" cellspacing="0px">
<?php
while ($obj=mysqli_fetch_object($result)){           
                echo "<input type='text' value='$obj->ime'/><br>";
			}// kraj petlje koja lista artikle
?>
</table>
</body>
</html>