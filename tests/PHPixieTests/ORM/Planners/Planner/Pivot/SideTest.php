<?php

namespace PHPixieTests\ORM\Planners\Planner\Pivot;

/**
 * @coversDefaultClass \PHPixie\ORM\Planners\Planner\Pivot\Side
 */
class SideTest extends \PHPixieTests\AbstractORMTest
{
    protected $repository;
    protected $side;
    
    public function setUp()
    {
        $this->repository = $this->quickMock('\PHPixie\ORM\Repositories\Repository');
        $this->side = new \PHPixie\ORM\Planners\Planner\Pivot\Side(array(6), $this->repository, 'pixie');
    }
    
    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
    
    }
    
    /**
     * @covers ::items
     */
    public function testItems()
    {
        $this->assertEquals(array(6), $this->side->items());
    }
    
    /**
     * @covers ::items
     */
    public function testPivotKey()
    {
        $this->assertEquals('pixie', $this->side->pivotKey());
    }
    
    /**
     * @covers ::repository
     */
    public function testRepository()
    {
        $this->assertEquals($this->repository, $this->side->repository());
    }
    
    /**
     * @covers ::connection
     */
    public function testConnection()
    {
        $connection = $this->quickMock('\PHPixie\Database\Connection');
        $this->method($this->repository, 'connection', $connection, array(), 0);
        $this->assertEquals($connection, $this->side->connection());
    }
}