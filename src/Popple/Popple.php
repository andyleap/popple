<?php

namespace Popple;

class Popple implements \ArrayAccess, Poppable
{
	private $values = array();
	private $factories = array();
	private $mutators = array();
	private $funcs = array();
	
	private function evaluate($name)
	{
		$value = $this->factories[$name]($this);
		foreach($this->mutators[$name] as $mutator)
		{
			$value = $mutator($value, $this);
		}
		return $value;
	}
	
	public function offsetExists($name)
	{
		return array_key_exists($name, $this->values) || array_key_exists($name, $this->factories);
	}

	public function offsetGet($name)
	{
		if(array_key_exists($name, $this->values))
		{
			return $this->values[$name];
		}
		if(array_key_exists($name, $this->factories))
		{
			$this->values[$name] = $this->evaluate($name);
			return $this->values[$name];
		}
		throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $name));
	}

	public function offsetSet($name, $value)
	{
		$this->values[$name] = $value;
	}

	public function offsetUnset($name)
	{
		unset($this->values[$name]);
	}
	
	public function __call($name, $arguments)
	{
		if(array_key_exists($name, $this->funcs))
		{
			return call_user_func_array($this->funcs[$name], $arguments);
		}
		throw new BadMethodCallException();
	}
	
	public function __get($name)
	{
		if(array_key_exists($name, $this->funcs))
		{
			return $this->funcs[$name];
		}
		return $this->offsetGet($name);
	}
	
	public function __set($name, $value)
	{
		if(array_key_exists($name, $this->funcs))
		{
			throw new OutOfBoundsException();
		}
		$this->offsetSet($name, $value);
	}
	
	public function __clone()
	{
		foreach($this->funcs as &$func)
		{
			$func = $func->bindTo($this);
		}
	}
	
	public function share($name, $factory)
	{
		$this->factories[$name] = $factory;
		if(!array_key_exists($name, $this->mutators))
		{
			$this->mutators[$name] = array();
		}
	}
	
	public function mutate($name, $mutator)
	{
		if(array_key_exists($name, $this->values))
		{
			$this->values[$name] = $mutator($this->values[$name], $this);
		}
		if(!array_key_exists($name, $this->mutators))
		{
			$this->mutators[$name] = array();
		}
		$this->mutators[$name][] = $mutator;
	}

	public function pop()
	{
		foreach(array_keys($this->factories) as $name)
		{
			if(!array_key_exists($name, $this->values))
			{
				$this->values[$name] = $this->evaluate($name);
			}
			if($this->values[$name] instanceof Poppable)
			{
				$this->values[$name]->pop();
			}
		}
	}
	
	public function extend($name, $function)
	{
		$this->funcs[$name] = $function->bindTo($this);
	}
}

