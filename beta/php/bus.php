<? //15APR18 this gets json from madison transit and reformats it into valid geoJSON

include_once('mrn.php');
if($_REQUEST['function'] == 'map'){
?>
{
	"type": "FeatureCollection",
	"features": [
<?
	$positions = curl('http://transitdata.cityofmadison.com/Vehicle/VehiclePositions.json');
	$positions = json_decode($positions, true);

	foreach($positions['entity'] as $k=>$v){
		?>
		{ "type": "Feature",
		"geometry": {
        "type": "Point",
        "coordinates": [<?echo $v['vehicle']['position']['longitude'].', '.$v['vehicle']['position']['latitude'];?>]
      },
      "properties": {
        "id": "<?=$v['id']?>"
      }
    }
	<? 
	if( $k !== count($positions['entity']) -1 ){
		echo ",";
	}
	else
	{
		echo "";
	}
	}//ends foreach
}// ends function
?>
   ]
}