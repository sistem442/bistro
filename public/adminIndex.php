<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
 <a href='login.php'>Prijava</a>
<br />";
}
else{
	$_SESSION['poruka_prodaja'] ='';//ovo se koristi na stranicama unos-prodaje i korekcija_prodaje
	$_SESSION['datum_prodaje']='';
	echo '<span class="blink_me">'.$_SESSION['poruka'].'</span>';
	$_SESSION['poruka'] = '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Glavni meni</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<style>
	input {width:200px}
	/*.ui-datepicker-calendar {
    display: none;
    }*/
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<script>

	//removes border color after focus when false date is entered
	$(document).ready(function(){
		$( "#date_of_sale" ).focus(function() {
		  $( this ).css('border-color','');
		});
		$( "#date_of_admission" ).focus(function() {
		  $( this ).css('border-color','');
		});
	});
	  
	$(function() {    
		var currentDate = new Date();  
		$( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" }); 
		$( ".datepicker" ).datepicker( "setDate",currentDate );		
	 });

	
	
	//validates date
	function isValidDate(dateString)
	{
		// First check for the pattern
		var regex_date = /^\d{4}\-\d{1,2}\-\d{1,2}$/;
	
		if(!regex_date.test(dateString))
		{
			return false;
		}
	
		// Parse the date parts to integers
		var parts   = dateString.split("-");
		var day     = parseInt(parts[2], 10);
		var month   = parseInt(parts[1], 10);
		var year    = parseInt(parts[0], 10);
	
		// Check the ranges of month and year
		if(year < 1000 || year > 3000 || month == 0 || month > 12)
		{
			return false;
		}
	
		var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];
	
		// Adjust for leap years
		if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
		{
			monthLength[1] = 29;
		}
	
		// Check the range of the day
		return day > 0 && day <= monthLength[month - 1];
	}
	
	function validate(){
		var date = $('#date_of_sale').val();
		if(isValidDate(date)){
			if(confirm('Uneli ste datum: '+date+'. Da li je datum ispravan?' )){
				$("#frmSale").submit();
			}
			else{
				$('#date_of_sale').css('border-color','red');
			}
		}
		else{
			alert('Nije unet validan datum!');
			$('#date_of_sale').css('border-color','red');
		}
	} 

	function validate2(){
		var date = $('#date_of_admission').val();
		if(isValidDate(date)){
			if(confirm('Uneli ste datum: '+date+'. Da li je datum ispravan?' )){
				$("#frm_admission").submit();
			}
			else{
				$('#date_of_admission').css('border-color','red');
			}
		}
		else{
			alert('Nije unet validan datum!');
			$('#date_of_admission').css('border-color','red');
		}
	} 
</script>
</head>
<body class="container2">
	<div class="page-holder">
        <div class="container2">
        	<div style="background-color: #FF9; padding: 10px">
                <span style="font-size: 36px; color: #333">PRODAJA</span>
                <form action="unos_prodaje.php" method="post" id="frmSale">
                    <input type="text" name="datum_prodaje" class="datepicker" id="date_of_sale" value="<?php echo date('Y-m-d')?>" />
                    <input type="button" onclick="validate()" name="submit3" value="Unos i izmena prodaje"/>
                </form>
            </div>
            <div style="background-color: #09C; padding: 10px">
                <span style="font-size: 36px; color: #333">PRIJEM</span>
                <form action="unos_prijema.php" method="post" id="frm_admission">
                    <input type="text" class="datepicker" name="datum_prijema" id="date_of_admission" value="<?php echo date('Y-m-d')?>"/>
                    <input type="button" onclick="validate2()" name="submit3" value="Unos i izmena prijema"/>
           	  </form>
          </div>
        	<div style="background-color: #FF9; padding: 10px">
              <span style="font-size:36px; color:#333">IZVEŠTAJI</span></br>
                <form action="izvestaj.php" method="post" target="_blank">
                    <input type="text" class="datepicker" name="datum_izvestaja" value="<?php echo date('Y-m-d')?>"/>
                    <input type="submit" name="submit3" value="Izveštaj za bistro"/>
              </form><br />
                <form action="izvestaj_klub.php" method="post" target="_blank">
                    <input type="text" class="datepicker" name="datum_izvestaja" value="<?php echo date('Y-m-d')?>"/>
                    <input type="submit" name="submit3" value="Izveštaj za klub"/>
                </form>
          </div>
          <div style="background-color: #09C; padding: 10px">
            <form action="artikli.php">
			    <input type="submit" value="Artikli" > <input type="button" onclick="window.open('log.php')" value="Log">
			</form>
          </div>
          <span style="font-size:36px; color:#333">KONTROLA</span><div style="float:right">unesite datum formatu GGGG-MM (2014-01 za januar 2014.)</div></br>
          <div style="background-color: #FF9; padding: 10px">
              <form action="razlika.php" method="post">			    
                    <div style="width:800px">Razlika u ceni za mesec:</div>
                    <input name="diffrence_date" id="startDate" type="text" />
                    <input type="submit" value="Prikaži razliku" >
              </form>
          </div>
          <div style="background-color: #FF9; padding: 10px">
              <form action="control_month.php" method="post">			    
                    <div style="width:800px">Kontrola za mesec:</div>
                    <input name="report_month" id="startDate" type="text" />
                    <input type="submit" value="Prikaži kontrolu" >
              </form>
          </div>
          <div style="background-color: #FF9; padding: 10px">
              <form action="control_month_club.php" method="post">			    
                    <div style="width:800px">Kontrola za mesec (donji hol):</div>
                    <input name="report_month" id="startDate" type="text" />
                    <input type="submit" value="Prikaži kontrolu" >
              </form>
          </div>
		</div><!--container-->
	</div><!-- page holder-->
</body>
</html>
<?php 
}
?>