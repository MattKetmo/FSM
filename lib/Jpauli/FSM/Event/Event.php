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
namespace Jpauli\FSM\Event;

use Jpauli\FSM\Exception\NotCallableException;
use Jpauli\FSM\FSM;

/**
 * The event class mainly hold a callback invoked when this
 * event is dispatched in the FSM
 *
 * @copyright  2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2012 PAULI Julien <jpauli@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 */
class Event
{
    /**
     * @var string
     */
    const EVENT_START = 'EVENT_START';

    /**
     * Event name
     * 
     * @var string
     */
    protected $name;

    /**
     * The next state to go when this event
     * has finished dispatch
     * 
     * @var string
     */
    protected $nextState;

    /**
     * Callback action. May be null
     * 
     * @var callable
     */
    protected $action;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the next state of the event.
     *
     * @param string $state
     */
    public function setNextState($state)
    {
        $this->nextState = $state;
    }

    /**
     * Sets the action callback for this event.
     *
     * @param callable $action
     * @return \Jpauli\FSM\Event\Event
     * @throws \Jpauli\FSM\Exception\NotCallableException
     */
    public function setAction($action)
    {
        if ($action && !is_callable($action)) {
            throw new NotCallableException('The action is not callable.');
        }
        $this->action = $action;
        return $this;
    }

    /**
     * Gets the name of the event.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the next state of the event.
     *
     * @return string
     */
    public function getNextState()
    {
        return $this->nextState;
    }

    /**
     * Gets the action callback of the event.
     *
     * @return callable
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Invokes the action callback.
     *
     * @param \Jpauli\FSM\FSM $fsm
     * @return mixed
     */
    public function invokeAction(FSM $fsm)
    {
        if (is_null($this->action)) {
            return;
        }
        $payload = &$fsm->getPayload();
        return call_user_func_array($this->action, array($fsm, $this, &$payload));
    }
}
