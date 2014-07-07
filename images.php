<?php


include( "includes/timer.class.php" );
include( "includes/csvrow.class.php" );
include( "includes/ldapuser.class.php" );
include( "includes/ldapgroup.class.php" );
include( "includes/ldap.class.php" );
include( "includes/common.class.php" );

$ldap = new Ldap();
$common = new Common( $ldap );


$sites = array();
$sites[] = "ibw";
$sites[] = "igk";
$sites[] = "ir";
$sites[] = "isw";
$sites[] = "iul";
$sites[] = "ka";
$sites[] = "nc";
$sites[] = "sie";
$sites[] = "tl";
$sites[] = "tm";
$sites[] = "upc";

foreach( $sites as $site )
{
	$lines = file( $common->siteroot . "stoddump/" . $site . "/users.csv" );
	
	foreach( $lines as $line )
	{
		$parts = explode( "," , $line );
		$user = trim( $parts[ count( $parts ) -1 ] );
		
		if( $user == "*" || $user == "" )
		{
			continue;
		}
		
		print $user . ", ";
		$user = $ldap->getldapuser( $user );
		$user->getimage();
	}
}


