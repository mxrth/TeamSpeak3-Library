<?php

namespace devmx\Teamspeak3\Query\Transport\Decorator\Caching;
use devmx\Teamspeak3\Query\Command;
use devmx\Teamspeak3\Query\CommandResponse;
use devmx\Teamspeak3\Query\CommandAwareQuery;

require_once dirname( __FILE__ ) . '/../../../../../../../../src/devmx/Teamspeak3/Query/Transport/Decorator/Caching/CachingDecorator.php';

/**
 * Test class for CachingDecorator.
 * Generated by PHPUnit on 2012-03-31 at 11:41:55.
 */
class CachingDecoratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CachingDecorator
     */
    protected $decorator;
    
    /**
     * @var \devmx\Teamspeak3\Query\Transport\Decorator\Caching\CachingDecorator
     */
    protected $cache;
    
    /**
     * @var \devmx\Teamspeak3\Query\Transport\QueryTransportStub
     */
    protected $query;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->cache = $this->getMockForAbstractClass('\devmx\Teamspeak3\Query\Transport\Decorator\Caching\CacheInterface');
        $this->query = new \devmx\Teamspeak3\Query\Transport\QueryTransportStub();
        $this->decorator = new CachingDecorator($this->query, $this->cache);

    }

    /**
     * @covers devmx\Teamspeak3\Query\Transport\Decorator\Caching\CachingDecorator::connect
     * A call to connect should be delayed.
     */
    public function testConnect()
    {
        $this->query->expectConnection(false);
        $this->decorator->connect();
    }

    /**
     * @covers devmx\Teamspeak3\Query\Transport\Decorator\Caching\CachingDecorator::sendCommand
     * @todo Implement testSendCommand().
     */
    public function testSendCommand_delayed()
    {
        $this->decorator->setDelayableCommands(array('use'));
        $this->decorator->setCacheableCommands(array());
        $this->query->expectConnection(false);
        $this->decorator->sendCommand(new Command('use'));
    }
    
    public function testSendCommand_applyDelayed() {
        $this->decorator->setDelayableCommands(array('use'));
        $this->decorator->setCacheableCommands(array());
        
        $use_cmd = new Command('use');
        $use_r = new CommandResponse($use_cmd);
        $cl_cmd = new Command('channellist');
        $cl_r = new CommandResponse($cl_cmd);
        
        $this->query->expectConnection(false);
        
        $this->assertFalse($this->decorator->sendCommand($use_cmd)->errorOccured());
        
        $this->query->expectConnection();
        
        $this->query->addResponses(array($use_r, $cl_r));
        
        $this->assertEquals($cl_r , $this->decorator->sendCommand($cl_cmd));
    }
    
    /**
     * @expectedException \devmx\Teamspeak3\Query\Exception\CommandFailedException 
     */
    public function testSendCommand_applyDelayed_Error() {
        $this->decorator->setDelayableCommands(array('use'));
        $this->decorator->setCacheableCommands(array());
        
        $use_cmd = new Command('use');
        $use_r = new CommandResponse($use_cmd, array(), 123, 'error');
        $cl_cmd = new Command('channellist');
        $cl_r = new CommandResponse($cl_cmd);
        
        $this->query->expectConnection(false);
        
        $this->assertFalse($this->decorator->sendCommand($use_cmd)->errorOccured());
        
        $this->query->expectConnection();
        
        $this->query->addResponses(array($use_r, $cl_r));
        
        $this->decorator->sendCommand($cl_cmd);
    }
    
    public function testSendCommand_cache() {
        $this->decorator->setDelayableCommands(array());
        $this->decorator->setCacheableCommands(array('clientlist'));
        
        $cl_cmd = new Command('clientlist');
        $cl_r = new CommandResponse($cl_cmd);
        
        $this->query->addResponse($cl_r);
        
        $this->cache->expects($this->exactly(2))
                    ->method('isCached')
                    ->with(md5(serialize($cl_cmd)))
                    ->will($this->onConsecutiveCalls(false, true));
        
        $this->cache->expects($this->once())
                    ->method('cache')
                    ->with($this->equalTo(md5(serialize($cl_cmd))), $this->equalTo($cl_r));
        
        $this->cache->expects($this->once())
                    ->method('getCache')
                    ->with($this->equalto(md5(serialize($cl_cmd))))
                    ->will($this->returnValue($cl_r));
        
        $this->assertEquals($cl_r, $this->decorator->sendCommand($cl_cmd));
        $this->assertEquals($cl_r, $this->decorator->sendCommand($cl_cmd));
        
        $this->query->assertAllResponsesReceived();
    }
    
    public function testSendCommand_dontApplyDelayedBeforeCachedCommand() {
        $this->decorator->setDelayableCommands(array('use'));
        $this->decorator->setCacheableCommands(array('channellist'));
        
        $use_cmd = new Command('use');
        $use_r = new CommandResponse($use_cmd);
        $cl_cmd = new Command('channellist');
        $cl_r = new CommandResponse($cl_cmd);
        
        $this->query->expectConnection(false);
                
        $this->assertFalse($this->decorator->sendCommand($use_cmd)->errorOccured());
        
        $this->cache->expects($this->once())
                    ->method('isCached')
                    ->with(md5(serialize($cl_cmd)))
                    ->will($this->returnValue(true));
        
        $this->cache->expects($this->once())
                    ->method('getCache')
                    ->will($this->returnValue($cl_r));
        
        
        $this->assertEquals($cl_r, $this->decorator->sendCommand($cl_cmd));
    }

    /**
     * @dataProvider eventGetterProvider
     */
    public function testGetAllEvents_connect($method)
    {
        $e = new \devmx\Teamspeak3\Query\Event('foobar', array());
        $this->query->addEvent($e);
        $this->assertEquals(array($e), $this->decorator->$method());
        $this->assertTrue($this->query->isConnected());
    }
    
    /**
     * @dataProvider eventGetterProvider
     */
    public function testGetAllEvents_applyBeforeGet($method) {
        $this->decorator->setDelayableCommands(array('use'));
        $this->decorator->setCacheableCommands(array());
        $this->query->expectConnection(false);
        
        $useCommand = new Command('use');
        $useResponse = new CommandResponse($useCommand);
        $event = new \devmx\Teamspeak3\Query\Event('foo', array());
        
        $this->decorator->sendCommand($useCommand);
        
        $this->query->expectConnection();
        $this->query->addResponse($useResponse);
        $this->query->addEvent($event);
        
        $this->assertEquals(array($event), $this->decorator->$method());
        $this->query->assertAllResponsesReceived();
    }
    
    public function eventGetterProvider() {
        return array( array('getAllEvents'), array('waitForEvent') );
    }

    /**
     * @covers devmx\Teamspeak3\Query\Transport\Decorator\Caching\CachingDecorator::getDelayableCommands
     * @todo Implement testGetDelayableCommands().
     */
    public function testSetGetDelayableCommands()
    {
        $this->decorator->setDelayableCommands(array('foo', 'bar'));
        $this->assertEquals(array('foo', 'bar'), $this->decorator->getDelayableCommands());
    }

    /**
     * @covers devmx\Teamspeak3\Query\Transport\Decorator\Caching\CachingDecorator::getCacheAbleCommands
     * @todo Implement testGetCacheAbleCommands().
     */
    public function testSetGetCacheAbleCommands()
    {
        $this->decorator->setCacheableCommands(array('foo', 'bar'));
        $this->assertEquals(array('foo', 'bar'), $this->decorator->getCacheableCommands());
    }
    
    public function testDefaults() {
        $this->assertEquals(CommandAwareQuery::getNonChangingCommands(), $this->decorator->getCacheAbleCommands());
        $this->assertEquals(CommandAwareQuery::getQueryStateChangingCommands(), $this->decorator->getDelayableCommands());
    }
    
    public function testPrefix() {
        $this->decorator = new CachingDecorator($this->query, $this->cache, 'foo');
        $this->decorator->setDelayableCommands(array());
        $this->decorator->setCacheableCommands(array('clientlist'));
        
        $cl_cmd = new Command('clientlist');
        $cl_r = new CommandResponse($cl_cmd);
        
        $this->query->addResponse($cl_r);
        
        $this->cache->expects($this->exactly(2))
                    ->method('isCached')
                    ->with('foo'.md5(serialize($cl_cmd)))
                    ->will($this->onConsecutiveCalls(false, true));
        
        $this->cache->expects($this->once())
                    ->method('cache')
                    ->with($this->equalTo('foo'.md5(serialize($cl_cmd))), $this->equalTo($cl_r));
        
        $this->cache->expects($this->once())
                    ->method('getCache')
                    ->with($this->equalto('foo'.md5(serialize($cl_cmd))))
                    ->will($this->returnValue($cl_r));
        
        $this->assertEquals($cl_r, $this->decorator->sendCommand($cl_cmd));
        $this->assertEquals($cl_r, $this->decorator->sendCommand($cl_cmd));
        
        $this->query->assertAllResponsesReceived();
    }

}

?>
