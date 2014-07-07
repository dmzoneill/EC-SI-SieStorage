<?php

class Timer
{
	private $start;
    private static $Instance;

    private function __construct() 
    { 
    }

    public static function I()
    {
        if (!self::$Instance)
        {
            self::$Instance= new Timer();
        }

        return self::$Instance;
    }
	
	public function Start()
	{
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$this->start = $time;
	}
	
	public function End()
	{
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $this->start), 4);
		echo 'Completed in '.$total_time.' seconds.<br>';
	}

}

