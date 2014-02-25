<?php

namespace PHPixie\ORM;

abstract class Driver{
	
	protected $orm;
	
	public function __construct($orm) {
		$this->orm = $orm;
	}
	
	public abstract function repository($model_name, $model_config);
	
}