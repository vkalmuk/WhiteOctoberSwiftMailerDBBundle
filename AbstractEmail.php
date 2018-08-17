<?php

use WhiteOctober\SwiftMailerDBBundle\EmailInterface;

/**
 *
 * @Author: jwamser
 * @CreateAt: 5/30/18 12:36 PM
 * Project: WhiteOctoberSwiftMailerDBBundle
 * File Name: AbstractEmail.php
 */

abstract class AbstractEmail implements EmailInterface
{

    /**
     * @return string
     */
    abstract public function getMessage();

    /**
     * @return string
     */
    abstract public function getStatus();

    /**
     * @return string
     */
    abstract public function getEnvironment();

    /**
     * @param $message string Serialized \Swift_Mime_SimpleMessage
     */
    abstract public function setMessage($message);

    /**
     * @param $status string
     */
    abstract public function setStatus($status);

    /**
     * @param $environment string
     */
    abstract public function setEnvironment($environment);
}