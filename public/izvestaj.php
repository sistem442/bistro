<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
	die;
}
include "database_connect.php";
$_SESSION['datum'] = $_POST['datum_izvestaja'];
  $datum = $_POST['datum_izvestaja'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Bistro <?php echo date("d-m-Y", strtotime($datum));?></title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
<style type="text/css">
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
	table{border-collapse:collapse;}
	table, th, td{border: 1px solid black;}
	td{height:100%; text-align:center}
	.left{text-align:left}
	body{font:  14pt "Times New Roman"}	
	tr:nth-child(3n+0)
	{
		background:#CCC;
	}
</style>
</head>
<body>
<div class="container2">
  <div class="naslov">DNEVNI OBRAČUN PROMETA I ZALIHA ROBE U UGOSTITELJSTVU: <div class="datum"><?php echo date("d-m-Y", strtotime($datum));?></div></div>
  <table>
  	<thead>
    	<tr>
    		<td>ime</td>
            <td>jedinica mere</td>
            <td>cena</td>
            <td>stanje prethodnog dana</td>
            <td>primljena količina</td>
            <td>vrednost primljene robe</td>
            <td>ukupno</td>
            <td>prodata količina</td>
            <td>vrednost prodate robe</td>
            <td>zalihe</td>
            <td>vrednost zaliha na kraju dana</td>
       </tr>
    </thead> 
  <?php
  	//get presentation for current day
	$query = "SELECT prezentacija FROM prezentacija 
			  WHERE datum='".$datum."'";
	$result = $mysqli->query($query);
	if ($result->num_rows != 0){
		$result = $mysqli->query($query);
		$obj = $result->fetch_object();
		$prezentacija= $obj->prezentacija;
	}
	else $prezentacija = 0;
  
  	$query = "SELECT artikli.poredak,artikli.id,artikli.cena,artikli.ime FROM artikli 
			  WHERE artikli.status='aktivan' AND lokacija = 'bistro'; ";
	//echo $query.'</br>';
	$result = $mysqli->query($query);
	//echo 'broj artikala: '.$result->num_rows;
	$temp = 0;
	$pazar = 0;
	$korekcija = 0;
	$vrednost_zaliha_na_kraju_dana = 0;
	$vrednost_zaliha_na_kraju_dana_prethodni = 0;
	$primljena_kolicina = 0;
	$value_of_received_goods = 0;
	$artikli = array();
	
	while ($obj=mysqli_fetch_object($result))
	{
		//get walues for one article from artikli table
		$id = $obj->id;
		$ime = $obj->ime;
		$poredak = $obj->poredak;
	//amount and price of article for current day
		$query4 = "	SELECT kolicina, cena
					FROM stanje
			  		WHERE id_artikla = $obj->id AND datum <='$datum' 
					ORDER BY datum 
					DESC LIMIT 0,1 ";
		$result4 = $mysqli->query($query4);
		//echo $query4."<br/>";
		$stanje =mysqli_fetch_object($result4);
		$cena = $stanje->cena;
		//echo $stanje -> kolicina.'</br>';
		
		//get number of received articles for current row
		$query1 = "SELECT kolicina FROM prijem
			  WHERE id_artikla = $obj->id AND datum='$datum'";
		$result1 = $mysqli->query($query1);
		$kolicina = mysqli_fetch_object($result1);
		//echo $query1.'</br>';
		if ($result1->num_rows != 0){ 
			$prijemljena_kolicina = $kolicina->kolicina;
		}
		else $prijemljena_kolicina = 0;
	
		//get number of sold articles for current row
		$query2 = "SELECT kolicina FROM prodaja
			  	   WHERE id_artikla = $obj->id AND datum='$datum'";
		$result2 = $mysqli->query($query2);
		$prodaja =mysqli_fetch_object($result2);
		//echo $query2.'</br>';
		if ($result2->num_rows != 0){ 
			$prodata_kolicina = $prodaja->kolicina;
		}
		else $prodata_kolicina = 0;
		
		//amount of article received
		$query6 = "SELECT kolicina FROM prijem
			  WHERE id_artikla = $obj->id AND datum = '$datum'";
		$result6 = $mysqli->query($query6);
		$prijem =mysqli_fetch_object($result6);
		//echo $query3.'</br>';
		if ($result6->num_rows != 0){ 
			$suma_prijem = $prijem->kolicina*$cena;
		}
		else $suma_prijem = 0;
		
		$value_of_received_goods = $value_of_received_goods + $suma_prijem;
		
		//amount of article for previous day
		$query3 = "SELECT kolicina, cena FROM stanje
			  WHERE id_artikla = $obj->id AND datum<'$datum' ORDER BY datum DESC LIMIT 0,1";
		$result3 = $mysqli->query($query3);
		$stanje_p = mysqli_fetch_object($result3);
		//echo $query3.'</br>';
		if ($result3->num_rows != 0){ 
			$stanje_prethodni_dan = $stanje_p->kolicina;
			$cena_prethodni_dan = $stanje_p->cena;
		}
		else
		{
			$stanje_prethodni_dan = 0;
			$cena_prethodni_dan = 0;
		}
		//vrednost zaliha na kraju dana for previous day
		$vrednost_zaliha_na_kraju_dana_prethodni = $vrednost_zaliha_na_kraju_dana_prethodni + $stanje_prethodni_dan * $cena_prethodni_dan;
		
		
		
		//get kolicina from prodaja to calculate correction
		$query5 = "SELECT kolicina,id_artikla,ime 
					FROM prodaja JOIN artikli ON(artikli.id = prodaja.id_artikla)
			  		WHERE id_artikla = $obj->id AND datum ='$datum'
					 ";
		$result5 = $mysqli->query($query5);
		$kolicina_p =mysqli_fetch_object($result5);
		//echo $query5."<br/>";		
		if ($result5->num_rows != 0){ 
			//echo 'entered in if condition</br>';
			$prodaja2 = $kolicina_p->kolicina;
			$id52 = $kolicina_p->id_artikla;
			$ime52 = $kolicina_p -> ime;
		}
		else $prodaja2 = 0;
		//echo "kolicina u tabeli prodaja ID:$id52:$ime52 - $prodaja2</br>";
		
		/************************************************
		
		ATTENTION All articles that doesnt exist in stanje must have at lest one entry with kolicina = 0  
		in oreder for application to work properly. DO NOT delete entrys for kafa za poneti and similar
		
		*************************************************/
		
		//ako postoji stanje za danasnji datum
		if ($result4->num_rows != 0){ 
			if($id == 1) {$temp = $temp + $stanje->kolicina;	$zalihe = 0; $korekcija = $prodaja2 * 20;}			
			else if($id == 2) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodaja2 *15;}
			else if($id == 3) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodaja2 *5;}			
			else if($id == 4) $zalihe = $temp + $stanje->kolicina;
			else if($id == 5) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodaja2 *5;}
			else if($id == 6) $zalihe = $temp + $stanje->kolicina;
			else if($id == 7) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodaja2 *5;}
			else if($id == 8) $zalihe = $temp + $stanje->kolicina;
			else if($id == 9) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodaja2 *5;}
			else if($id == 10) $zalihe = $temp + $stanje->kolicina;
			else if($id == 11) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodaja2 *5;}
			else if($id == 12) $zalihe = $temp + $stanje->kolicina;
			else if($id == 13) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija =  $korekcija +$prodaja2 *5;}
			else if($id == 14) $zalihe = $temp + $stanje->kolicina;
			
			else $zalihe = $stanje->kolicina;
		}
	
		else $zalihe = 0;
		
		//echo "korekcija jeste: $korekcija</br></br></br></br>";

		//echo "pazar jeste za $id ".$pazar." + ".$prodata_kolicina * $cena;
		$pazar = $pazar + ($prodata_kolicina * $cena);
		//echo ' = '.$pazar."</br>";
		$vrednost_zaliha_na_kraju_dana = $vrednost_zaliha_na_kraju_dana + $zalihe * $cena;
		
		$artikli[$poredak] = array(	"ime" => $ime,
									"cena" => $cena, 
									"stanje_prethodni_dan" => number_format($stanje_prethodni_dan),
									"primljena_kolicina" => number_format($prijemljena_kolicina),
									"vrednost_primljene_robe" => number_format($prijemljena_kolicina * $cena),
									"stanje_na_pocetku_dana" => number_format($prijemljena_kolicina + $stanje_prethodni_dan),
									"prodata_kolicina" => 	number_format($prodata_kolicina),
									"vrednost_prodate_robe" => number_format($prodata_kolicina * $cena),
									"zalihe_na_kraju_dana" => number_format($zalihe),
									"vrednost_zaliha_na_kraju_dana" => number_format($zalihe * $cena));
	}
	//print_r($artikli);
	ksort($artikli);
	foreach ($artikli as $x => $value) 
	{
?>
				<tr>
                    <td style="width:150px; text-align:left; font-size:12px"><?php echo $artikli[$x]['ime'];?></td>
                    <td>kom</td>
                    <td><?php echo $artikli[$x]['cena'];?></td>
                    <td><?php echo $artikli[$x]['stanje_prethodni_dan'];?></td>
                    <td><?php echo $artikli[$x]['primljena_kolicina'];?></td>
                    <td><?php echo $artikli[$x]['vrednost_primljene_robe'];?></td>
                    <td><?php echo $artikli[$x]['stanje_na_pocetku_dana'];?></td>
                    <td><?php echo $artikli[$x]['prodata_kolicina'];?></td>
                    <td><?php echo $artikli[$x]['vrednost_prodate_robe'];?></td>
                    <td><?php echo $artikli[$x]['zalihe_na_kraju_dana'];?></td>
                    <td><?php echo $artikli[$x]['vrednost_zaliha_na_kraju_dana'];?></td> 
                </tr> 
<?php }?>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?php echo number_format($value_of_received_goods);?></td>
                    <td></td>
                    <td>UKUPNO:</td>
                    <td><?php echo number_format($pazar); $pazar = $pazar - $prezentacija;?></td>
                    <td></td>
                    <td></td> 
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>PREZENTACIJA:</td>
                    <td><?php echo  number_format($prezentacija);?></td>
                    <td></td>
                    <td></td> 
                </tr>  
				<tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Predat pazar:</td>
                    <td><?php echo number_format($pazar); ?></td>
                    <td></td>
                    <td><?php echo number_format($vrednost_zaliha_na_kraju_dana); ?></td> 
                </tr> 
            <?php $mysqli->close();
            ?>
            </table>
            <br />
Obračun sastavio:<br />
Katica Vasiljov<br />
<br />
-----------------------------------
  </form>
  <br />
  <br />
  <script type="text/javascript">
  x = <?php echo $vrednost_zaliha_na_kraju_dana_prethodni;?>;
  z = <?php echo $korekcija;?>;
  y = <?php echo $pazar + $prezentacija;?>;
  r = <?php echo $value_of_received_goods;?>;
  t = x + z -y + r;
  
  alert ("Stanje prethodni dan ("+x+") - pazar ("+y+") + korekcija ("+z+")+ primljena kolicina ("+r+")= "+t)</script>
</div>
<!--container-->
</body>
</html>
