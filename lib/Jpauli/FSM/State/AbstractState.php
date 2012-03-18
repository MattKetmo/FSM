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

namespace Jpauli\FSM\State;

use Jpauli\FSM\Exception\EventException;
use Jpauli\FSM\Event\Event;

/**
 * Abstract state which contains Events
 *
 * @copyright  2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2012 PAULI Julien <jpauli@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 */
abstract class AbstractState implements IState, \Countable, \IteratorAggregate
{
    /**
     * Name of the state
     *
     * @var string
     */
    protected $name;

    /**
     * Array of Events
     *
     * @var array
     */
    protected $events = array();

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @see \Countable::count()
     */
    public function count()
    {
        return count($this->events);
    }

    /**
     * @see \IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->events);
    }

    /**
     * Finds and returns the event with the given name.
     *
     * @param string $event
     * @return \Jpauli\FSM\Event\Event
     * @throws \Jpauli\FSM\Exception\EventException
     */
    public function getEvent($eventName)
    {
        if (!$this->hasEvent($eventName)) {
            throw new EventException("Event $eventName does not exist in $this");
        }
        return $this->events[$eventName];
    }

    /**
     * Adds an Event object to this state
     *
     * @param \Jpauli\FSM\Event\Event $event
     * @return \Jpauli\FSM\State\IState
     */
    public function addEvent(Event $event)
    {
        $this->events[$event->getName()] = $event;

        return $this;
    }

    /**
     * Gets the name of the state.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Removes an event
     *
     * @param string $eventName
     * @return \Jpauli\FSM\State\IState
     * @throws \Jpauli\FSM\Exception\EventException
     */
    public function removeEvent($eventName)
    {
        if (!$this->hasEvent($eventName)) {
            throw new EventException("Event $eventName does not exist in $this");
        }

        unset($this->events[$eventName]);

        return $this;
    }

    /**
     * Returns whether the state has an event with a given name.
     *
     * @param string $name
     * @return boolean
     */
    public function hasEvent($name)
    {
        return array_key_exists($name, $this->events);
    }

    /**
     * @see Jpauli\FSM\State\IState::__toString()
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
