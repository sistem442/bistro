<?php

include "database_connect.php";

$q = "SELECT id,cena FROM artikli";
$result = $mysqli->query($q);
while ($obj=mysqli_fetch_object($result)){
	$q2="SELECT datum,id_artikla FROM stanje WHERE id_artikla = $obj->id AND datum < '2015-01-01'";
	echo $q2."</br>";
	$result2 = $mysqli->query($q2);
		while ($obj2=mysqli_fetch_object($result2)){
			$q1 = "UPDATE stanje SET cena = $obj->cena WHERE id_artikla = $obj2->id_artikla and datum = '$obj2->datum'";
			echo $q1."</br>";
			$result1 = $mysqli->query($q1);
		}
	}
