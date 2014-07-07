<?php

class Common
{
	public $current_user = null;
	public $requested_user = null;
	protected $container = array();
	protected $docroot = null;
	public $users = array();
	public $areas = array();
	public $disks = array();
	public $lastupdated = array();
	public $projects = array();
	public $businessgroups = array();
	public $owners = array();
	public $fileservers = array();
	public $allocation = array();
	protected $loadfiles = array();	
	protected $userimages = array();
	public $site;
	public $sitename;
	public $sitenamesarr;
	public $sitehomedir;
	public $sitelabhomedir;
	public $trendingfiles;

	public function __construct( $ldap )
	{
		$this->cleanoldcache();

		if( !isset( $_SERVER[ 'SystemRoot'] ) )
		{
			$this->docroot = "/srv/www/siestorage.ir.intel.com/";
		}
		else
		{
			$this->docroot = "C:/inetpub/wwwroot/";
		}
	
		$this->prepareSiteSettings();

		$this->loadfiles[] = array( &$this->users , "stoddump/" . $this->site . "/users.csv" );
		$this->loadfiles[] = array( &$this->areas , "stoddump/" . $this->site . "/areas.csv" );
		$this->loadfiles[] = array( &$this->disks , "stoddump/" . $this->site . "/disks.csv" );
		$this->loadfiles[] = array( &$this->lastupdated , "stoddump/lastrun" );
		$this->loadfiles[] = array( &$this->projects , "stoddump/" . $this->site . "/projects.csv" );
		$this->loadfiles[] = array( &$this->businessgroups , "stoddump/" . $this->site . "/businessgroups.csv" );
		$this->loadfiles[] = array( &$this->owners , "stoddump/" . $this->site . "/owners.csv" );
		$this->loadfiles[] = array( &$this->fileservers , "stoddump/" . $this->site . "/fileservers.csv" );
		$this->loadfiles[] = array( &$this->allocation , "stoddump/" . $this->site . "/allocation.csv" );
	
		$this->prepare( $ldap );
		$this->loadall();
		$this->sortall();
		$this->userimages = glob( $this->docroot . "images/users/*.jpg" );
	}

	private function prepareSiteSettings()
	{
        $hname = $_SERVER['HTTP_HOST'];
		
		if( $hname == "localhost" )
		{
			$this->site = "sie";
		}
		else
		{
			$hparts = explode( "." , $hname );
			$hcount = count( $hparts ) -3;

			if( $hparts[ $hcount  ] == "ir" && stristr( $hname , "sie" ) )
			{
				$this->site = "sie";
			}	
			else
			{
				$this->site = $hparts[ $hcount  ]; //"sie";
			}
		}
		
		$this->trendingfiles = glob( $this->docroot . "stoddump/" . $this->site . "/trending/*" );
		sort( $this->trendingfiles );

		$homes = array( 
			"ibw" => "/nfs/ibw/disks/ibwusershome",
			"igk" => "/nfs/igk/disks/igk-gwa",
			"ir" => "/nfs/iir/home",
			"isw" => "/nfs/isw/home",
			"iul" => "/nfs/iul/disks/iul_users",
			"ka" => "/nfs/ka/disks/ka-home-disk001",
			"nc" => "/nfs/tl/home",
			"sie" => "/nfs/sie/disks/sie_home_001",
			"tl" => "/nfs/tl/home",
			"tm" => "/nfs/tm/disks/home_disk001",
			"upc" => "/nfs/upc/disks/upc_users_disk001"
		);

		$phomes = array(
			"ibw" => "/nfs/ibw/disks/ibwusershome",
			"igk" => "/nfs/igk/disks/igk-gwa",
			"ir" => "/nfs/iir/home",
			"isw" => "/nfs/isw/home",
			"iul" => "/nfs/iul/disks/iul_users",
			"ka" => "/nfs/ka/disks/ka-home-disk001",
			"nc" => "/nfs/tl/home",
			"sie" => "/nfs/sie/public_home",
			"tl" => "/nfs/tl/home",
			"tm" => "/nfs/tm/disks/home_disk001",
			"upc" => "/nfs/upc/disks/upc_users_disk001"
		);

		$snames = array(
			"ibw" => "Braunschweig",
			"igk" => "Gdansk",
			"ir" => "Leixlip",
			"isw" => "Swindon",
			"iul" => "Ulm",
			"ka" => "Karlsruhe",
			"nc" => "Nice",
			"sie" => "Shannon",
			"tl" => "Toulouse",
			"tm" => "Tampere",
			"upc" => "Barcelona"
		);
		
		$this->sitehomedir = $homes[ $this->site ];
		$this->sitelabhomedir = $phomes[ $this->site ];
		$this->sitename = $snames[ $this->site ];
		$this->sitenamesarr = $snames;
	}
	
	public function __get( $key ) 
	{		
		if( !array_key_exists( $key , $this->container ) )
		{
			return false;
		}

		return $this->container[ $key ];
	}
	
	private function prepare( $ldap )
	{
		if( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) || isset( $_SERVER[ 'AUTH_USER' ] ) )
		{
			$authuser = isset( $_SERVER[ 'PHP_AUTH_USER' ] ) ? $_SERVER[ 'PHP_AUTH_USER' ] : $_SERVER[ 'AUTH_USER' ];
			
			if( stristr( $authuser , "\\" ) )
			{
				$authuser = substr( $authuser , strpos( $authuser , "\\" ) + 1 );
			}
		}
		
		$this->current_user = $ldap->getldapuser( $authuser );		
		$this->requested_user = isset( $_GET[ 'requested_user' ] ) ? $ldap->getldapuser( $_GET[ 'requested_user' ] ) : false;
		$this->container[ 'requested_image' ] = isset( $_GET[ 'requested_image' ] ) ? $_GET[ 'requested_image' ] : false;
		$this->container[ 'debug_user' ] = isset( $_GET[ 'debug_user' ] ) ? $_GET[ 'debug_user' ] : false;
				
		foreach( $_GET as $key => $value )
		{
			$this->container[ $key ] = $value;
		}
		
		unset( $_GET );
	}


	private function loadall()
	{	
		foreach( $this->loadfiles as $loadfile )
		{
			$keys = array();
			$lines = file( $this->docroot . $loadfile[ 1 ] );
			$arr = &$loadfile[0];
			
			for( $t = 0; $t < count( $lines ); $t++ )
			{
				$line = trim( $lines[ $t ] );
				if( $line == "" ) continue;
				
				if( stristr( $line , "," ) )
				{
					$parts = explode( "," , $lines[ $t ] );
				
					if( $t == 0 )
					{
						$keys = $parts;
					}
					else
					{
						$row = array();
						
						for( $s = 0; $s < count( $parts ); $s++ )
						{				
							$row[ trim( $keys[ $s ] ) ] = $parts[ $s ];
						}
						
						$arr[] = new CsvRow( $row );
					}
				}
				else
				{
					if( $t == 0 )
					{
						$keys = $line;
					}
					else
					{
						$row = array();							
						$row[ $keys ] = $line;				
						$arr[] = new CsvRow( $row );
					}
				}
			}
		}
	}

	static function mysort( $a , $b )
	{
		return ( strcasecmp( $a , $b ) >= 0 ) ? 1 : -1;
	}

	private function sortall()
	{
		$this->projects = array_unique( $this->projects , SORT_REGULAR ); 
		$this->owners = array_unique( $this->owners , SORT_REGULAR );
		$this->businessgroups = array_unique( $this->businessgroups , SORT_REGULAR );

		usort( $this->projects , array( 'Common' , 'mysort' ) );
		usort( $this->owners , array( 'Common' , 'mysort' ) );
		usort( $this->businessgroups , array( 'Common' , 'mysort' ) );
	}
	
	public function createpiechart( $name , $div , $title , $stats , $size )
	{
		$chart = "<script type=\"text/javascript\">   

			var $name;
			
			$(document).ready(function() 
			{
			
				$name = new Highcharts.Chart({
					chart: {
						renderTo: '" . $div . "',
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false
					},
					title: {
						text: '" . $title . "'
					},
					tooltip: {
						pointFormat: '{series.name}: <b>{point.y} $size</b>',
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
									return '<b>'+ this.point.name +'</b>:' + parseFloat(this.y).toFixed(2) + ' $size';
								}
							}
						}
					},
					series: [{
						type: 'pie',
						name: 'Disks Usage',
						data: [";			
								
			$biggest = 0;
			
			foreach( $stats as $key => $value )
			{
				if( $value > $biggest )
				{
					$biggest = $value;
				}
			}

			$data = "";
		
			foreach( $stats as $key => $value )
			{
				if( $value == $biggest )
				{
					$data .= "{ name: '" . trim( $key ) . "', y: " . $value . ", sliced: true, selected: true },\n";
				}
				else
				{
					$data .= "[ '" . trim( $key ) . "' , " . $value . " ],\n";
				}
			}
		
			$data = substr( $data , 0 , -2 );
			
			$chart .= $data;
							 
			$chart .= "			]
					}]
				});		
			});
		</script>";
		
		return $chart;
	}
	
	
	public function getuserimage( $idsid , $ldap )
	{		
		if( in_array( $this->docroot . "images/users/" . $idsid . ".jpg" , $this->userimages ) )
		{
			return "images/users/" . $idsid . ".jpg";
		}
		else
		{
			return "images/person.png";
		}
	}

	
	public function cleanoldcache()
	{
		shell_exec( "find " . $this->docroot . "cache/ -type f -mtime +1 -exec rm {} \;" );	
	}
}

