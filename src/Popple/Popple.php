<?php

namespace Popple;

class Popple implements \ArrayAccess, Poppable
{
	use Extendable;
	
	private $values = array();
	private $factories = array();
	private $mutators = array();
	
	private function Evaluate($name)
	{
		$value = $this->factories[$name]($this);
		foreach($this->mutators[$name] as $mutator)
		{
			$value = $mutator($value);
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
			$this->values[$name] = $this->Evaluate($name);
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
	
	public function Share($name, $factory)
	{
		$this->factories[$name] = $factory;
		if(!array_key_exists($name, $this->mutators))
		{
			$this->mutators[$name] = array();
		}
	}
	
	public function Mutate($name, $mutator)
	{
		$boundmutator = $mutator->bindTo($this);
		if(array_key_exists($name, $this->values))
		{
			$this->values[$name] = $boundmutator($this->values[$name]);
		}
		if(!array_key_exists($name, $this->mutators))
		{
			$this->mutators[$name] = array();
		}
		$this->mutators[$name][] = $boundmutator;
	}

	public function Pop()
	{
		foreach(array_keys($this->factories) as $name)
		{
			if(!array_key_exists($name, $this->values))
			{
				$this->values[$name] = $this->Evaluate($name);
			}
			if($this->values[$name] instanceof Poppable)
			{
				$this->values[$name]->Pop();
			}
		}
	}
}

