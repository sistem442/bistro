<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br /><a href='login.php'>Prijava</a>";
}
$diffrence_date = $_POST['diffrence_date'];
include "database_connect.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Razlika u ceni</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<style>
	td {width:200px;border:solid 1px; padding:2px; text-align:center}
	tr:nth-child(odd)
	{
	background:#fff;
	}
	tr:nth-child(even)
	{
	background:#CCC;
}
</style>

</head>
<body class="container2">
	<div class="page-holder">
        <div class="container2">
        	<div class="naslov">Razlika u ceni <div class="datum"><?php echo date("M-Y", strtotime($diffrence_date));?></div></div>
			<?php 
            	
			$modifier = array(0,20,15,5,0,5,0,5,0,5,0,5,0,5,0,0);
			$milk_modifier = array(0,0.05,0.05,0,0,0,0,0,0,0.07,0.07,0.07,0.07,0.07,0.07,0.09);
			$sugar_modifier = array(0,2,2,2,2,2,2,2,2,0,0,0,0,2,2,0);
			$ukupna_razlika = 0;
			$milk = 0;
			$sugar = 0;
			
			echo '<table cellpadding="0" cellspacing="0">';
			echo '	<tr>
						<td>Ime artikla</td>
						<td>Jedinica mere</td>
						<td>Broj prodatih artikala</td>
						<td>Utrošeno mleko L</td>
						<td>Utrošеn šećer po kom</td>
						<td style="font-weight:bold">Razlika u ceni</td>
						<!--td>Mnozilac razlike</td>
						<td>Mnozilac mleka</td>
						<td>Mnozilac secera</td-->
					</tr>';
			for($i = 1;$i<16;$i++){
				$query = "	SELECT SUM(kolicina) AS kolicina_za_mesec 
							FROM prodaja 
							WHERE datum <= '$diffrence_date-31' AND datum >= '$diffrence_date-01' AND id_artikla = $i";
							
				$query2 = "	SELECT ime 
							FROM artikli 
							WHERE id = $i";
							
			//echo $query.'</br>'; 
				$result = $mysqli->query($query);
				$obj = $result->fetch_object();
				$result2 = $mysqli->query($query2);
				$obj2 = $result2->fetch_object();
		
		
				echo '<tr>
						<td>'.$obj2->ime.'</td>
						<td> kom </td>
						<td>'.$obj->kolicina_za_mesec.'</td>
						<td>'.number_format($obj->kolicina_za_mesec * $milk_modifier[$i],2).'</td>
						<td>'.number_format($obj->kolicina_za_mesec * $sugar_modifier[$i],2).'</td>
						<td style="font-weight:bold" >'.number_format($obj->kolicina_za_mesec * $modifier[$i]).'</td>
						<!--td>'.$modifier[$i].'</td>
						<td>'.$milk_modifier[$i].'</td>
						<td>'.$sugar_modifier[$i].'</td-->
					</tr>';
				
				$ukupna_razlika = $ukupna_razlika + $obj->kolicina_za_mesec * $modifier[$i];
				$milk = $milk + $obj->kolicina_za_mesec * $milk_modifier[$i];
				$sugar = $sugar + $obj->kolicina_za_mesec * $sugar_modifier[$i];
			}
			echo '	<tr style="font-weight:bold">
						<td></td>
						<td></td>
						<td></td>
						<td>'.number_format($milk,2).'</td>
						<td>'.number_format($sugar).'</td>
						<td>'.number_format($ukupna_razlika).'</td>
						<!--td></td>
						<td></td>
						<td></td-->
					</tr>
			</table>';
            ?>
		</div><!--container-->
	</div><!-- page holder-->
</body>
</html>