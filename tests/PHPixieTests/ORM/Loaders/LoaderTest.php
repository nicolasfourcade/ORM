<?php

namespace PHPixieTests\ORM\Loaders;

/**
 * @coversDefaultClass \PHPixie\ORM\Loaders\Loader
 */
abstract class LoaderTest extends \PHPixieTests\AbstractORMTest
{
    protected $loaders;
    protected $loader;
    
    public function setUp()
    {
        $this->loaders = $this->quickMock('\PHPixie\ORM\Loaders');
        $this->loader = $this->getLoader();
    }
    
    /**
     * @covers ::__construct
     * @covers \PHPixie\ORM\Loaders\Loader::__construct
     * @covers ::<protected>
     */
    public function testConstruct()
    {
    
    }
    
    /**
     * @covers ::asArray
     */
    public function testAsArray()
    {
        $models = array();
        foreach(array(0, 1) as $i) {
            $model = $this->quickMock('\PHPixie\ORM\Model');
            $this->method($model, 'asObject', array($i), array(true), null);
            $models[]=$model;
        }
        
        $iterator = new \ArrayIterator($models);
        $this->method($this->loaders, 'iterator', $iterator, array($this->loader), null);
        
        $this->assertEquals($models, $this->loader->asArray());
        $this->assertEquals(array(array(0), array(1)), $this->loader->asArray(true));
    }
    
    /**
     * @covers ::getIterator
     */
    public function testGetIterator()
    {
        $iterator = new \ArrayIterator(array());
        $this->method($this->loaders, 'iterator', $iterator, array($this->loader), null);
        $this->assertEquals($iterator, $this->loader->getIterator());
    }
    
    /**
     * @covers ::getByOffset
     * @covers ::<protected>
     */
    public abstract function testNotFoundException();
    
    abstract protected function getLoader();
}