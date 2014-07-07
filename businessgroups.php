<?php

include( "includes/intel_header.php" );

function sortLess( $a , $b )
{
	return $a < $b;
}

function sortLessArr( $a , $b )
{
	return $a[0] < $b[0];
}

if( $common->bu )
{
	$bu = ucfirst( $common->bu );
	$stats = array();
	$totalusage = 0;
	$totalallocated = 0;
	$totalfree = 0;
	$totalnumfiles = 0;
	$totalage90 = 0;
	$totalage180 = 0;
	$totalage360 = 0;
	
	$businessprojects = array();
	$businessprojectstats = array();
	
	foreach( $common->areas as $area )
	{	
		if( $common->bu == $area->BusinessGroup )
		{
			if( !isset( $businessprojectstats[ $area->Project ] ) )
			{
				$businessprojectstats[ $area->Project ] = array( 0 , 0 , 0 , 0 , 0 , 0 );
			}
			
			if( !isset( $businessprojects[ $area->Project ] ) )
			{
				$businessprojects[ $area->Project ] = 0;
			}
			
			$businessprojects[ $area->Project ] += $area->UsageGB;
			
			$businessprojectstats[ $area->Project ][0] += $area->UsageGB;
			$businessprojectstats[ $area->Project ][1] += $area->SizeGB;
			$businessprojectstats[ $area->Project ][2] += $area->numfiles;
			$businessprojectstats[ $area->Project ][3] += $area->age90;
			$businessprojectstats[ $area->Project ][4] += $area->age180;
			$businessprojectstats[ $area->Project ][5] += $area->age360;
			
			$totalusage += $area->UsageGB;
			$totalallocated += $area->SizeGB;
			$totalnumfiles += $area->numfiles;
			$totalage90 += $area->age90;
			$totalage180 += $area->age180;
			$totalage360 += $area->age360;
			$totalfree += ( $area->SizeGB - $area->UsageGB );
		}
	}
	
	
	uasort( $businessprojects , "sortLess" );
	
	uasort( $businessprojectstats , "sortLessArr" );
	
	print "<h2>$bu</h2>";
	
	print "<div id=\"chart_bu_pie\" style=\"width:90%; height:250px;\"></div><br/><br/>";
	print $common->createpiechart( "chart_bu_pie" , "chart_bu_pie" , "Business Project Areas" , $businessprojects , "GB" );
	
	print "<h3>$bu Project Areas</h3>\n";
	print "<table width='100%'>";
	print "<tr><th>Path</th><th>Usage GB</th><th>Size GB</th><th>Free GB</th><th>Num Files</th><th>Age 180</th><th>Age 360</th></tr>";
	print "<tr><td colspan='9'><hr/></td></tr>";
		
	$counter = 1;
	foreach( $businessprojectstats as $proj => $dalues )
	{
		print "<tr>\n";	
		print "<td><a href='#anchor$counter'>$proj</a></td>";
		print "<td>" . $dalues[0] . "</td>\n";
		print "<td>" . $dalues[1] . "</td>\n";
		print "<td>" . ( $dalues[1] - $dalues[0] ) . "</td>\n";
		print "<td>" . $dalues[2] . "</td>\n";
		print "<td>" . $dalues[4] . "</td>\n";
		print "<td>" . $dalues[5] . "</td>\n";
		print "</tr>\n";		
		$counter++;
	}
	print "<tr><td colspan='9'><hr/></td></tr>";
	print "<tr><th></th><th>$totalusage</th><th>$totalallocated</th><th>$totalfree</th><th>$totalage180</th><th>$totalage360</th></tr>";
	print "</table>";

}
else
{
	$stats = array();
	$totalusage = 0;
	$totalallocated = 0;

	foreach( $common->businessgroups as $bg )
	{			
		foreach( $common->areas as $area )
		{			
			if( !isset( $stats[$bg->BusinessGroup] ) )
			{
				$stats[ $bg->BusinessGroup ] = array( 0 , 0 );
			}
			
			if( $bg->BusinessGroup == $area->BusinessGroup )
			{
				$stats[ $bg->BusinessGroup ][ 0 ] += $area->SizeGB;
				$stats[ $bg->BusinessGroup ][ 1 ] += $area->UsageGB;
				$totalusage += $area->UsageGB;
				$totalallocated += $area->SizeGB;
			}
		}	
	}

	?>

	<div id="chart_div1" style="width:90%; height:400" align='center'></div>
	<br/>
	
	<script type="text/javascript">   

		var chart;
		var chart2;
		
		$(document).ready(function() {
		
			chart = new Highcharts.Chart({
				chart: {
					renderTo: 'chart_div1',
					type: 'areaspline'
				},
				title: {
					text: 'Business Groups Allocation Usage Chart'
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					x: 550,
					y: 50,
					floating: true,
					borderWidth: 1,
					backgroundColor: '#FFFFFF'
				},
				xAxis: {
					categories: [
						
						<?php
			
							$data = "";
						
							foreach( $stats as $key => $value )
							{
								$data .= "'" . $key . "',\n";
							}
						
							$data = substr( $data , 0 , -2 );
							
							print $data;
						?>   
						
					],
					plotBands: [{ // visualize the weekend
						from: 4.5,
						to: 6.5,
						color: 'rgba(68, 170, 213, .2)'
					}]
				},
				yAxis: {
					title: {
						text: 'GB'
					}
				},
				tooltip: {
					formatter: function() {
						return ''+
						this.x +': '+ this.y +' GB';
					}
				},
				credits: {
					enabled: false
				},
				plotOptions: {
					areaspline: {
						fillOpacity: 0.5
					}
				},
				series: [								
				
					{
						name: 'Allocated GB',
						data: [
					
						<?php
				
							$data = "";
						
							foreach( $stats as $key => $value )
							{
								$data .= $value[0] . ",\n";
							}
						
							$data = substr( $data , 0 , -2 );
							
							print $data;
						?>   
						
						]
					}, 
					{
						name: 'Usage GB',
						data: [
						
						<?php
				
							$data = "";
						
							foreach( $stats as $key => $value )
							{
								$data .= $value[1] . ",\n";
							}
						
							$data = substr( $data , 0 , -2 );
							
							print $data;
						?> 
						
						]
					}
				]
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
			
			// Build the chart
			chart2 = new Highcharts.Chart({
				chart: {
					renderTo: 'chart_div2',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'Business Groups Allocation Usage Chart'
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
			
		});

	</script>

	<br/>
	<?php

	print "<table cellpadding='2' width='90%' id='bustables' align='center'><thead>";
	print "<tr><th>Business Group</th><th>Allocated GB</th><th>Usage GB</th><th>Free GB</th><th>Usage %</th><th>Total Usage %</th></tr></thead>";
	print "<tbody>";

	foreach( $stats as $statkey => $statvalue )
	{	
		print "<tr>
			<td style='pading-right:15px'>$statkey</td>
			<td style='pading-right:15px'>" . $statvalue[0] . "</td>
			<td style='pading-right:15px'>" . $statvalue[1] . "</td>
			<td style='pading-right:15px'>" . ( $statvalue[0] - $statvalue[1] ) . "</td>
			<td style='pading-right:15px'>" . ( round( ( $statvalue[1] / $statvalue[0] ) * 100 ) ) . "</td>
			<td style='pading-right:15px'>" . ( round( ( $statvalue[1] / $totalusage ) * 100 ) ) . "</td>
		</tr>";	
	}

	print "</tbody></table>";

	?>
	<script type="text/javascript" charset="utf-8">
		$( document ).ready( function() 
		{
			$( '#bustables' ).dataTable(
				{
					"bPaginate": false
				}
			);
		});
	</script>

	<br/>
	<br/>

	<div id="chart_div2" style="width:90%; height:600"  align='center'></div>
	
	<?php

}

include( "includes/intel_footer.php" );
