<?php

include( "includes/intel_header.php" );

if( $common->owner )
{



}
else
{
	$stats = array();
	$totalusage = 0;
	$totalallocated = 0;

	foreach( $common->owners as $owner )
	{	
		foreach( $common->areas as $area )
		{
			if( !isset( $stats[ $owner->Owner ] ) )
			{
				$stats[ $owner->Owner ] = array( 0 , 0 ) ;
			}
			
			if( $owner->Owner == $area->Owner )
			{
				$stats[$owner->Owner][0] += $area->SizeGB;
				$stats[$owner->Owner][1] += $area->UsageGB;
				$totalusage += $area->UsageGB;
				$totalallocated += $area->SizeGB;
			}
		}	
	}

	?>

	<div id="chart_div2" style="width:100%; height:600"></div>
	<br/>
	<br/>
	<script type="text/javascript">   

		var chart2;
		
		$(document).ready(function() {
					
		
			// Build the chart
			chart2 = new Highcharts.Chart({		

				chart: {
					renderTo: 'chart_div2',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'Owners Allocation Usage Chart'
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
								return '<b>'+ this.point.name +'</b>: ' + parseFloat(this.percentage).toFixed(2) + '%';
							}
						}
					}
				},
				series: [{
					type: 'pie',
					name: 'Usage Share',
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
		});

	</script>

	<br/>
	<br/>

	<?php

	print "<table cellpadding='2' width='100%' id='ownerstable'><thead>";
	print "<tr><th>Owner</th><th>Allocated GB</th><th>Usage GB</th><th>Usage %</th><th>Total Usage %</th></tr></thead><tbody>";

	foreach( $stats as $statkey => $statvalue )
	{	
		print "<tr>
			<td style='pading-right:15px'>$statkey</td>
			<td style='pading-right:15px'>" . $statvalue[0] . "</td>
			<td style='pading-right:15px'>" . $statvalue[1] . "</td>
			<td style='pading-right:15px'>" . ( round( ( $statvalue[1] / $statvalue[0] ) * 100 ) ) . "</td>
			<td style='pading-right:15px'>" . ( round( ( $statvalue[1] / $totalusage ) * 100 ) ) . "</td>
		</tr>";	
	}

	print "</tbody></table>";

	?>
	<script type="text/javascript" charset="utf-8">
		$( document ).ready( function() 
		{
			$( '#ownerstable' ).dataTable(
				{
					"bPaginate": false
				}
			);
		});
	</script>

	<br/>
	<br/>
	<?php

}

include( "includes/intel_footer.php" );
