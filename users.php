<?php

include( "includes/intel_header.php" );

function sortUsage( $a , $b )
{
	return $a->Usage < $b->Usage;
}

if( $common->requested_user )
{
	$directories = array();
	$title = "";
	
	/*
	
	for( $y = 0; $y < count( $common->users ); $y++ )
	{
		if( $common->users[$y]->User == $common->requested_user )
		{
			$directories[] = $common->users[$y];
		}
	}
	
	*/

	for( $y = 0; $y < count( $common->areas ); $y++ )
	{
		if( stristr( $common->areas[$y]->Users , $common->requested_user->samaccountname ) )
		{
			$directories[] = $common->areas[$y];
		}
	}
	
	$title_user = ucfirst( $common->requested_user ) . "'s";
	
	print "<table width='100%'>";
	print "<tr>";	
	print "<td valign='bottom' width='800'><h2>" . $title_user . " Storage Usage</h2><br/>";
	print "</td>";
	print "<td align='right' width='120'><center><img width='100' src='" . $common->getuserimage( $common->requested_user , $ldap ) . "' /><br/>" . $common->requested_user->cn . "</td>";
	print "</tr>";
	print "<tr>";	
	print "<td valign='bottom' colspan='2'>";
	print "<div id=\"chart_user_pie\" style=\"width:100%; height:260px;\"></div><br/>";
	print "</td>";
	print "</tr>";
	print "</table><br/><br/>";
	
	print "<table cellpadding='5' id='userOverviewTable' width='100%'>";
	print "<thead>
		<tr>
			<th>#</th>
			<th>Path</th>
			<th>MaxSize</th>
			<th>Usage</th>
			<th>NumFiles</th>
			<th>Age0</th>
			<th>Age90</th>
			<th>Age180</th>
			<th>Age360</th>
		</tr>
	</thead>
	<tbody>";
	
	$stats = array();
	$total_usage = 0;
	$total_files = 0;
	$count = 1;
	
	foreach( $directories as $directory )
	{
		if( !isset( $stats[ $directory->Path ] ) )
		{
			$stats[ $directory->Path ] = 0;
		}
		
		$stats[ $directory->Path ] += $directory->Usage;
		
		print "<tr>";
		print "<td width='50'>" . $count . "</td>";
		print "<td>" . $directory->Path . "</td>";
		print "<td>" . $directory->MaxSize . "</td>";
		print "<td>" . $directory->Usage . "</td>";
		print "<td>" . $directory->NumFiles . "</td>";
		print "<td>" . $directory->Age0 . "</td>";
		print "<td>" . $directory->Age90 . "</td>";
		print "<td>" . $directory->Age180 . "</td>";
		print "<td>" . $directory->Age360 . "</td>";
		print "</tr>";
	
		$total_usage += $directory->Usage;
		$total_files += $directory->NumFiles;
		$count++;
	}

	print "</tbody></table>";
	
	?>
	<script type="text/javascript" charset="utf-8">
		$( document ).ready( function() 
		{
			$( '#userOverviewTable' ).dataTable(
				{
					"bPaginate": false
				}
			);
		});
	</script>
	<?php
	
	print $common->createpiechart( "chart_user_pie" , "chart_user_pie" , ucfirst( $common->requested_user ) . "\'s Areas" , $stats , "MB" );
}
else
{
	$outfile = "";
	if( $common->home ) 
	{
		$outfile = "home";
	}
	else if( $common->publichome ) 
	{
		$outfile = "publichome";
	}
	else 
	{
		$outfile = "project";
	}
	
	if( $outfile == "" )
	{
		print "error determining cache file" ;
		include( "includes/intel_footer.php" );
		exit;
	}
	
	$cachefile = $common->docroot . "cache/" . $outfile . "." . $common->site . ".cache";
	
	if( file_exists( $cachefile ) && $common->cache == false )
	{
		print file_get_contents( $cachefile );
	}
	else
	{
		$cache = "";
		$directories = array();
		$title = "";
		$showall = "";
		
		for( $y = 0; $y < count( $common->users ); $y++ )
		{	
			if( strpos( $common->users[$y]->Path , $common->sitehomedir ) !== false && $common->home )
			{
				$directories[] = $common->users[$y];
				$title = "Home";
				$showall = "users.php?home=true&showall=true";
			}
			else if( strpos( $common->users[$y]->Path , $common->sitelabhomedir ) !== false && $common->publichome )
			{
				$directories[] = $common->users[$y];
				$title = "Public Home";
				$showall = "users.php?publichome=true&showall=true";
			}
			else if( $common->project )
			{
				$directories[] = $common->users[$y];
				$title = "Project";
				$showall = "users.php?project=true&showall=true";
			}
		}
		
		usort( $directories , 'sortUsage' );

		$cache .= "<table width='100%'><tr><td><h2>$title Directories</h2></td><td align='right'><a href='" . $showall . "'>Show 0MB Usage Users</a></td></tr>";
		$cache .= "<tr><td colspan='2'><div id='chart_div1' style='width:100%; height:400'/><br/></td></tr></table>";
		
		$cache .= "<table cellpadding='2' id='usersOverviewTable' width='100%'>";
		$cache .= "<thead>
			<tr>
				<th>#</th>
				<th>User</th>
				<th>Path</th>
				<th>Usage MB</th>
				<th>Num Files</th>
			</tr>
		</thead>
		<tbody>";

		$biggest = 0;
		$total_user = 0;
		$total_path = 0;
		$total_usage = 0;
		$total_maxsize =  0;
		$total_numfiles = 0;
		$count = 1;	   
		$data = "";
		$tsize = 0;
		$combined = array();

		foreach( $directories as $directory )
		{
			$total_user += $directory->User;
			$total_path += $directory->Path;
			$total_usage += $directory->Usage;
			$total_numfiles += $directory->NumFiles;
			
			if( $directory->Usage > $biggest )
			{
				$biggest = $directory->Usage;
			}
			
			if( $common->show_all )
			{		
				if( !isset( $combined[ $directory->User ] ) )
				{
					$combined[ $directory->User ] = array( 0 , 0 );
				}

				$combined[ $directory->User ][ 0 ] += $directory->Usage;	
				$combined[ $directory->User ][ 1 ] += $directory->NumFiles;	
			}
		}   

		foreach( $directories as $directory )
		{				
			if( $directory->Usage > 0 )
			{	
				$data = "[" . $directory->Usage . "," . $directory->NumFiles . "],\n" . $data;
				
				$cache .= "<tr>";
				$cache .= "<td>$count</td>";
				$cache .= "<td><a href='users.php?requested_user=" . $directory->User . "'>" . $directory->User . "</a></td>";
				$cache .= "<td><a href='users.php?path=" . $directory->Path . "'>" . $directory->Path . "</a></td>";
				$cache .= "<td>" . $directory->Usage . "</td>";
				$cache .= "<td>" . $directory->NumFiles . "</td>";
				$cache .= "</tr>";
				
				$count++;
			}
		}

		if( isset( $common->project ) )
		{
			$data = "";
			foreach( $combined as $key => $value )
			{
				if( $value[0] > 0 )
				{
					$data = "[" . $value[0] . "," . $value[1] . "],\n" . $data;
				}
			}
		}
			
		$data = substr( $data , 0 , -2 );

		$cache .= "</tbody>";
		$cache .= "</table>";

		$cache .= "
		<script type=\"text/javascript\">   

			var chart;
				
			\$(document).ready(function() {
			
				chart = new Highcharts.Chart({
					chart: 
					{
						renderTo: 'chart_div1'
					},
					xAxis: 
					{
						title: {
							enabled: true,
							text: 'Usage MB'
						},
						min: -1,
						max: $biggest
					},
					yAxis: 
					{
						title: {
							enabled: true,
							text: 'Num Files'
						},
						min: 0
					},
					tooltip: 
					{
						formatter: function() 
						{
							return ''+
							this.x +' MB, '+ this.y +' Files';
						}
					},
					title: 
					{
						text: 'Showing Usage > 0 MB'
					},
					series: [
					{
						type: 'scatter',
						name: 'Observations',
						data: [ $data ],
						marker: {
							radius: 4
						}
					}]
				});

				\$( '#usersOverviewTable' ).dataTable(
					{
						\"bPaginate\": false
					}
				);
			});
			
		</script>";
		
		file_put_contents( $cachefile , $cache );
		print $cache;
	
	}
}

include( "includes/intel_footer.php" );
