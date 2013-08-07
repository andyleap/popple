Popple
======
Develop
-------
[![Build Status](https://travis-ci.org/andyleap/popple.png?branch=develop)](https://travis-ci.org/andyleap/popple)

Simple DI container for PHP inspired by Pimple.

What does Popple offer over the simplicity of Pimple?

Quite simply, Popple tries to maintain the same level of simplicity,
while offering additional functionality to make it easier to work with.

For instance, extending services with Pimple requires several steps and
a little bit of work around, as you have to extend the service, and then share it.

With Popple, that's handled for you.  Popple takes the viewpoint that everything is shared, so, first, you create the service.

    $popple = new Popple();
    
    $popple->Share('db', function($p)
    {
      $db = new Popple();
    	$db->Extend('Connect', function()
    	{
    		if(!isset($this['username']))
    		{
    			die('Username required!');
    		}
    		echo 'Connecting as ' . $this['username'];
    	});
    	return $db;
    });

This creates a simple service of the main $popple instance.  It's accessed and used rather simply

    $popple['db']['username'] = 'guest';
    $popple['db']->Connect();

This also showcases another feature of Popple, it's ability to quickly extend out and form a service on it's own,
without requiring formalized classes, while still allowing easy transitioning to a class structure.  Hence, the above is roughly equivalent  to

    public class DB extends Popple
    {
      public function Connect()
      {
        if(!isset($this['username']))
      	{
    			die('Username required!');
    		}
    		echo 'Connecting as ' . $this['username'];
      }
    }
    
    $popple->Share('db', function($p)
    {
      return new DB();
    });

Then, all you do is Mutate the service to alter it

    $popple->Mutate('db', function($db)
    {
      $oldconnect = $db->Connect;
    	$db->Extend('Connect', function() use ($oldconnect)
    	{
    		$oldconnect();
    		if(!isset($this['password']))
    		{
    			die('Password required!');
    		}
    		echo ' with password ' . $this['password'];
    	});
    	return $db;
    });

Then, all you have to do to use the new Connect method is

    $popple['db']['username'] = 'admin';
    $popple['db']['password'] = 'password';
    $popple['db']->Connect();

Note that the Mutation can occur at any point.  The service may have already been instantiated, 
it may be registered and the mutation queued to occur on instantiation, 
or the service may not even be registered yet.  No matter what, it will work out.  This is useful for 
services that mutually alter each other.  Just register the Mutate on the other, and let it all work out.
You can even register Mutates for services that never get added without any ill effects.

The final additional feature of Popple is it's ability to be "Popped", hence the naming.

Popping a Popple causes all of it's shared services to be instantiated, and this instantiation walks down the tree,
in turn popping any additional Popples or anything that implements Poppable.

This seems rather backwards, as part of the advantage of a system like this is to delay or prevent instantiation
as much as possible, but some use cases have slack time when loading could occur, and loading while processing
requests later could be minimized by preloading all services

