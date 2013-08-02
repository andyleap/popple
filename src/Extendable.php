<?php

/**
 * Description of Extendable
 *
 * @author Vendan
 */
trait Extendable
{
	private $funcs = array();
	
	public function __call($name, $arguments)
	{
		if(array_key_exists($name, $this->funcs))
		{
			return call_user_func_array($this->funcs[$name], $arguments);
		}
		throw new BadMethodCallException();
	}
	
	public function Extend($name, $function)
	{
		$this->funcs[$name] = $function->bindTo($this);
	}
}

