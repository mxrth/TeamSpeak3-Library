<?php

namespace devmx\Teamspeak3\Query;
use devmx\Teamspeak3\Query\Response\CommandResponse;
use devmx\Teamspeak3\Query\Response\Event;


require_once dirname( __FILE__ ) . '/../../../../../src/devmx/Teamspeak3/Query/ServerQuery.php';

/**
 * Test class for ServerQuery.
 * Generated by PHPUnit on 2012-01-26 at 19:03:35.
 */
class ServerQueryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \devmx\Teamspeak3\Query\ServerQuery
     */
    protected $query;
    
    /**
     *@var \devmx\Teamspeak3\Query\Transport\QueryTransportStub
     */
    protected $stub;
    
    /**
     *@var \devmx\Teamspeak3\Query\Transport\Decorator\DebuggingDecorator
     */
    protected $transport;
    
    /**
     * @var \devmx\Teamspeak3\Query\Transport\Decorator\CachingDecorator
     */
    protected $cachedTransport;
    
    /**
     * @var \devmx\Teamspeak3\Query\Transport\Decorator\Caching\Cache\InMemoryCache
     */
    protected $cache;

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->stub = new Transport\QueryTransportStub;
        $this->transport = new Transport\Decorator\DebuggingDecorator($this->stub);
        $this->query = new ServerQuery($this->transport);
    }
    
    protected function needsCachedQuery() {
        $this->stub = new Transport\QueryTransportStub;
        $this->cache = new Transport\Decorator\Caching\Cache\InMemoryCache(100);
        $this->cachedTransport = new Transport\Decorator\CachingDecorator($this->stub, $this->cache);
        $this->transport = new Transport\Decorator\DebuggingDecorator($this->cachedTransport);
        $this->query = new ServerQuery($this->transport);
    }
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::__construct
     * @covers devmx\Teamspeak3\Query\ServerQuery::getTransport
     */
    public function testConstruct() {
        $this->assertEquals($this->transport,$this->query->getTransport());
    }
    
        
    /**
     * @dataProvider whoamiProvider
     * @covers devmx\Teamspeak3\Query\ServerQuery::refreshWhoAmI
     * @covers devmx\Teamspeak3\Query\ServerQuery::isOnVirtualServer
     * @covers devmx\Teamspeak3\Query\ServerQuery::isLoggedIn
     * @covers devmx\Teamspeak3\Query\ServerQuery::getLoginName
     * @covers devmx\Teamspeak3\Query\ServerQuery::getVirtualServerPort
     * @covers devmx\Teamspeak3\Query\ServerQuery::getVirtualServerID
     * @covers devmx\Teamspeak3\Query\ServerQuery::getChannelID
     * @covers devmx\Teamspeak3\Query\ServerQuery::getVirtualServerStatus
     * @covers devmx\Teamspeak3\Query\ServerQuery::getUniqueID
     * @covers devmx\Teamspeak3\Query\ServerQuery::getNickname
     * @covers devmx\Teamspeak3\Query\ServerQuery::getDataBaseID
     * @covers devmx\Teamspeak3\Query\ServerQuery::getUniqueVirtualServerID
     * @covers devmx\Teamspeak3\Query\ServerQuery::getClientID
     */
    public function testWhoAmI($items, $values)
    {
        $this->needsCachedQuery();
        $cmd = new Command('whoami');
        $this->stub->addResponse(new CommandResponse($cmd, array($items)));
        $this->query->connect();
        foreach($values as $method => $expected) {
            $this->assertEquals($expected, $this->query->$method(), "Testing $method");
        }
    }
    
    public function whoamiProvider() {
        $items1 = array(
          'virtualserver_status' => 'online',
          'virtualserver_id' => 1,
          'virtualserver_unique_identifier' => 'foo',
          'virtualserver_port' => 9987,
          'client_id' => 11,
          'client_channel_id' => 123,
          'client_nickname' => 'foobar',
          'client_database_id' => 0,
          'client_login_name' => 'asdf',
          'client_unique_identifyer' => 'sdfsdf',
        );
        $expected1 = array(
            'isOnVirtualServer' => true,
            'isLoggedIn' => true,
            'getLoginName' => 'asdf',
            'getVirtualServerPort' => 9987,
            'getVirtualServerID' => 1,
            'getChannelID' => 123,
            'getVirtualServerStatus' => 'online',
            'getUniqueID' => 'sdfsdf',
            'getNickname' => 'foobar',
            'getDataBaseID' => 0,
            'getUniqueVirtualServerID' => 'foo',
            'getClientID' => 11,
        );
        
        $items2 = array(
          'virtualserver_status' => 'unknown',
          'virtualserver_id' => 0,
          'virtualserver_port' => 0,
          'virtualserver_unique_identifier' => '',
          'client_id' => 0,
          'client_channel_id' => 0,
          'client_nickname' => '',
          'client_database_id' => 0,
          'client_login_name' => '',
          'client_unique_identifyer' => '',
        );
        $expected2 = array(
            'isOnVirtualServer' => false,
            'isLoggedIn' => false,
            'getLoginName' => '',
            'getVirtualServerPort' => 0,
            'getVirtualServerID' => 0,
            'getChannelID' => 0,
            'getVirtualServerStatus' => 'unknown',
            'getUniqueID' => '',
            'getNickname' => '',
            'getDataBaseID' => 0,
            'getUniqueVirtualServerID' => '',
            'getClientID' => 0,
        );
        
        return array(
            array($items1, $expected1),
            array($items2, $expected2),
        );
    }
    

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::login
     * @covers devmx\Teamspeak3\Query\ServerQuery::isLoggedIn
     */
    public function testLogin()
    {
        $this->needsCachedQuery();
        $this->expectWhoAmI(array('client_login_name'=>'foo'));
        $cmd = new Command('login', array('client_login_name'=>'foo', 'client_login_password'=>'bar'));
        $r = new CommandResponse($cmd, array());
        $this->stub->addResponse($r);
        
        $this->query->connect();
        $this->query->login('foo', 'bar');
        $this->assertEquals('foo', $this->query->getLoginName());
        $this->assertEquals('bar', $this->query->getLoginPass());
    }
    
    public function testLogin_responseByReference() {
        $response = '';
        $cmd = new Command('login', array('client_login_name'=>'foo', 'client_login_password'=>'bar'));
        $r = new CommandResponse($cmd);
        $this->stub->addResponse($r);
        $this->stub->connect();
        $this->query->login('foo', 'bar', $response);
        $this->assertEquals($r, $response);
    }
    
    /**
     * @expectedException \RunTimeException
     * @covers devmx\Teamspeak3\Query\ServerQuery::login
     * @covers devmx\Teamspeak3\Query\ServerQuery::isLoggedIn
     */
    public function testLogin_failed()
    {
        $cmd = new Command('login', array('client_login_name'=>'foo', 'client_login_password'=>'bar'));
        $r = new CommandResponse($cmd, array(), 123, 'there was an error');
        $this->stub->addResponse($r);
        $this->query->connect();
        $this->query->login('foo','bar');
    }

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::logout
     * @covers devmx\Teamspeak3\Query\ServerQuery::isLoggedIn
     */
    public function testLogout()
    {
        $this->testLogin();
        $this->cache->flush();
        $cmd = new Command('logout');
        $r = new CommandResponse($cmd);
        $this->stub->addResponse($r);
        $this->query->logout();
        $this->expectWhoAmI(array());
        $this->assertFalse($this->query->isLoggedIn());
        $this->stub->assertAllResponsesReceived();
    }
    
    public function testLogout_responseByReference() {
        $this->testLogin();
        $r = new CommandResponse(new Command('logout'));
        $this->stub->addResponse($r);
        $response = '';
        $this->query->logout($response);
        $this->assertEquals($r, $response);
    }

    
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::useByPort
     */
    public function testUseByPort()
    {
       $this->needsCachedQuery();
       $cmd = new Command('use', array('port'=>9987), array('virtual'));
       $r = new CommandResponse($cmd);
       $this->stub->addResponse($r);
       $this->query->connect();
       $this->query->useByPort(9987);
       
       $this->expectWhoAmI(array('virtualserver_port'=>9987));
       $this->assertTrue($this->query->isOnVirtualServer());
       $this->assertEquals(9987, $this->query->getVirtualServerPort(false));
       $this->stub->assertAllResponsesReceived();
    }
    
    public function testUseByPort_responseByReference() {
       $cmd = new Command('use', array('port'=>9987), array('virtual'));
       $r = new CommandResponse($cmd);
       $this->stub->addResponse($r);
       $this->query->connect();
       $response = '';
       $this->query->useByPort(9987, true, $response);
       $this->assertEquals($r, $response);
    }

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::useByID
     */
    public function testUseByID()
    {
       $this->needsCachedQuery();
       $r = $this->useByID(15);
              
       $this->expectWhoAmI(array('virtualserver_id'=>15, 'virtualserver_port'=>123));
       $this->assertTrue($this->query->isOnVirtualServer());
       $this->assertEquals(15, $this->query->getVirtualServerID());
       $this->stub->assertAllResponsesReceived();
    }
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::useByID
     * @expectedException \InvalidArgumentException
     */
    public function testUseByID_invalidID()
    {
        $this->useByID(0);
    }
    
    public function testUseByID_getResponseByReference() {
        $r = new CommandResponse(new Command('use', array('sid'=>17)));
        $this->stub->addResponse($r);
        $response = '';
        $this->query->connect();
        $this->query->useByID(17, false, $response);
        $this->assertEquals($r, $response);
    }

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::deselect
     */
    public function testDeselect()
    {
       $this->needsCachedQuery();
       $this->useByID(32);
       $this->stub->addResponse(new CommandResponse(new Command('use')));
       $this->query->deselect();
       $this->stub->assertAllResponsesReceived();
       
       $this->expectWhoAmI(array());
       $this->assertFalse($this->query->isOnVirtualServer());
       $this->assertEquals(0, $this->query->getVirtualServerID());

    }
    
    public function testDeselect_getResponseByReference() {
        $this->useByID(123);
        $r = new CommandResponse(new Command('use'));
        $this->stub->addResponse($r);
        $response = '';
        $this->query->deselect($response);
        $this->assertEquals($r, $response);
    }
    
    protected function useByID($id) {
       $cmd = new Command('use', array('sid'=>$id), array('virtual'));
       $r = new CommandResponse($cmd);
       $this->stub->addResponse($r);
       $this->query->connect();
       $this->query->useByID($id);
       return $r;
    }

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::moveToChannel
     */
    public function testMoveToChannel()
    {
        $this->needsCachedQuery();
        $cmd = new Command('clientmove', array('clid'=>15, 'cid'=>12));
        
        $this->stub->addResponse(new CommandResponse(new Command('use', array('port'=>123), array('virtual'))));
        $this->stub->addResponse(new CommandResponse($cmd));
        $this->expectWhoAmI(array('port'=>123, 'client_id'=>15, 'client_channel_id'=>12, 'virtualserver_port'=>123));
        
        $this->query->connect();
        $this->query->useByPort(123);
        $this->query->moveToChannel(12);
        
        $this->assertEquals(12, $this->query->getChannelID());
        $this->stub->assertAllResponsesReceived();
    }
    
    /**
     * @expectedException devmx\Teamspeak3\Query\Exception\LogicException
     * @covers devmx\Teamspeak3\Query\ServerQuery::moveToChannel
     */
    public function testMoveToChannel_notOnVirtualServer()
    {
        $cmd = new Command('clientmove', array('clid'=>15, 'cid'=>12));
        $this->stub->addResponse(new CommandResponse($cmd));
        $this->expectWhoAmI(array());
        
        $this->query->connect();
        $this->query->moveToChannel(12);
    }
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::registerForEvent
     * @covers devmx\Teamspeak3\Query\ServerQuery::getRegisterCommands
     */
    public function testRegisterForEvent()
    {
        $cmd = new Command('servernotifyregister', array('event'=>'foobar'));
        $r = new CommandResponse($cmd);
        $this->stub->addResponse($r);
        $this->query->connect();
        $this->query->registerForEvent('foobar');
        $this->assertEquals(array($cmd), $this->query->getRegisterCommands());
    }
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::registerForEvent
     * @covers devmx\Teamspeak3\Query\ServerQuery::getRegisterCommands
     */
    public function testRegisterForEvent_cid()
    {
        $cmd = new Command('servernotifyregister', array('event'=>'foobar', 'id'=>123));
        $r = new CommandResponse($cmd);
        $this->stub->addResponse($r);
        $this->query->connect();
        $this->query->registerForEvent('foobar', 123);
        $this->assertEquals(array($cmd), $this->query->getRegisterCommands());
        $this->stub->assertAllResponsesReceived();
    }
    
    /**
     * @expectedException \RuntimeException
     * @covers devmx\Teamspeak3\Query\ServerQuery::registerForEvent
     */
    public function testRegisterForEvent_error()
    {
        $cmd = new Command('servernotifyregister', array('event'=>'foobar', 'id'=>123));
        $r = new CommandResponse($cmd, array(), 123, 'error');
        $this->stub->addResponse($r);
        $this->query->connect();
        $this->query->registerForEvent('foobar', 123);
    }

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::unregisterEvents
     */
    public function testUnregisterEvents()
    {
        $cmd = new Command('servernotifyunregister');
        $cmd2 = new Command('servernotifyregister', array('event'=>'foo'));
        $r = new CommandResponse($cmd);
        $r2 = new CommandResponse($cmd2);
        $this->stub->addResponse($r);
        $this->stub->addResponse($r2);
        $this->query->connect();
        $this->query->registerForEvent('foo');
        $this->query->unregisterEvents();
        $this->stub->assertAllResponsesReceived();
    }

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::quit
     */
    public function testQuit()
    {
        $this->query->connect();
        $this->query->quit();
        $this->assertFalse($this->query->isConnected());
    }

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::__sleep
     * @covers devmx\Teamspeak3\Query\ServerQuery::__wakeup
     * @covers devmx\Teamspeak3\Query\ServerQuery::recoverState
     */
    public function testSerializeUnserialize()
    {
        $r1 = new CommandResponse(new Command('use', array('sid'=>12), array('virtual')));
        $r2 = new CommandResponse(new Command('servernotifyregister', array('event'=>'foo')));
        $r3 = new CommandResponse(new Command('login', array('client_login_name'=>'foo', 'client_login_password'=>'bar')));
        $this->stub->addResponses(array($r1, $r2, $r3));
        $this->query->connect();
        $this->query->useByID(12);
        $this->query->registerForEvent('foo');
        $this->query->login('foo', 'bar');
        $this->stub->assertAllResponsesReceived();
        $this->stub->addResponses(array($r1, $r2, $r3));
        $serialized = serialize($this->query);
        $this->assertFalse($this->transport->isConnected());
        $unserialized = unserialize($serialized);
        $this->assertEquals(array($r2->getCommand()), $unserialized->getRegisterCommands());        
    }

    

    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::connect
     * @covers devmx\Teamspeak3\Query\ServerQuery::isConnected
     */
    public function testConnect()
    {
        $this->query->connect();
        $this->assertTrue($this->query->isConnected());
        $this->assertTrue($this->stub->isConnected());
    }

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::disconnect
     * @covers devmx\Teamspeak3\Query\ServerQuery::isConnected
     */
    public function testDisconnect()
    {
        $this->query->connect();
        $this->query->disConnect();
        $this->assertFalse($this->query->isConnected());
        $this->assertFalse($this->stub->isConnected());
    }

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::getAllEvents
     */
    public function testGetAllEvents()
    {
        $e = new Event('notifyfoo', array());
        $this->stub->addEvent($e);
        $this->stub->addResponse(new CommandResponse(new Command('servernotifyregister', array('event' => 'foo'))));
        $this->query->connect();
        $this->query->registerForEvent('foo');
        $this->assertEquals(array($e), $this->query->getAllEvents());
    }
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::getAllEvents
     * @expectedException \LogicException
     */
    public function testGetAllEvents_Exception()
    {
        $e = new Event('notifyfoo', array());
        $this->stub->addEvent($e);
        $this->query->getAllEvents();
    }

        

    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::waitForEvent
     * @covers devmx\Teamspeak3\Query\ServerQuery::hasRegisteredForEvents
     */
    public function testWaitForEvent()
    {
        $e = new Event('notifyfoo', array());
        $this->stub->addEvent($e);
        $this->stub->addResponse(new CommandResponse(new Command('servernotifyregister', array('event'=>'channel'))));
        
        $this->query->connect();
        $this->query->registerForEvent('channel');
        $this->assertEquals(array($e), $this->query->waitForEvent());
    }
    
    /**
     * @expectedException \LogicException
     * @covers devmx\Teamspeak3\Query\ServerQuery::waitForEvent
     * @covers devmx\Teamspeak3\Query\ServerQuery::hasRegisteredForEvents
     */
    public function testWaitForEvent_notRegistered()
    {
        $e = new Event('notifyfoo', array());
        $this->stub->addEvent($e);
        
        $this->query->connect();
        $this->assertEquals(array($e), $this->query->waitForEvent());
    }
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::sendCommand
     */
    public function testSendCommand()
    {
        $cmd = new Command('foo');
        $r = new CommandResponse($cmd);
        $this->stub->addResponse($r);
        $this->query->connect();
        $this->assertEquals($r, $this->query->sendCommand($cmd));
    }
    
    public function testSendCommand_use() {
        $cmd = new Command('use', array('sid'=>1), array('virtual'));
        $r = new CommandResponse($cmd);
        $this->stub->addResponse($r);
        $this->query->connect();
        $this->assertEquals($r, $this->query->sendCommand($cmd));
    }
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::sendCommand
     * @covers devmx\Teamspeak3\Query\ServerQuery::useVirtualServer
     */
    public function testSendCommand_recognizeUse() {
        $cmd = new Command('use', array('port'=>9987), array('virtual'));
        $this->stub->addResponse(new CommandResponse($cmd));
        $this->query->connect();
        $this->query->sendCommand($cmd);
        $this->stub->assertAllResponsesReceived();
    }
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::sendCommand
     * @covers devmx\Teamspeak3\Query\ServerQuery::getLoginPass
     */
    public function testSendCommand_recognizeLoginAndLogout() {
        $cmd = new Command('login', array('client_login_name'=>'foo', 'client_login_password'=>'bar'));
        $this->stub->addResponse(new CommandResponse($cmd));
        $this->query->connect();
        $this->query->sendCommand($cmd);
        $this->stub->assertAllResponsesReceived();
        $this->stub->addResponse(new CommandResponse(new Command('logout')));
        $this->query->query('logout');
        $this->stub->assertAllResponsesReceived();
    }
    
    
    /**
     * @covers devmx\Teamspeak3\Query\ServerQuery::query
     */
    public function testQuery()
    {
        $cmd = new Command('foo');
        $r = new CommandResponse($cmd);
        $this->stub->addResponse($r);
        $this->query->connect();
        $this->assertEquals($r, $this->query->query('foo'));
    }
    
    /*public function testGetClientId() {
        $query = $this->getMockBuilder('\devmx\Teamspeak3\Query\ServerQuery')
                      ->setConstructorArgs(array($this->transport))
                      ->setMethods(array('refreshWhoAmI', 'isOnVirtualServer'))->getMock();
        $query->expects($this->once())
              ->method('isOnVirtualServer')
              ->will($this->returnValue(true));
        $query->expects($this->once())
             ->method('refreshWhoAmI');
        $query->getClientID();
    }*/
    
    public function testChangeNickname() {
        $this->needsCachedQuery();
        $this->query->connect();
        $r1 = new CommandResponse(new Command('use', array('port'=>9987)));
        $this->stub->addResponse($r1);
        $this->expectWhoAmI(array('client_id'=>12, 'virtualserver_port'=>9987));
        $r3 = new CommandResponse(new Command('clientedit', array('clid'=>12, 'client_nickname'=>'FooBar')));
        $this->stub->addResponse($r3);
        $this->query->useByPort(9987, false);
        $this->query->changeNickname('FooBar');
        $this->stub->assertAllResponsesReceived();
    }
    
    /**
     * @expectedException \devmx\Teamspeak3\Query\Exception\LogicException 
     */
    public function testChangeNickname_ErrorWhenNotOnVServer() {
        $this->query->connect();
        $this->query->changeNickname('FooBar');
    }
    
    /**
     * @expectedException \devmx\Teamspeak3\Query\Exception\CommandFailedException
     */
    public function testChangeNickname_ErrorOnCommandFailure() {
        $this->needsCachedQuery();
        $this->query->connect();
        $r1 = new CommandResponse(new Command('use', array('port'=>9987)));
        $this->stub->addResponse($r1);
        $this->expectWhoAmI(array('client_id'=>12, 'virtualserver_port'=>9987));
        $r3 = new CommandResponse(new Command('clientedit', array('clid'=>12, 'client_nickname'=>'FooBar')), array(), 12, 'failed');
        $this->stub->addResponse($r3);
        $this->query->useByPort(9987, false);
        $this->query->changeNickname('FooBar');
        $this->stub->assertAllResponsesReceived();
    }
    
    protected function expectWhoAmI($values, $errorID=0, $errorMsg='ok') {
        $items = array(
            'virtualserver_status' => 'unknown',
            'virtualserver_unique_identifyer' => '',
            'virtualserver_port' => 0,
            'virtualserver_id' => 0,
            'client_id' => 0,
            'client_channel_id' => 0,
            'client_nickname' => '',
            'client_database_id' => 0,
            'client_login_name' => '',
            'client_unique_identifyer' => '',
            'client_server_origin' => 0
        );
        foreach($values as $name => $val) {
            $items[$name] = $val;
        }
        $this->stub->addResponse(new CommandResponse(new Command('whoami'), array($items), $errorID, $errorMsg));
    }
}

?>
