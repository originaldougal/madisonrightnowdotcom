<? // 15APR18

include_once('mrn.php');

if($_REQUEST['function'] == 'cityParking'){
	$data = curl('http://www.cityofmadison.com/parking-utility/data/ramp-availability.json');
	$data = utf8_encode($data);
	$data = json_decode($data, true);
	$totalUsed = $totalFree = 0;
	
	$capacity = array(247,613,516,620,1061,850);
	
	for ($i=0; $i<=sizeof($data)-1; $i++){
		$name = $data[$i]['name'];
		$vacantStalls = $data[$i]['vacant_stalls'];
		$vacancyRate = $vacantStalls/$capacity[$i];
		$vacancyRate = round($vacancyRate, 2);
		$utilizationRate = (1-$vacancyRate)*100;
		$vacancyRate = $vacancyRate * 100;
		
		$totalFree += $data[$i]['vacant_stalls'];
		$totalUsed += $capacity[$i]-$vacantStalls;
		
		echo $name; 
		echo '<div class="progress">';
		echo '<div class="progress-bar" role="progressbar" aria-valuenow="'.$utilizationRate.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$utilizationRate.'%">';
		
		echo $utilizationRate."%";
		
		echo '</div></div>';
		echo $vacantStalls.' open';
		echo '<hr/>';
	}
	echo 'There are '.$totalUsed.' parked vehicles and '.$totalFree.' open spaces<br/>';
	
}
if($_REQUEST['function'] == 'campusParking'){
	$campusData = curl('https://transportation.wisc.edu/parking/lotinfo_occupancy.aspx');
	$campusData = strip_tags($campusData);	
	$campusGarages = array('UNIVERSITY AVE RAMP', 'NANCY NICHOLAS HALL GARAGE', 'OBSERVATORY DR RAMP', 'HC WHITE GARAGE LOWR', 'HC WHITE GARAGE UPPR', 'GRAINGER HALL GARAGE', 'N PARK STREET RAMP', 'JOHNSON RAMP', 'FLUNO CENTER GARAGE', 'ENGINEERING DR RAMP', 'UNION SOUTH GARAGE', 'UNIV BAY DRIVE RAMP');
	$close = " Map";
	
	echo '<table class="table table-striped"><thead><tr><td><strong>Garage</strong></td><td><strong>Open visitor spaces</strong></td></tr></thead><tbody>';
	foreach ($campusGarages as $garage){
		echo '<tr><td>';
		echo ucwords(strtolower($garage));
		echo '</td><td>';
		echo $openAtThisGarge = scrapeBetween($campusData, $garage, $close);
		echo '</td></tr>';
		$totalOpen += $openAtThisGarge;
	}
	echo '<tr><td><strong>Total open spaces</strong></td><td><strong>'.$totalOpen.'</strong></td></tr>';
	echo '</tbody></table>';
	
}