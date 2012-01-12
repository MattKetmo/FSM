<?php
namespace Jpauli\FSM;

use Jpauli\FSM\Event\Event;

use Jpauli\FSM\State\State;

use Jpauli\FSM\State\FinalState;
use Jpauli\FSM\State\InitialState;
use Jpauli\FSM\State\IState;

class FSMTest extends \PHPUnit_Framework_TestCase
{
    private $fsm;
    
    public function setUp()
    {
        $this->fsm = new FSM('test');
    }
    
    /* Default FSM base behavior */
    public function assertPreConditions()
    {
        $this->assertEquals(0, count($this->fsm));
        $this->assertEquals('test', $this->fsm->getName());
        $this->assertEquals(0, $this->fsm->getNumberOfFinalStates());
        $this->assertNull($this->fsm->getPayload());
        
        $this->fsm->initialize(); // initial and final default states
        
        $this->assertTrue($this->fsm->isInitialized());
        $this->assertEquals(IState::STATE_INITIAL, $this->fsm->getCurrentState()->getType());
        $this->assertTrue($this->fsm->hasState(InitialState::DEFAULT_NAME));
        $this->assertTrue($this->fsm->hasState(FinalState::DEFAULT_NAME));
        $this->assertEquals(2, count($this->fsm));
        $this->assertEquals(1, $this->fsm->getNumberOfFinalStates());
        
        $this->fsm->reset();
        
        $this->assertEquals(0, count($this->fsm));
        $this->assertEquals(0, $this->fsm->getNumberOfFinalStates());
    }
    
    public function testAddStates()
    {
        $this->fsm->addState('foo');
        $this->fsm->addState('bar');
        
        $this->assertInstanceOf('\Jpauli\FSM\State\IState', $this->fsm->getState('foo'));
        $this->assertEquals('foo', $this->fsm->getState('foo')->getName());
        $this->assertInstanceOf('\Jpauli\FSM\State\IState', $this->fsm->getState('bar'));
        $this->assertEquals('bar', $this->fsm->getState('bar')->getName());
        
        $this->assertEquals(2, count($this->fsm));
        $this->assertEquals(0, $this->fsm->getNumberOfFinalStates());
        
        $this->fsm->removeState('foo')->removeState('bar');
        
        $this->assertEquals(0, count($this->fsm));
        $this->assertEquals(0, $this->fsm->getNumberOfFinalStates());
        
        $this->setExpectedException('Jpauli\FSM\Exception\StateException');
        $this->fsm->addState(42);
    }
    
    public function testPayloadAndPayloadReference()
    {
        $payload = array('foo');
        $this->fsm->setPayload($payload);
        $this->assertSame($payload, $this->fsm->getPayload());
        $payload[42] = 'bar';
        $this->assertArrayHasKey(42, $this->fsm->getPayload());
        array_push($this->fsm->getPayload(), 'bar');
        $this->assertArrayHasKey(43, $this->fsm->getPayload());
        $this->fsm->clearPayload();
        $this->assertNull($this->fsm->getPayload());
    }
    
    public function testDeleteState()
    {
        $this->fsm->initialize();
        $this->fsm->addState(new State('foo'));
        $this->fsm->removeState('foo');
        $this->assertFalse($this->fsm->hasState('foo'));
        $this->assertEquals(2, count($this->fsm));
        
        $this->setExpectedException('Jpauli\FSM\Exception\StateException');
        $this->fsm->removeState('bar');
    }
 
    public function testAddStatesWithInitialStates()
    {
        $this->fsm->addState('foo');
        $this->fsm->addState('bar');
        $this->fsm->initialize();
        
        $this->assertEquals(4, count($this->fsm));
        $this->assertEquals(1, $this->fsm->getNumberOfFinalStates());
    }
    
    public function testReset()
    {
        $this->fsm->addState('foo');
        $this->assertEquals(1, count($this->fsm));
        $this->fsm->reset();
        $this->assertEquals(0, count($this->fsm));
        $this->assertNull($this->fsm->getPayload());
        $this->assertFalse($this->fsm->isInitialized());
    }
    
    public function testCantBeLaunchedIfNotInitialized()
    {
        $this->setExpectedException('Jpauli\FSM\Exception\LogicalException');
        $this->fsm->processEvent('foo');
    }
    
    public function testAddTheSameStateFails()
    {
        $this->fsm->addState(new State('foo'));
        $this->setExpectedException('Jpauli\FSM\Exception\StateException');
        $this->fsm->addState(new State('foo'));
    }
    
    public function testSettingTheCurrentStateWronglyFails()
    {
        $this->setExpectedException('Jpauli\FSM\Exception\StateException', 'valid state');
        $this->fsm->setCurrentState(42);
    }
    
    public function testSettingCurrentStateAsANonExistantStateFails()
    {
        $this->setExpectedException('Jpauli\FSM\Exception\StateException');
        $this->fsm->setCurrentState('baz');
    }

    public function testAddSeveralFinalStates()
    {
        $this->fsm->addFinalState('foo');
        $this->fsm->addFinalState('bar');
        $this->fsm->initialize();
        $this->assertEquals(3, $this->fsm->getNumberOfFinalStates());
    }
    
    public function testTriggerEvents()
    {
        $this->fsm->initialize();
        
        $this->fsm->addState('foo');
        $this->fsm->addState('bar');
        $this->fsm->addTransition(InitialState::DEFAULT_NAME, 'gotofoo', 'foo');
        $this->fsm->addTransition('foo', 'foo2bar', 'bar');
        $this->fsm->addTransition('bar', 'bar2end', FinalState::DEFAULT_NAME);
        
        $this->fsm->processEvent('gotofoo');
        $this->assertEquals('foo', $this->fsm->getCurrentState()->getName());
        
        $this->fsm->processEvent('foo2bar');
        $this->assertEquals('bar', $this->fsm->getCurrentState()->getName());
        $this->assertEquals('foo', $this->fsm->getPreviousState()->getName());
        
        $this->fsm->processEvent('bar2end');
        $this->assertEquals(FinalState::DEFAULT_NAME, $this->fsm->getCurrentState()->getName());
        $this->assertTrue($this->fsm->isFinished());
    }
    
    public function testAStateCantRegisterTheSameEventTwice()
    {
        $this->fsm->addTransition('foo', 'foo2bar', 'bar');
        $this->setExpectedException('Jpauli\FSM\Exception\LogicalException');
        $this->fsm->addTransition('foo', 'foo2bar', 'bar');
    }
    
    public function testSeveralFinalStates()
    {
        $this->fsm->initialize();
        
        $this->fsm->addState('foo')->addState('bar')->addFinalState('final1')->addFinalState('final2');
        $this->fsm->addTransition(InitialState::DEFAULT_NAME, '2foo', 'foo');
        
        $this->fsm->addTransition('foo', 'foo2bar', 'bar');
        $this->fsm->addTransition('foo', 'foo2end', 'final1');
        $this->fsm->addTransition('bar', 'bar2end', 'final2');
        
        $this->fsm->processEvent('2foo')->processEvent('foo2bar')->processEvent('bar2end');
        $this->assertTrue($this->fsm->isFinished());
    }
    
    public function testInitializeFunction()
    {
        $init = function ($fsm) { $fsm->addState(new InitialState('foo'))->setCurrentState('foo');};
        $this->fsm->reset()
                  ->setInitializeFunction($init)
                  ->initialize();
        $this->assertEquals(1, count($this->fsm));
        $this->assertTrue($this->fsm->hasState('foo'));
        
        $this->setExpectedException('Jpauli\FSM\Exception\NotCallableException');
        $this->fsm->setInitializeFunction("I'm-not-callable");
    }
    
    public function testInitializedFSMShouldHaveAFirstStateBeeingAStartingState()
    {
        $this->fsm->setInitializeFunction(function($fsm) { $fsm->addState("Im-not-a-valid-starting-state")->setCurrentState('Im-not-a-valid-starting-state');});
        $this->setExpectedException('Jpauli\FSM\Exception\LogicalException', IState::STATE_INITIAL);
        $this->fsm->initialize();
    }
    
    public function testTriggersEventInBadOrderFails()
    {
        $this->fsm->addState('foo');
        $this->fsm->addState('bar');
        $this->fsm->addTransition('foo', 'foo2bar', 'bar');
        
        $this->fsm->initialize();
        
        $this->fsm->addTransition(InitialState::DEFAULT_NAME, 'gotofoo', 'foo');
        $this->setExpectedException('Jpauli\FSM\Exception\EventException', 'Cant process event');
        $this->fsm->processEvent('foo2bar');
    }
    
    public function testCantTriggerEventsWhenInFinishedState()
    {
        $this->fsm->start();
        $this->setExpectedException('Jpauli\FSM\Exception\LogicalException');
        $this->fsm->processEvent('foo');
    }
    
    public function testFinalStateCannotReceiveAnymoreEvent()
    {
        $this->fsm->initialize();
        $this->setExpectedException('Jpauli\FSM\Exception\LogicalException');
        $this->fsm->addTransition(FinalState::DEFAULT_NAME, 'foo', 'barbaz');
    }
    
    public function testTriggerEventTriggersTheAssociatedCallback()
    {
        $this->fsm->initialize();
        $boundary = 0;
        $this->fsm->addState('foo');
        $this->fsm->addTransition(InitialState::DEFAULT_NAME, '2foo', 'foo', function() use (&$boundary) {$boundary++;});
        
        $this->fsm->processEvent('2foo');
        $this->assertEquals(1, $boundary);
    }
    
    public function testTriggerEventUsesASharedPayload()
    {
        $this->fsm->initialize();
        $payload = 0;
        $this->fsm->setPayload($payload);
        $this->fsm->addState('foo');
        $this->fsm->addTransition(InitialState::DEFAULT_NAME, '2foo', 'foo', function(FSM $fsm, Event $event, $p) {$p++;});
        $this->fsm->addTransition('foo', '2foo', 'foo', function(FSM $fsm, Event $event, $p) {$p++;});
        $this->fsm->processEvent('2foo');
        $this->assertEquals(1, $payload);
        $this->fsm->processEvent('2foo');
        $this->assertEquals(2, $payload);
    }
    
    public function testTransitionsWorkWithManualEvents()
    {
        $this->fsm->initialize();
        $this->fsm->addStates(array('foo','bar'));
        $this->fsm->getState('foo')->addEvent(new Event('foo2bar'))->getEvent('foo2bar')->setNextState('bar');
        $this->fsm->addTransition(InitialState::DEFAULT_NAME, '2foo', 'foo');
        $this->fsm->processEvent('2foo');
        $this->assertEquals('foo', $this->fsm->getCurrentState());
        $this->fsm->processEvent('foo2bar');
        $this->assertEquals('bar', $this->fsm->getCurrentState());
    }
    
    public function testSPLInterfaces()
    {
        $this->fsm[] = $state = new State('foo');
        $this->assertContains($state, $this->fsm);
        $this->assertSame($state, $this->fsm['foo']);
        $this->assertTrue(isset($this->fsm['foo']));
        $this->assertFalse(isset($this->fsm['bar']));
        unset($this->fsm['foo']);
        $this->assertEquals(0, count($this->fsm));
    }
}
