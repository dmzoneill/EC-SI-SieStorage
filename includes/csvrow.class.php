<?php


class CsvRow implements ArrayAccess, Iterator, Countable
{
	protected $compare = "";
    protected $container;

    public function __construct( array $data = array() ) 
	{
		$keys = array_keys( $data );
		$this->compare = $data[ $keys[ 0 ] ];
        $this->container = $data;
    }
	
    public function __get( $key ) 
	{
		if( !array_key_exists( $key , $this->container ) )
		{
			if( array_key_exists( strtolower( $key ) , $this->container ) )
			{
				return $this->container[ strtolower( $key ) ];
			}
			
			return false;
		}
		
        return $this->container[ $key ];
    }

    public function __set( $key , $val ) 
	{
        $this->container[ $key ] = $val;
    }
	
	public function offsetSet( $offset , $value ) 
	{
		if( $value instanceof CsvView )
		{
			if ($offset == "") 
			{
				$this->container[] = $value;
			}
			else 
			{				
				$this->container[$offset] = $value;
			}
		} 
		else 
		{
			throw new Exception("Value have to be a instance of CsvView");
		}
    }

    public function offsetExists( $offset ) 
	{
		return isset( $this->container[ $offset ] );
    }

    public function offsetUnset($offset) 
	{
        unset( $this->container[ $offset ] );
    }

    public function offsetGet( $offset ) 
	{
		$retVal = null;
	
		if( is_int( $offset ) )
		{
			$keys = array_keys( $this->container );
			
			if( count( $keys ) > 0 && $offset < count( $keys ) )
			{
				$retVal = $this->container[ $keys[ $offset ] ];
			}
			else
			{
				$retval = null;
			}	
		}
		else
		{
			$retVal = isset( $this->container[ $offset ] ) ? $this->container[ $offset ] : null;		
		}
	
        return $retVal;
    }

    public function rewind() 
	{
        reset( $this->container );
    }

    public function current() 
	{
        return current( $this->container );
    }

    public function key() 
	{
        return key( $this->container );
    }

    public function next() 
	{
        return next( $this->container );
    }

    public function valid() 
	{
        return $this->current() !== false;
    }    

    public function count() 
	{
		return count( $this->container );
    }
	
	public function __toString()
    {
		return $this->compare;
    }
} 