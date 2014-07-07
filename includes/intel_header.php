<?php

session_start();

if( isset( $_GET['viewall'] ) )
{
	if( $_GET['viewall'] == "no" )
	{
		unset( $_SESSION['viewall'] );
	}
	else
	{
		$_SESSION['viewall'] = true;
	}
}

date_default_timezone_set( 'Europe/Dublin' );

header( "Pragma: nocache" );
header( "cache-Control: no-cache; must-revalidate" );
header( "Expires: Mon, 26 Jul 1993 00:00:00 GMT" );

include( "timer.class.php" );
include( "csvrow.class.php" );
include( "ldapuser.class.php" );
include( "ldapgroup.class.php" );
include( "ldap.class.php" );
include( "common.class.php" );

$t = Timer::I();


$ldap = new Ldap();
$common = new Common( $ldap );

if( $common->debug_user )
{
	$user = $ldap->getldapuser( $common->debug_user );
	$user->dump();	
	exit();
}

$viewall = ( isset( $_SESSION['viewall'] ) ) ? true : false;


//if( !ob_start( "ob_gzhandler" ) ) ob_start();

?>
<!doctype html>
<html>
<head>
<link rel='stylesheet' href='css/intel.css' type='text/css' media='screen'>
<link rel="stylesheet" href="css/smoothness/jquery-ui-1.9.1.custom.min.css" />
<script src="js/jquery-1.8.2.js" type="text/javascript"></script>
<script src="js/jrotate.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.9.1.custom.min.js" type="text/javascript"></script>
<script src="js/highcharts.js" type="text/javascript"></script> 
<style type="text/css" title="currentStyle">
	@import "css/demo_page.css";
	@import "css/demo_table.css";
</style>
<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
<script src="js/intel.js" type="text/javascript"></script>
</head>
<body>

<table width='1500'>
	<tr>	
		<td width='1500' colspan='3'>
			<table>
				<tr>
				<td width='242' height='150'>
                    			<div id='intelloadingCon' style='width:162px;height:150px;margin-left:70px;margin-right:0px'>
			                <a href='index.php'>
                        		<img id='intelloading' src='images/intel-small.png' width='100%' height='100%' border='0'/>
		                        </a>
                			</div>
				</td>
				<td width='820'>
					<font style='padding-left:40px;padding-right:40px;font-size:18pt'><?php print $common->sitename; ?> Start Visualizer</font><br/>
			                <span style='padding-left:60px;'>
			                <font style='font-size:12pt'>ST</font><font style='font-size:10pt'>orage </font> 
                        		<font style='font-size:12pt'>A</font><font style='font-size:10pt'>llocation, </font>
		                        <font style='font-size:12pt'>R</font><font style='font-size:10pt'>eplication and </font>
                		        <font style='font-size:12pt'>T</font><font style='font-size:10pt'>racking tool</font>
		                        </span><br><br>    
					<font style='padding-left:70px;padding-right:40px;font-size:10pt'><?php print "Last polled <b>" . $common->lastupdated[0]['lastrun']; ?></b></font>
				</td>
				<td width='*'>
					<center>
					<img width='100' height='100' src='<?php print $common->getuserimage( $common->current_user , $ldap ); ?>'><br>
					<?php print $common->current_user->cn; ?>
					</center>
				</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width='200' style='text-align:right;vertical-align:top;padding-right:20px'>
			<h2 class='hr' align='right' style='border-bottom:1px dashed #99f;margin-left:10px'>Campus</h2>
					<select style='width:120px' onchange="document.location.href=this.value">
					<?php

							foreach( $common->sitenamesarr as $sitecode => $sitename  )
							{
								$url = "http://" . $sitecode . "storage.";
								$url .= ( $sitecode == "sie" ) ? "ir" : $sitecode;
								$url .= ".intel.com";	
														
								print "<option value='$url'";
								if( $common->site == $sitecode )
								{
										print " selected='selected'";
								}
								print ">" . $sitename . "</option>\n";
							}

					?>
					</select>
			<br>
			<br>
			<h2 class='hr' align='right' style='border-bottom:1px dashed #99f;margin-left:10px'>Home</h2>
				<a href='index.php'>Homepage</a><br />				
				<a href='users.php?requested_user=<?php print $common->current_user; ?>'>My Storage</a><br />
		<?php

			if( $viewall )
			{
		?>

				<a href='createreport.php'>Create Report</a><br/>
				<a href='trending.php'>Trending</a>
		<?php
			}
		?>

			<br>
			<br>							
			<h2 class='hr' align='right' style='border-bottom:1px dashed #99f;margin-left:10px'>All Users</h2>
				<a href='users.php?home=true'>Home Folders</a><br/>
				<a href='users.php?publichome=true'>Public Home Folders</a><br/>
				<a href='users.php?project=true'>Project Folders</a>
			<br>
			<br>
                <?php
                        if( $viewall )
                        {
                ?>
			<h2 class='hr' align='right' style='border-bottom:1px dashed #99f;margin-left:10px'>Business Groups</h2>		
				<a href='businessgroups.php?overview=true'>All Groups</a><br/><br/>
				<select name='bu' style='width:120px' onchange="document.location.href='businessgroups.php?bu=' + this.value">
					<option value=''>Select</option>
				<?php
				
					foreach( $common->businessgroups as $gr )
					{
						print "<option value='" . $gr->BusinessGroup . "'";
						if( $common->bu == $gr->BusinessGroup ) 
						{
							print " selected='selected'";
						}
						print ">" . $gr->BusinessGroup . "</option>\n";
					}	
					
				?>					
				</select>
			<br>
			<br>	
			<h2 class='hr' align='right' style='border-bottom:1px dashed #99f;margin-left:10px'>Projects</h2>	
				<a href='projects.php?overview=true'>All Projects</a><br/><br/>
				<select name='project' style='width:120px' onchange="document.location.href='projects.php?project=' + this.value">
					<option value=''>Select</option>
				<?php
				
					foreach( $common->projects as $gr )
					{
						
						print "<option value='" . $gr->Project . "'";
						
						if( $common->project == $gr->Project ) 
						{
							print " selected='selected'";
						}
						
						print ">" . $gr->Project . "</option>\n";
					}	
					
				?>					
				</select>
			<br>
			<br>	
			<h2 class='hr' align='right' style='border-bottom:1px dashed #99f;margin-left:10px'>Owners</h2>	
				<a href='owners.php?overview=true'>All Owners</a><br/><br/>
				<select name='owner' style='width:120px' onchange="document.location.href='owners.php?owner=' + this.value">	
					<option value=''>Select</option>
				<?php
				
					foreach( $common->owners as $gr )
					{
						print "<option value='" . $gr->Owner . "'";
						
						if( $common->owner == $gr->Owner ) 
						{
							print " selected='selected'";
						}
						
						print ">" . $gr->Owner . "</option>\n";
					}
					
				?>					
				</select>
			<br>
			<br>	
			<h2 class='hr' align='right' style='border-bottom:1px dashed #99f;margin-left:10px'>Download <img align='bottom' src='images/csv.png' border='0'/></h2>
				<a href='stoddump/<?php print $common->site; ?>/areas.csv'>Areas</a><br/>
				<a href='stoddump/<?php print $common->site; ?>/disks.csv'>Disks</a><br/>
				<a href='stoddump/<?php print $common->site; ?>/users.csv'>Users</a><br/>
		<?php
			}
			
		?>
			<br/>
                        <h2 class='hr' align='right' style='border-bottom:1px dashed #99f;margin-left:10px'>Options</h2>
                <?php
                        $link = ( $viewall ) ? "no" : "yes";
                        $name = ( $viewall ) ? "Hide extended menu" : "Show extended menu";
                        print "<a href='index.php?viewall=$link'>$name</a><br />";
                ?>

	
		</td>
		<td width='1000' style='padding:20px;vertical-align:top;border:1px dashed #99f'>
