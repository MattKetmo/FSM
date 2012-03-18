<?php

/*
 * Copyright (c) 2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>,
 * Copyright 2012 PAULI Julien <jpauli@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Jpauli\FSM;

use Jpauli\FSM\State\InitialState;
use Jpauli\FSM\Exception\NotCallableException;
use Jpauli\FSM\Exception\EventException;
use Jpauli\FSM\Exception\LogicalException;
use Jpauli\FSM\State\FinalState;
use Jpauli\FSM\Exception\StateException;
use Jpauli\FSM\State\IState;
use Jpauli\FSM\State\State;
use Jpauli\FSM\Event\Event;

/**
 * A Finite State Machine.
 *
 * Jpauli FSM is based on Jpauli FSM and provides a self configuring Finite State Machine(FSM).
 * The following is a list of features of Jpauli_FSM.
 * o Transition action
 * o Initial and Final pseudo states
 * o User defined payload
 *
 * @copyright  2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2012 PAULI Julien <jpauli@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @link       http://en.wikipedia.org/wiki/Finite_state_machine
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/FSMExample.html
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/
 * @link       http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @link       http://pear.php.net/package/FSM
 * @link       http://www.microsoft.com/japan/msdn/net/aspnet/aspnet-finitestatemachines.asp
 * @link       http://www.generation5.org/content/2003/FSM_Tutorial.asp
 */
class FSM implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var \Jpauli\FSM\State\IState
     */
    protected $currentState;

    /**
     * @var \Jpauli\FSM\IState
     */
    protected $previousState;

    /**
     * Has the FSM been initialized ?
     *
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var array
     */
    protected $states = array();

    /**
     * @var string
     */
    protected $name;

    /**
     * The payload. Can be anything. That's
     * the base FSM heart, the payload is the
     * thing that is shared and altered through states
     *
     * @var mixed
     */
    protected $payload;

    /**
     * Initialize function
     *
     * @var callable
     */
    protected $initializeFunction;

    /**
     * @param string $name The FSM name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * Starts the Finite State Machine.
     *
     * @return \Jpauli\FSM\FSM
     */
    public function start()
    {
        $this->initialize();
        $this->processEvent(Event::EVENT_START);

        return $this;
    }

    /**
     * Weither or not a state exists inside
     * the FSM
     *
     * @param string $stateName
     * @return bool
     */
    public function hasState($stateName)
    {
        return array_key_exists($stateName, $this->states);
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }

    /**
     * Gets the current state.
     *
     * @return \Jpauli\FSM\State\IState
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->states);
    }

    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->states);
    }

    /**
     * Gets the previous state.
     *
     * @return \Jpauli\FSM\IState
     */
    public function getPreviousState()
    {
        return $this->previousState;
    }

    /**
     * Gets the payload.
     *
     * @return mixed $payload
     */
    public function &getPayload()
    {
        return $this->payload;
    }

    /**
     * Retrieves the number of final states
     *
     * @return int
     */
    public function getNumberOfFinalStates()
    {
        $count = 0;
        foreach ($this->states as $state) {
            if ($state->getType() == IState::STATE_FINAL) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Finds and returns the state with the given name.
     *
     * @param string $stateName
     * @return \Jpauli\FSM\State\IState
     * @throws \Jpauli\FSM\Exception\UnknownStateException
     */
    public function getState($stateName)
    {
        if ($this->hasState($stateName)) {
            return $this->states[$stateName];
        }

        throw new StateException("$stateName not found");
    }

    /**
     * Removes a state from the FSM
     *
     * @param string $stateName
     * @return \Jpauli\FSM\FSM
     * @throws \Jpauli\FSM\Exception\StateException
     */
    public function removeState($stateName)
    {
        if (!$this->hasState($stateName)) {
            throw new StateException("State $stateName is missing, can't delete it");
        }

        unset($this->states[$stateName]);

        return $this;
    }

    /**
     * Adds the state with the given name.
     *
     * @param string|\Jpauli\FSM\State\IState $state
     * @return \Jpauli\FSM\FSM;
     * @throws \Jpauli\FSM\Exception\StateException
     */
    public function addState($state)
    {
        if (is_string($state)) {
            $stateName = $state;
            $state = new State($stateName);
        } elseif ($state instanceof IState) {
            $stateName = $state->getName();
        } else {
            throw new StateException("State must be a state name or an IState");
        }

        if ($this->hasState($stateName)) {
            throw new StateException("$stateName already exists into FSM");
        }

        $this->states[$stateName] = $state;

        return $this;
    }

    /**
     * Adds several states in one call
     *
     * @param array $states
     * @return \Jpauli\FSM\FSM
     */
    public function addStates(array $states)
    {
        foreach ($states as $state) {
            $this->addState($state);
        }

        return $this;
    }

    /**
     * Adds a final states
     *
     * @param string $finalStateName
     * @return \Jpauli\FSM\FSM
     */
    public function addFinalState($finalStateName)
    {
        $this->addState(new FinalState($finalStateName));

        return $this;
    }

    /**
     * Is the FSM in a finish state
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->isInitialized && $this->currentState->getType() == IState::STATE_FINAL;
    }

    /**
     * Adds a transition from a starting state to
     * a landing state, binding them with an event
     * States and Events are created if needed
     *
     * @param string $stateName
     * @param string $eventName
     * @param string $nextStateName
     * @param callable $action
     * @return \Jpauli\FSM\FSM
     * @throws \Jpauli\FSM\Exception\LogicalException
     */
    public function addTransition($stateName, $eventName, $nextStateName, $action = null)
    {
        try {
            $state = $this->getState($stateName);
        } catch (StateException $e) {
            $state = new State($stateName);
            $this->addState($state);
        }

        try {
            $event = $state->getEvent($eventName);
            if ($state->hasEvent($eventName)) {
                throw new LogicalException("The state $stateName has already registered even $eventName");
            }
        } catch (EventException $e) {
            $event = new Event($eventName);
            $state->addEvent($event);
        }

        try {
            $nextState = $this->getState($nextStateName);
        } catch (StateException $e) {
            $nextState = new State($nextStateName);
            $this->addState($nextState);
        }

        $event->setNextState($nextStateName);
        $event->setAction($action);

        return $this;
    }

    /**
     * Gets the name of the FSM.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the given shared payload.
     *
     * @param mixed &$payload
     * @return \Jpauli\FSM\FSM
     */
    public function setPayload(&$payload)
    {
        $this->payload = &$payload;

        return $this;
    }

    /**
     * Returns whether the current state has an event with a given name.
     *
     * @param string $name
     * @return boolean
     */
    public function hasEvent($eventName)
    {
        return $this->currentState->hasEvent($eventName);
    }

    /**
     * Removes the payload from the FSM.
     *
     * @return \Jpauli\FSM\FSM
     */
    public function clearPayload()
    {
        $this->payload = null;

        return $this;
    }

    /**
     * Transitions to the next state.
     *
     * @param string $stateName
     * @return \Jpauli\FSM\FSM
     */
    protected function transition($stateName)
    {
        $this->previousState = $this->currentState;
        $this->currentState  = $this->getState($stateName);

        return $this;
    }

    /**
     * Initializes the FSM.
     *
     * @throws \Jpauli\FSM\Exception\LogicalException
     * @return \Jpauli\FSM\FSM
     */
    public function initialize()
    {
        if ($this->isInitialized) {
            return $this;
        }

        if (!$this->initializeFunction) {
            $this->initializeFunction = $this->getDefaultInitializeFunction();
        }

        $function = $this->initializeFunction;
        call_user_func($function, $this);

        if ($this->currentState && $this->currentState->getType() != IState::STATE_INITIAL) {
            throw new LogicalException("First state is expected to be of type " . IState::STATE_INITIAL);
        }

        $this->isInitialized = true;

        return $this;
    }

    /**
     * Sets the FSM init callback
     *
     * @param callable $callable
     * @throws \Jpauli\FSM\Exception\NotCallableException
     * @return \Jpauli\FSM\FSM
     */
    public function setInitializeFunction($callable)
    {
        if (!is_callable($callable)) {
            throw new NotCallableException('The initialize function is not callable.');
        }

        $this->initializeFunction = $callable;

        return $this;
    }

    /**
     * Retrieves the default FSM init function
     *
     * @return \Closure
     */
    private function getDefaultInitializeFunction()
    {
        return function (FSM $fsm) {
            $initialState = new InitialState();
            $fsm->addState($initialState);
            $fsm->setCurrentState($initialState);
            $finalState = new FinalState();
            $fsm->addState($finalState);
            $initialState->getEvent(Event::EVENT_START)->setNextState($finalState->getName());
        };
    }

    /**
     * Sets the current FSM state
     *
     * @param string $state
     * @throws \Jpauli\FSM\Exception\StateException
     * @return \Jpauli\FSM\FSM
     */
    public function setCurrentState($state)
    {
        if (is_string($state)) {
            $state = $this->getState($state);
        } elseif (!$state instanceof IState) {
            throw new StateException("A valid state is expected");
        }
        $this->currentState = $state;

        return $this;
    }

    /**
     * Resets the FSM as if it was new
     *
     * @return \Jpauli\FSM\FSM
     */
    public function reset()
    {
        $this->states = array();
        $this->currentState = $this->previousState
                            = $this->payload
                            = $this->initializeFunction
                            = null;
        $this->isInitialized = false;

        return $this;
    }

    /**
     * Processes an event
     *
     * @param string  $eventName
     * @return \Jpauli\FSM\FSM
     * @throws \Jpauli\FSM\Exception\LogicalException
     * @throws \Jpauli\FSM\Exception\EventException
     */
    public function processEvent($eventName)
    {
        if (!$this->isInitialized) {
            throw new LogicalException('The FSM has not been initialized');
        }
        if ($this->isFinished()) {
            throw new LogicalException('The FSM is in final state');
        }
        if (!$this->hasEvent($eventName)) {
            throw new EventException("Cant process event $eventName on $this->currentState");
        }
        $event = $this->currentState->getEvent($eventName);
        $nextStateName = $event->getNextState();
        $this->transition($nextStateName);
        $event->invokeAction($this);

        return $this;
    }

    /**
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return $this->hasState($offset);
    }

    /**
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->getState($offset);
    }

    /**
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->addState($value);
    }

    /**
     * @see \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        $this->removeState($offset);
    }
}
