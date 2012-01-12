<?php
namespace Jpauli\FSM;

use Jpauli\FSM\State\FinalState;

use Jpauli\FSM\Event\Event;

use Jpauli\FSM\State\IState;
use Jpauli\FSM\State\State;

class StateTest extends \PHPUnit_Framework_TestCase
{
    public function testMain()
    {
        $state = new State('foo');
        $this->assertEquals(0, count($state));
        $this->assertEquals('foo', $state->getName());
        $this->assertEquals('foo', (string)$state);
        $this->assertEquals(IState::STATE_NORMAL, $state->getType());
        $state->addEvent($event = new Event('bar'));
        $this->assertSame($event, $state->getEvent('bar'));
        $this->assertEquals(1, count($state));
        $this->assertContains($event, $state);
        $state->removeEvent('bar');
        
        $this->setExpectedException('Jpauli\FSM\Exception\EventException');
        $state->removeEvent('bazbaz');
    }
    
    public function testFinalState()
    {
        $final = new FinalState();
        $this->assertFalse($final->hasEvent(uniqid()));
        $this->setExpectedException('Jpauli\FSM\Exception\LogicalException');
        $final->addEvent(new Event('foo'));
    }
}
