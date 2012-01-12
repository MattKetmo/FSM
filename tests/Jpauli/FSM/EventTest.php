<?php
namespace Jpauli\FSM;

use Jpauli\FSM\Event\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function testMain()
    {
        $event = new Event('foo');
        $event->setNextState('thestate');
        $event->setAction($action = function() { static $called = 0; $called++; return $called;});
        
        $this->assertEquals('thestate', $event->getNextState());
        $this->assertSame($action, $event->getAction());
        $this->assertEquals('foo', $event->getName());
        $fsm = $this->getMock('Jpauli\FSM\FSM');
        
        $this->assertEquals(1, $event->invokeAction($fsm));
        
        $this->setExpectedException('Jpauli\FSM\Exception\NotCallableException');
        $event->setAction("I'm-not-callable");
    }
}
