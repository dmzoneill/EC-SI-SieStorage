<?php

include( "includes/intel_header.php" );

$stats = array();
$stats2 = array();
$totalusage = 0;
$totalallocated = 0;
$totalavailable = 0;

foreach( $common->disks as $disk )
{	
	$fserve = ( stristr( $disk->FileServer , "." ) ) ? substr( $disk->FileServer , 0 , strpos( $disk->FileServer , "." ) ) : $disk->FileServer;
	
	$index = $fserve . "/" . $disk->Disk;
	
	if( !isset( $stats[ $index ] ) )
	{
		$stats[ $index ] = array( 0 , 0 , 0 );
	}
	
	if( !isset( $stats2[ $fserve ] ) )
	{
		$stats2[ $fserve ] = array( 0 , 0 , 0 );
	}
	
	$stats[ $index ][ 0 ] += $disk->AllocatedGB;
	$stats[ $index ][ 1 ] += $disk->UsageGB;
	$stats[ $index ][ 2 ] += $disk->AvailableGB;
	$stats2[ $fserve ][ 0 ] += $disk->AllocatedGB;
	$stats2[ $fserve ][ 1 ] += $disk->UsageGB;
	$stats2[ $fserve ][ 2 ] += $disk->AvailableGB;
	$totalusage += $disk->UsageGB;
	$totalallocated += $disk->AllocatedGB;
	$totalavailable += $disk->AvailableGB;
}

/*

$areasoverview = array();

foreach( $common->areas as $area )
{		
	$areasoverview[ $area->Path ] = array( $area->SizeGB , $area->UsageGB , ( $area->SizeGB - $area->UsageGB ) , 0 , "biggestconsumer" );
	$areausers = array();
	
	foreach( $common->users as $user )
	{		
		if( $user->Path == $area->Path )
		{
			$areausers[ $user->User ] = $user->Usage;
		}
	}	
	
	asort( $areausers );
	
	end( $areausers );
	$key = key( $areausers );

	$areasoverview[ $area->Path ][ 4 ] = $key;
}

$t = Timer::I();
$t->End();

*/

function cmp( $a , $b ) 
{
    return ( $a[1] > $b[1] ) ? -1 : 1;
}

uasort( $stats2 , 'cmp' );
uasort( $stats , 'cmp' );

?>

<h2>Site Overview</h2>
<div id="chart_div1" style="width:100%; height:350px"></div>
<div id="chart_div2" style="width:100%; height:350px"></div>

<script type="text/javascript">   

	var chart1;
	var chart2;
	
	$(document).ready(function() {
				
		// Radialize the colors
		Highcharts.getOptions().colors = $.map(Highcharts.getOptions().colors, function(color) {
			return {
				radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
				stops: [
					[0, color],
					[1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
				]
			};
		});
		
		// Build the chart
		chart1 = new Highcharts.Chart({
			chart: {
				renderTo: 'chart_div1',
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false
			},
			title: {
				text: 'Filer Usage Chart'
			},
			tooltip: {
				pointFormat: '{series.name}: <b>{point.y} GB</b>',
				percentageDecimals: 1
			},
			plotOptions: {
				pie: {
					allowPointSelect: true,
					cursor: 'pointer',
					dataLabels: {
						enabled: true,
						color: '#000000',
						connectorColor: '#000000',
						formatter: function() {
							return '<b>'+ this.point.name +'</b>:' + parseFloat(this.y).toFixed(2) + ' GB';
						}
					},
					size: '70%'
				}
			},
                        width: '750px',
                        height: '350px',
			series: [{
				type: 'pie',
				name: 'Disks Usage',
				data: [					
					<?php				
						
						$biggest = 0;
						
						foreach( $stats2 as $key => $value )
						{
							if( $value[1] > $biggest )
							{
								$biggest = $value[1];
							}
						}
		
						$data = "";
					
						foreach( $stats2 as $key => $value )
						{
							if( $value[1] == $biggest )
							{
								$data .= "{ name: '$key allocated', y: " . $value[0] . ", sliced: true, selected: true },\n";
								$data .= "{ name: '$key used', y: " . $value[1] . ", sliced: true, selected: true },\n";
								$data .= "{ name: '$key available', y: " . $value[2] . ", sliced: true, selected: true },\n";
							}
							else
							{
								$data .= "[ '" . $key . " allocated' , " . $value[0] . " ],\n";
								$data .= "[ '" . $key . " used' , " . $value[1] . " ],\n";
								$data .= "[ '" . $key . " available' , " . $value[2] . " ],\n";
							}
						}
					
						$data = substr( $data , 0 , -2 );
						
						print $data;
					?>  
				]
			}]
		});
		
		
		// Build the chart
		chart2 = new Highcharts.Chart({
			chart: {
				renderTo: 'chart_div2',
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false
			},
			title: {
				text: 'Filer Disks Usage Chart'
			},
			tooltip: {
				pointFormat: '{series.name}: <b>{point.y} GB</b>',
				percentageDecimals: 1
			},
			plotOptions: {
				pie: {
					allowPointSelect: true,
					cursor: 'pointer',
					dataLabels: {
						enabled: true,
						color: '#000000',
						connectorColor: '#000000',
						formatter: function() {
							return '<b>'+ this.point.name +'</b>:' + parseFloat(this.y).toFixed(2) + ' GB';
						}
					},
					size: '70%'
				}
			},
			width: '750px',
			height: '350px',
			series: [{
				type: 'pie',
				name: 'Disks Usage',
				data: [					
					<?php				
						
						$biggest = 0;
						
						foreach( $stats as $key => $value )
						{
							if( $value[1] > $biggest )
							{
								$biggest = $value[1];
							}
						}
		
						$data = "";
					
						foreach( $stats as $key => $value )
						{
							if( $value[1] == $biggest )
							{
								$data .= "{ name: '$key', y: " . $value[1] . ", sliced: true, selected: true },\n";
							}
							else
							{
								$data .= "[ '" . $key . "' , " . $value[1] . " ],\n";
							}
						}
					
						$data = substr( $data , 0 , -2 );
						
						print $data;
					?>  
				]
			}]
		});
	
                $( '#areasOverviewTable' ).dataTable(
		{
                	"bPaginate": false
                });
	
	});

</script>

<?php

/*
print "<table cellpadding='2' id='areasOverviewTable' width='100%'>";
print "<thead>
		<tr>
			<th>Path</th>
			<th>Allocated</th>
			<th>Usage</th>
			<th>Free</th>
			<th>max(Consumer)</th>
		</tr>
	</thead>
	<tbody>";

foreach( $areasoverview as $areaname => $details )
{		
	print "<tr>";
	print "<td>" . $areaname . "</td>";
	print "<td>" . $details[ 0 ] . "</td>";
	print "<td>" . $details[ 1 ] . "</td>";
	print "<td>" . $details[ 2 ] . "</td>";
	print "<td><a href='" . $details[ 4 ] . "'>" . $details[ 4 ] . "</a></td>";
	print "</tr>";
}

print "</tbody></table>";
*/

include( "includes/intel_footer.php" );
