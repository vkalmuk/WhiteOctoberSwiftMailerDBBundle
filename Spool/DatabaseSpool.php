<?php

namespace WhiteOctober\SwiftMailerDBBundle\Spool;

use AbstractEmail;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use WhiteOctober\SwiftMailerDBBundle\EmailInterface;

/**
 * Class DatabaseSpool
 * @package WhiteOctober\SwiftMailerDBBundle\Spool
 */
class DatabaseSpool extends \Swift_ConfigurableSpool
{
    /**
     * @var ManagerRegistry
     */
    protected $doc;

    /**
     * @var AbstractEmail
     */
    protected $entityClass;

    /**
     * @var boolean
     */
    protected $keepSentMessages;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(ManagerRegistry $doc, $entityClass, $environment, $keepSentMessages = false)
    {
        $this->doc = $doc;
        $this->keepSentMessages = $keepSentMessages;

        /** @var AbstractEmail $obj */
        $obj = new $entityClass;
        if (!$obj instanceof EmailInterface) {
            throw new \InvalidArgumentException("The entity class '{$entityClass}'' does not extend from EmailInterface");
        }

        $this->entityClass = $entityClass;
        $this->environment = $environment;
        $this->em = $this->getManager();
    }

    /**
     * Starts this Spool mechanism.
     */
    public function start()
    {
    }

    /**
     * Stops this Spool mechanism.
     */
    public function stop()
    {
    }

    /**
     * Tests if this Spool mechanism has started.
     *
     * @return boolean
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Queues a message.
     *
     * @param \Swift_Mime_SimpleMessage $message The message to store
     * @return boolean Whether the operation has succeeded
     */
    public function queueMessage(\Swift_Mime_SimpleMessage $message)
    {
        /** @var AbstractEmail $mailObject */
        $mailObject = new $this->entityClass;
        $mailObject->setMessage(serialize($message));
        $mailObject->setStatus(EmailInterface::STATUS_READY);
        $mailObject->setEnvironment($this->environment);
        $this->em->persist($mailObject);
        $this->em->flush();

        return true;
    }

    /**
     * Sends messages using the given transport instance.
     *
     * @param \Swift_Transport $transport A transport instance
     * @param string[]        &$failedRecipients An array of failures by-reference
     *
     * @return int The number of sent emails
     */
    public function flushQueue(\Swift_Transport $transport, &$failedRecipients = null)
    {
        $em = $this->em;
        $repoClass = $this->em->getRepository($this->entityClass);
        $limit = $this->getMessageLimit();
        $limit = $limit > 0 ? $limit : null;

        /** @var AbstractEmail[] $emails */
        $emails = $repoClass->findBy(
            array('status' => EmailInterface::STATUS_READY, 'environment' => $this->environment),
            null,
            $limit
        );
        if (!\count($emails)) {
            return 0;
        }

        $failedRecipients = (array)$failedRecipients;
        $count = 0;
        $time = time();
        foreach ($emails as $email) {
            $email->setStatus(EmailInterface::STATUS_PROCESSING);
            $em->persist($email);
            $em->flush();

            $message = \unserialize($email->getMessage(), null);

            if (!$transport->isStarted()) {
                $transport->start();
            }

            $count += $transport->send($message, $failedRecipients);
            if ($this->keepSentMessages === true) {
                $email->setStatus(EmailInterface::STATUS_COMPLETE);
                $em->persist($email);
            } else {
                $em->remove($email);
            }
            $em->flush();

            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit()) {
                break;
            }
        }

        return $count;
    }

    protected function getManager(): EntityManagerInterface
    {
        return $this->doc->getManagerForClass($this->entityClass);
    }
}


namespace WhiteOctober\SwiftMailerDBBundle\Spool;

use AbstractEmail;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use WhiteOctober\SwiftMailerDBBundle\EmailInterface;

/**
 * Class DatabaseSpool
 * @package WhiteOctober\SwiftMailerDBBundle\Spool
 */
class DatabaseSpool extends \Swift_ConfigurableSpool
{
    /**
     * @var ManagerRegistry
     */
    protected $doc;

    /**
     * @var AbstractEmail
     */
    protected $entityClass;

    /**
     * @var boolean
     */
    protected $keepSentMessages;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(ManagerRegistry $doc, $entityClass, $environment, $keepSentMessages = false)
    {
        $this->doc = $doc;
        $this->keepSentMessages = $keepSentMessages;

        /** @var AbstractEmail $obj */
        $obj = new $entityClass;
        if (!$obj instanceof EmailInterface) {
            throw new \InvalidArgumentException("The entity class '{$entityClass}'' does not extend from EmailInterface");
        }

        $this->entityClass = $entityClass;
        $this->environment = $environment;
        $this->em = $this->getManager();
    }

    /**
     * Starts this Spool mechanism.
     */
    public function start()
    {
    }

    /**
     * Stops this Spool mechanism.
     */
    public function stop()
    {
    }

    /**
     * Tests if this Spool mechanism has started.
     *
     * @return boolean
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Queues a message.
     *
     * @param \Swift_Mime_SimpleMessage $message The message to store
     * @return boolean Whether the operation has succeeded
     */
    public function queueMessage(\Swift_Mime_SimpleMessage $message)
    {
        /** @var AbstractEmail $mailObject */
        $mailObject = new $this->entityClass;
        $mailObject->setMessage(serialize($message));
        $mailObject->setStatus(EmailInterface::STATUS_READY);
        $mailObject->setEnvironment($this->environment);
        $this->em->persist($mailObject);
        $this->em->flush();

        return true;
    }

    /**
     * Sends messages using the given transport instance.
     *
     * @param \Swift_Transport $transport A transport instance
     * @param string[]        &$failedRecipients An array of failures by-reference
     *
     * @return int The number of sent emails
     */
    public function flushQueue(\Swift_Transport $transport, &$failedRecipients = null)
    {
        $em = $this->em;
        $repoClass = $this->em->getRepository($this->entityClass);
        $limit = $this->getMessageLimit();
        $limit = $limit > 0 ? $limit : null;

        /** @var AbstractEmail[] $emails */
        $emails = $repoClass->findBy(
            array('status' => EmailInterface::STATUS_READY, 'environment' => $this->environment),
            null,
            $limit
        );
        if (!\count($emails)) {
            return 0;
        }

        $failedRecipients = (array)$failedRecipients;
        $count = 0;
        $time = time();
        foreach ($emails as $email) {
            $email->setStatus(EmailInterface::STATUS_PROCESSING);
            $em->persist($email);
            $em->flush();

            $message = \unserialize($email->getMessage(), null);

            if (!$transport->isStarted()) {
                $transport->start();
            }

            $count += $transport->send($message, $failedRecipients);
            if ($this->keepSentMessages === true) {
                $email->setStatus(EmailInterface::STATUS_COMPLETE);
                $em->persist($email);
            } else {
                $em->remove($email);
            }
            $em->flush();

            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit()) {
                break;
            }
        }

        return $count;
    }

    protected function getManager(): EntityManagerInterface
    {
        return $this->doc->getManagerForClass($this->entityClass);
    }
}