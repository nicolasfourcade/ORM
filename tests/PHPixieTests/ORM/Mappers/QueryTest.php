<?php

namespace PHPixieTests\ORM\Mappers;

/**
 * @coversDefaultClass \PHPixie\ORM\Mappers\Query
 */
class QueryTest extends \PHPixieTests\AbstractORMTest
{
    protected $mappers;
    protected $plans;
    protected $steps;
    protected $loaders;
    protected $repositories;
        
    protected $queryMapper;
    
    protected $modelName = 'fairy';
    
    protected $groupMapper;
    protected $preloadMapper;
    protected $cascadeDeleteMapper;
    
    protected $stepClasses = array(
        'query' => 'Query',
        'count' => 'Query\Count',
        'reusableResult' => 'Query\Result\Reusable'
    );
    
    protected $planClasses = array(
        'query' => 'Query',
        'count' => 'Query\Count',
        'steps' => 'Steps',
        'loader' => 'Query\Loader'
    );
    
    protected $loaderClasses = array(
        'preloadingProxy' => 'Proxy\Preloading',
        'cachingProxy' => 'Proxy\Caching',
        'reusableResult' => 'Repository\ReusableResult',
    );
    
    public function setUp()
    {
        $this->mappers = $this->quickMock('\PHPixie\ORM\Mappers');
        $this->plans = $this->quickMock('\PHPixie\ORM\Plans');
        $this->steps = $this->quickMock('\PHPixie\ORM\Steps');
        $this->loaders = $this->quickMock('\PHPixie\ORM\Loaders');
        $this->repositories = $this->quickMock('\PHPixie\ORM\Repositories');
        
        $this->groupMapper = $this->quickMock('\PHPixie\ORM\Mappers\Group');
        $this->preloadMapper = $this->quickMock('\PHPixie\ORM\Mappers\Preload');
        $this->cascadeDeleteMapper = $this->quickMock('\PHPixie\ORM\Mappers\Cascade\Mapper\Delete');
        
        $this->method($this->mappers, 'group', $this->groupMapper, array());
        $this->method($this->mappers, 'preload', $this->preloadMapper, array());
        $this->method($this->mappers, 'cascadeDelete', $this->cascadeDeleteMapper, array());
        
        $this->queryMapper = new \PHPixie\ORM\Mappers\Query(
            $this->mappers,
            $this->plans,
            $this->steps,
            $this->loaders,
            $this->repositories
        );
    }
    
    /**
     * @covers ::__construct
     * @covers ::<protected>
     */
    public function testConstruct()
    {
    
    }
        
    /**
     * @covers ::mapCount
     * @covers ::<protected>
     */
    public function testMapCount()
    {
        $query = $this->getQuery();
        
        $databaseQuery = $this->prepareDatabaseQuery('count');
        $step  = $this->prepareStep('count', array($databaseQuery));
        $plan  = $this->preparePlan('count', array($step));
        
        $this->prepareMapConditons($query, $databaseQuery, $plan);
        $this->assertSame($plan, $this->queryMapper->mapCount($query));
    }
    
    /**
     * @covers ::mapFind
     * @covers ::<protected>
     */
    public function testMapFind()
    {
        $this->findTest(false);
        $this->findTest(true);
    }
    
    /**
     * @covers ::mapDelete
     * @covers ::<protected>
     */
    public function testMapDelete()
    {
        $this->deleteTest(false);
        $this->deleteTest(true);
    }
    
    protected function findTest($withPreload)
    {
        $query = $this->getQuery();
        
        $preload = null;
        if($withPreload) {
            $preload = $this->getPreload();
        }
        
        $repository = $this->prepareRepository($this->modelName);
        $databaseQuery = $this->prepareDatabaseQuery('select', $repository);
        
        $step  = $this->prepareStep('reusableResult', array($databaseQuery));
        
        $loader = $this->prepareLoader('reusableResult', array($repository, $step));
        
        $loadersOffset = 1;
        
        if($withPreload) {
            $preloadingProxy = $this->prepareLoader('preloadingProxy', array($loader), $loadersOffset++);
            $loader = $preloadingProxy;
        }
        
        $cachingProxy = $this->prepareLoader('cachingProxy', array($loader), $loadersOffset++);
        
        $plan  = $this->preparePlan('loader', array($step, $cachingProxy));
        
        $this->prepareMapConditons($query, $databaseQuery, $plan);
        
        if($withPreload) {
            $preloadPlan = $this->getPlan('Steps');
            $this->method($plan, 'preloadPlan', $preloadPlan, array(), 1);
            $this->method($this->preloadMapper, 'map', null, array($preloadingProxy, $this->modelName, $preload, $step), 0);
        }
        
        $this->assertSame($plan, $this->queryMapper->mapFind($query, $preload));
    }
    
    protected function deleteTest($cascade)
    {
        $query = $this->getQuery();
        $repository = $this->prepareRepository($this->modelName);
        
        $deleteQuery = $this->prepareDatabaseQuery('delete', $repository);
        $step  = $this->prepareStep('query', array($deleteQuery));
        $plan  = $this->preparePlan('query', array($step));
        
        $this->method($this->cascadeDeleteMapper, 'isModelHandled', $cascade, array($this->modelName), 0);
        if($cascade) {
            $selectQuery = $this->prepareDatabaseQuery('select', $repository, 1);
            $requiredPlan = $this->getPlan('Steps');
            $this->method($plan, 'requiredPlan', $requiredPlan, array(), 1);
            $this->method($this->cascadeDeleteMapper, 'mapDeleteQuery', null, array($deleteQuery, $selectQuery, $requiredPlan), 1);
        }else{
            $this->prepareMapConditons($query, $deleteQuery, $plan);
        }
        
        
        $this->assertSame($plan, $this->queryMapper->mapDelete($query));
    }
    
    protected function prepareMapConditons($query, $databaseQuery, $plan)
    {
        $conditions = array('test');
        $this->method($query, 'getConditions', $conditions, array());
        
        $requiredPlan = $this->getPlan('Steps');
        $this->method($plan, 'requiredPlan', $requiredPlan, array(), 0);
        
        $this->method($this->groupMapper, 'mapDatabaseQuery', null, array(
            $databaseQuery,
            $conditions,
            $this->modelName,
            $requiredPlan
        ), 0);

    }
    
    protected function prepareDatabaseQuery($type, $repository = null, $at = 0)
    {
        if($repository === null)
            $repository = $this->prepareRepository($this->modelName);
        $query = $this->getDatabaseQuery($type);
        $this->method($repository, 'database'.ucfirst($type).'Query', $query, array(), $at);
        return $query;
    }

    protected function prepareStep($type, $params)
    {
        $step = $this->getStep($this->stepClasses[$type]);
        $this->method($this->steps, $type, $step, $params, 0);
        return $step;
    }
    
    protected function preparePlan($type, $params)
    {
        $plan = $this->getPlan($this->planClasses[$type]);
        $this->method($this->plans, $type, $plan, $params, 0);
        return $plan;
    }
    
    protected function prepareLoader($type, $params, $at = 0)
    {
        $loader = $this->getLoader($this->loaderClasses[$type]);
        $this->method($this->loaders, $type, $loader, $params, $at);
        return $loader;
    }
    
    protected function prepareRepository($name)
    {
        $repository=$this->getRepository();
        $this->method($this->repositories, 'get', $repository, array($name), 0);
        return $repository;
    }
    
    protected function getQuery()
    {
        $query = $this->abstractMock('\PHPixie\ORM\Models\Type\Database\Query');
        $this->method($query, 'modelName', $this->modelName, array());
        return $query;
    }
    
    protected function getRepository()
    {
        return $this->abstractMock('\PHPixie\ORM\Models\Type\Database\Repository');
    }
    
    protected function getDatabaseQuery($type)
    {
        return $this->abstractMock('\PHPixie\Database\Query\Type\\'.ucfirst($type));
    }
                      
    protected function getStep($class)
    {
        return $this->abstractMock('\PHPixie\ORM\Steps\Step\\'.ucfirst($class));
    }
    
    protected function getPlan($class)
    {
        return $this->abstractMock('\PHPixie\ORM\Plans\Plan\\'.ucfirst($class));
    }
    
    protected function getLoader($class)
    {
        return $this->abstractMock('\PHPixie\ORM\Loaders\Loader\\'.ucfirst($class));
    }
    
    protected function getPreload()
    {
        return $this->abstractMock('\PHPixie\ORM\Values\Preload');
    }
}