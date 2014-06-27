<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
	die;
}
include "database_connect.php";
$report_month = $_POST['report_month'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Mesecna kontrola</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
<style type="text/css">
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
	table{border-collapse:collapse;}
	table, th, td{border: 1px solid black;}
	td{height:100%; text-align:center; padding:5px}
	.left{text-align:left}
	body{font:  14pt "Times New Roman"}	
</style>
</head>
<body>
<div class="container2">
<div class="naslov">Kontrola <div class="datum"><?php echo date("M-Y", strtotime($report_month));?></div></div>
  <table>
  	<thead>
    	<tr>
    		<td>datum</td>
            <td>ulaz</td>
            <td>izlaz</td>
            <td>razlika</td>
            <td>stanje</td>
       </tr>
    </thead> 
  <?php
  	
	$total_received = 0;
	$total_sold = 0;
	$total_correction = 0;
  

	for($i = 1; $i<32; $i++){
		
		$i = sprintf("%02s", $i);
		$datum = $report_month."-$i";//build date 
		//echo $datum;
	
		/*for current day in month calculata sale, addmision and diffrence for all articles, 
		while loop is going trough all articles*/
		$query = "	SELECT artikli.id,artikli.cena,artikli.ime 
					FROM artikli 
				  	WHERE artikli.status='aktivan' AND lokacija = 'bistro' ";
		//echo $query.'</br>';
		$result = $mysqli->query($query);
		$temp = 0;
		$pazar = 0;
		$korekcija = 0;
		$vrednost_zaliha_na_kraju_dana = 0;
		$primljena_kolicina = 0;
		$value_of_received_goods = 0;
		$value_of_sold_goods = 0;
		$vrednost_zaliha_na_kraju_dana_prethodni = 0;
		
		
		while ($obj=mysqli_fetch_object($result)){
		
			//get values for one article from artikli table
			$id = $obj->id;
			 
			//amount of article for current day
			$query4 = "	SELECT kolicina,cena 
						FROM stanje
				  		WHERE id_artikla = $obj->id AND datum <='$datum' 
						ORDER BY datum DESC 
						LIMIT 0,1 ";
			$result4 = $mysqli->query($query4);
			//echo $query4."<br/>";
			$stanje =mysqli_fetch_object($result4);
			if ($result4->num_rows != 0){
				//echo $stanje -> kolicina.'</br>';
				$cena = $stanje->cena;
			}
			
			//get number of received articles for current article
			$query1 = "	SELECT kolicina 
						FROM prijem
				  		WHERE id_artikla = $obj->id AND datum='$datum'";
			$result1 = $mysqli->query($query1);
			$kolicina = mysqli_fetch_object($result1);
			//echo $query1.'</br>';
			if ($result1->num_rows != 0){ 
				$prijemljena_kolicina = $kolicina->kolicina;
			}
			else $prijemljena_kolicina = 0;
			
			$value_of_received_goods = $value_of_received_goods + $prijemljena_kolicina * $cena;
		
			//get number of sold articles for current article
			$query2 = "SELECT kolicina FROM prodaja
					   WHERE id_artikla = $obj->id AND datum='$datum'";
			$result2 = $mysqli->query($query2);
			$prodaja =mysqli_fetch_object($result2);
			//echo $query2.'</br>';
			if ($result2->num_rows != 0){ 
				$prodata_kolicina = $prodaja->kolicina;
			}
			else $prodata_kolicina = 0;
			
			$value_of_sold_goods = $value_of_sold_goods + $prodata_kolicina * $cena;
			
			//amount of article for previous day
			$query3 = "	SELECT kolicina 
						FROM stanje
				  		WHERE id_artikla = $obj->id AND datum<'$datum' 
						ORDER BY datum DESC 
						LIMIT 0,1";
			$result3 = $mysqli->query($query3);
			$stanje_p = mysqli_fetch_object($result3);
			//echo $query3.'</br>';
			if ($result3->num_rows != 0){ 
				$stanje_prethodni_dan = $stanje_p->kolicina;
			}
			else $stanje_prethodni_dan = 0;
			
			$vrednost_zaliha_na_kraju_dana_prethodni = $vrednost_zaliha_na_kraju_dana_prethodni + 
														$stanje_prethodni_dan * $cena;		
			
			
						
			
			//ako postoji stanje za danasnji datum
			if ($result4->num_rows != 0){ 
				if($id == 1) {$temp = $temp + $stanje->kolicina;	$zalihe = 0; $korekcija = $prodata_kolicina * 20;}			
				else if($id == 2) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodata_kolicina *15;}
				else if($id == 3) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodata_kolicina *5;}			
				else if($id == 4) $zalihe = $temp + $stanje->kolicina;
				else if($id == 5) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodata_kolicina *5;}
				else if($id == 6) $zalihe = $temp + $stanje->kolicina;
				else if($id == 7) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodata_kolicina *5;}
				else if($id == 8) $zalihe = $temp + $stanje->kolicina;
				else if($id == 9) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodata_kolicina *5;}
				else if($id == 10) $zalihe = $temp + $stanje->kolicina;
				else if($id == 11) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija = $korekcija + $prodata_kolicina *5;}
				else if($id == 12) $zalihe = $temp + $stanje->kolicina;
				else if($id == 13) {$zalihe = 0; $temp = $stanje->kolicina; $korekcija =  $korekcija +$prodata_kolicina *5;}
				else if($id == 14) $zalihe = $temp + $stanje->kolicina;
				
				else $zalihe = $stanje->kolicina;
			}
		
			else $zalihe = 0;
			
			//echo "korekcija jeste: ".$korekcija;
	
			//echo "pazar jeste za $id ".$pazar." + ".$prodata_kolicina * $cena;
			$pazar = $pazar + ($prodata_kolicina * $cena);
			//echo ' = '.$pazar."</br>";
			$vrednost_zaliha_na_kraju_dana = $vrednost_zaliha_na_kraju_dana + $zalihe * $cena;
			
					
		}//end while loop
		
		//izlistaj sve artikle 
		?>
		<tr>
			<td style="width:150px; text-align:left;"><?php echo $datum;?></td>
			<td><?php echo number_format($value_of_received_goods);?></td>
			<td><?php echo number_format($value_of_sold_goods);?></td>
			<td><?php echo number_format($korekcija);?></td>
			<td><?php echo number_format($vrednost_zaliha_na_kraju_dana);?></td>
		</tr>             
		<?php
		$total_received = $total_received + $value_of_received_goods;
		$total_sold = $total_sold + $value_of_sold_goods;
		$total_correction = $total_correction + $korekcija;
		
	}//enf for loop
					
	?>
        <tr>
			<td style="width:150px; text-align:left; font-size:12px"></td>
			<td><?php echo number_format($total_received);?></td>
			<td><?php echo number_format($total_sold);?></td>
			<td><?php echo number_format($total_correction);?></td>
			<td></td>
		</tr>         
                
   </table>

</div>
<!--container-->
</body>
</html>
