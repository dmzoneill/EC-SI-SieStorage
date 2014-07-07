<?php

include( "includes/intel_header.php" );

function lessThan( $a , $b )
{
	return $a < $b;
}

function countLessThan( $a , $b )
{
	return count( $a ) < count( $b );
}


if( $common->project )
{	
	$cachefile = $common->docroot . "cache/project." . $common->project . "." . $common->site . ".cache";
	
	if( file_exists( $cachefile ) && $common->cache == false )
	{
		print file_get_contents( $cachefile );
	}
	else
	{
		$projectname = ucfirst( $common->project );
		$stats = array();
		$totalusage = 0;
		$totalallocated = 0;
		$totalfree = 0;
		$totalnumfiles = 0;
		$totalage90 = 0;
		$totalage180 = 0;
		$totalage360 = 0;
		
		$projectsareas = array();
		$projectsareastats = array();
		$userprojectarea = array();
		$projectsusers = array();
		$areaprojectpie = array();
	
		foreach( $common->areas as $area )
		{	
			if( $common->project == $area->Project )
			{
				$projectsareastats[ $area->Path ] = array( $area->UsageGB , $area->SizeGB , $area->numfiles , $area->age90 , $area->age180 , $area->age360 , $area->Owner );
				$totalusage += $area->UsageGB;
				$totalallocated += $area->SizeGB;
				$totalnumfiles += $area->numfiles;
				$totalage90 += $area->age90;
				$totalage180 += $area->age180;
				$totalage360 += $area->age360;
				$totalfree += ( $area->SizeGB - $area->UsageGB );
				$projectsareas[ $area->Path ] = array();
				$areaprojectpie[ $area->Path ] = $area->UsageGB;
			}
		}
		
		foreach( $projectsareas as $key => $parea )
		{	
			foreach( $common->users as $user )
			{	
				if( $user->Path == $key )
				{
					if( $user->User == "*" ) continue;
					
					if( !isset( $projectsusers[ $user->User ] ) )
					{
						$projectsusers[ $user->User ] = 0;
					}
					
					$userprojectarea[ $key ][ $user->User ] = array( $user->NumFiles , $user->Age90 , $user->Age180 , $user->Age360 );
					$projectsareas[ $key ][ $user->User ] = $user->Usage;
					$projectsusers[ $user->User ] += $user->Usage;
				}
			}
		}
		
		uasort( $projectsusers , "lessThan" );		
		uasort( $projectsareas , "countLessThan" );		
		uasort( $areaprojectpie , "lessThan" );
		
		$cache = "<h2>$projectname</h2>";
		
		$cache .= "<div id=\"chart_project_pie\" style=\"width:90%; height:250px;\" align='right'></div><br/><br/>";
		$cache .= $common->createpiechart( "chart_project_pie" , "chart_project_pie" , "Project Areas" , $areaprojectpie , "GB" );
		
		$cache .= "<h3>$projectname Project Areas</h3>\n";
		$cache .= "<table width='100%'>";
		$cache .= "<tr><th>Path</th><th>Owner</th><th>Usage GB</th><th>Size GB</th><th>Free GB</th><th>Age 180</th><th>Age 360</th></tr>";
		$cache .= "<tr><td colspan='9'><hr/></td></tr>";
			
		$counter = 1;
		foreach( $projectsareastats as $proj => $dalues )
		{
			$cache .= "<tr>\n";	
			$cache .= "<td><a href='#anchor$counter'>$proj</a></td>";
			$cache .= "<td>" . $dalues[6] . "</td>\n";
			$cache .= "<td>" . $dalues[0] . "</td>\n";
			$cache .= "<td>" . $dalues[1] . "</td>\n";
			$cache .= "<td>" . ( $dalues[1] -  $dalues[0] ) . "</td>\n";
			$cache .= "<td>" . $dalues[4] . "</td>\n";
			$cache .= "<td>" . $dalues[5] . "</td>\n";
			$cache .= "</tr>\n";		
			$counter++;
		}
		$cache .= "<tr><td colspan='9'><hr/></td></tr>";
		$cache .= "<tr><th></th><th></th><th>$totalusage</th><th>$totalallocated</th><th>$totalfree</th><th>$totalage180</th><th>$totalage360</th></tr>";
		$cache .= "</table>";

		
		$cache .= "<br/><h3>$projectname Project Users</h3>\n";	
		$cache .= "<table width='100%'>\n";
		
		$break = 0;
				
		foreach( $projectsusers as $user => $space )
		{
			if( $break % 11 == 0 ) $cache .= "<tr>\n";	
			$break++;
			$cache .= "<td><a href='users.php?requested_user=$user'><img src='" . $common->getuserimage( trim( $user ) , $ldap ) . "' width='50' /></br>$user</a></td>\n";
			if( $break % 11 == 0 ) $cache .= "</tr>\n";	
		}
		
		$cache .= "</table>";
		
		$cache .= "<h2>$projectname Consumption By User</h2>";		
		$cache .= "<div id=\"chart_pie_user\" style=\"width:90%; height:400px;\" align='center'></div><br/><br/>";
		$cache .= $common->createpiechart( "chart_pie_user" , "chart_pie_user" , "Users" , $projectsusers , "MB" );
		
		$counter = 1;
			
		foreach( $projectsareas as $proj => $dalues )
		{
			$cache .= "<a name='anchor$counter'/><h3>$projectname $proj</h3>";			
			$cache .= "<div id=\"chart_pie$counter\" style=\"width:90%; height:400px;\" align='center'></div><br/><br/>";
			$cache .= $common->createpiechart( "chart_pie$counter" , "chart_pie$counter" , "Project Area Usage" , $dalues , "MB" );
			$counter++;
			
			$cache .= "<table width='100%'>";
			$cache .= "<tr><th>User</th><th>Usage GB</th><th>Num Files</th><th>Age 90</th><th>Age 180</th><th>Age 360</th></tr>";
			$cache .= "<tr><td colspan='9'><hr/></td></tr>";
				
			foreach( $userprojectarea[ $proj ] as $user => $dalues )
			{
				$cache .= "<tr>\n";	
				$cache .= "<td><a href='users.php?requested_user=$user'>$user</a></td>";
				$cache .= "<td>" . $projectsareas[ $proj ][ $user ] . "</td>\n";
				$cache .= "<td>" . $dalues[0] . "</td>\n";
				$cache .= "<td>" . $dalues[1] . "</td>\n";
				$cache .= "<td>" . $dalues[2] . "</td>\n";
				$cache .= "<td>" . $dalues[3] . "</td>\n";
				$cache .= "</tr>\n";	
			}
			$cache .= "</table>";
			
		}
				
		file_put_contents( $cachefile , $cache );
		print $cache;
	}
}
else
{
	$stats = array();
	$totalusage = 0;
	$totalallocated = 0;

	foreach( $common->projects as $proj )
	{	
		foreach( $common->areas as $area )
		{
			if( $proj->Project == $area->Project )
			{
				if( !isset( $stats[ $proj->Project ] ) )
				{
					$stats[ $proj->Project ] = array( 0 , 0 ) ;
				}
			
				$stats[$proj->Project][0] += $area->SizeGB;
				$stats[$proj->Project][1] += $area->UsageGB;
				$totalusage += $area->UsageGB;
				$totalallocated += $area->SizeGB;
			}
		}	
	}

	?>

	<div id="chart_div2" style="width:90%; height:500" align='center'></div>
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
					text: 'Project Allocation Usage Chart'
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

	<?php

	print "<table cellpadding='2' width='100%' id='projtables'><thead>";
	print "<tr><th>Project</th><th>Allocated GB</th><th>Usage GB</th><th>Usage %</th><th>Total Usage %</th></tr>";
	print "</thead><tbody>";

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
			$( '#projtables' ).dataTable(
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
