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

use Jpauli\FSM\Event\Event;

/**
 * The State interface
 *
 * @copyright  2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2012 PAULI Julien <jpauli@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 */
interface IState
{
    /* Constants for pseudo states */
    /**
     * @var string
     */
    const STATE_INITIAL = 'STATE_INITIAL';

    /**
     * @var string
     */
    const STATE_FINAL   = 'STATE_FINAL';

    /**
     * @var string
     */
    const STATE_NORMAL  = 'STATE_NORMAL';

    /**
     * Finds and returns the event with the given name.
     *
     * @param string $event
     * @return \Jpauli\FSM\Event\Event
     */
    public function getEvent($event);

    /**
     * Adds the event with the given name.
     *
     * @param \Jpauli\FSM\Event\Event $event
     * @return \Jpauli\FSM\State\IState 
     */
    public function addEvent(Event $event);

    /**
     * Gets the name of the state.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns whether the state has an event with a given name.
     *
     * @param string $name
     * @return boolean
     */
    public function hasEvent($name);

    /**
     * Return the type of the State
     * Enum chosen via self:: constants
     * 
     * @return string
     */
    public function getType();

    /**
     * Basically should return the State name
     * 
     * @return string
     */
    public function __toString();
}
