<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Ilmatar\TagManager;

/**
 * Helper class to manipulate emails.
 *
 */
class MailHelper extends BaseHelper
{
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = ['mailer', 'templateRepository'];

    /*
     * Builds up the message to be sent
     *
     * @param string        $subject
     * @param array         $body
     * @param array|string  $from
     * @param array|string  $to
     *
     * @return \Swift_Message|null
     */
    public function createMessage($subject = "", $body = "", $from = [], $to = [])
    {
        if (!empty($subject) && !empty($body) && !empty($from) && !empty($to)) {
            return \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(is_string($from) ? [$from] : $from)
                ->setTo(is_string($to) ? [$to] : $to)
                ->setBody($body)
                ->setContentType('text/html');
        }
        if (isset($this->options['logger']) && !is_null($this->options['logger'])) {
            $this->options['logger']->error(
                sprintf(
                    'mail "%s" cannot be generated because of invalid parameters into %s.',
                    $subject,
                    __FUNCTION__
                )
            );
        }
        return null;
    }
    /*
     * Builds up and sends a message
     *
     * @param string        $subject
     * @param array         $body
     * @param array|string  $from
     * @param array|string  $to
     *
     * @return int Number of successful recipients. If none of the recipients could be sent to then zero will be returned(which equates to a boolean false).
     */
    public function createAndSendMessage($subject = "", $body = "", $from = [], $to = [])
    {
        return $this->sendMessage(
            $this->createMessage($subject, $body, $from, $to)
        );
    }
    /*
     * Builds up a message form a template
     *
     * @param string        $templateCode
     * @param Manager       $tagManager
     * @param array|string  $from
     * @param array|string  $to
     *
     * @return \Swift_Message | null
     */
    public function createMessageFromTemplate($templateCode, TagManager $tagManager = null, $from = [], $to = [])
    {
        $template = $this->getTemplate($templateCode);
        if (!is_null($template)) {
            return $this->createMessage(
                is_null($tagManager) ? $template->getObject() : $tagManager->replaceTags($template->getObject()),
                is_null($tagManager) ? $template->getBody() : $tagManager->replaceTags($template->getBody()),
                $from,
                $to
            );
        }
        return null;
    }
    /*
     * Builds up a message and appends it into the message queue
     *
     * @param string        $subject
     * @param string        $body
     * @param string        $to
     * @param boolean       $isAllowedDuplicate
     *
     * @return \Entities\Mail | null
     */
    public function createAsynchronousMessage($subject, $body, $to, $isAllowedDuplicate = false)
    {
        if ($isAllowedDuplicate || !$this->options['orm.em']->getRepository('\\Entities\\Mail')->isMailExist($subject, $to)) {
            $mail = new \Entities\Mail([
                    'object'    => $subject,
                    'body'      => $body,
                    'recipient' => $to
            ]);
            $this->options['orm.em']->persist($mail);
            $this->options['orm.em']->flush();
            return $mail;
        }
        if (isset($this->options['logger']) && !is_null($this->options['logger'])) {
            $this->options['logger']->info(
                sprintf(
                    'mail "%s" to %s not sent because doublon into %s.',
                    $subject,
                    $to,
                    __FUNCTION__
                )
            );
        }
        return null;
    }
    /*
     * Builds up a message from a template and appends it into the message queue
     *
     * @param string        $templateCode
     * @param string        $to     
     * @param Manager       $tagManager
     * @param boolean       $isAllowedDuplicate
     *
     * @return \Entities\Mail | null
     */
    public function createAsynchronousMessageFromTemplate($templateCode, $to, TagManager $tagManager = null, $isAllowedDuplicate = false)
    {
        $template = $this->getTemplate($templateCode);
        if (!is_null($template)) {
            return $this->createAsynchronousMessage(
                is_null($tagManager) ? $template->getObject() : $tagManager->replaceTags($template->getObject()),
                is_null($tagManager) ? $template->getBody() : $tagManager->replaceTags($template->getBody()),
                $to,
                $isAllowedDuplicate
            );
        }
        return null;
    }
    /*
     * Builds up and sends a message from a template
     *
     * @param string        $templateCode
     * @param Manager       $tagManager
     * @param array|string  $from
     * @param array|string  $to
     *
     * @return int Number of successful recipients. If none of the recipients could be sent to then zero will be returned(which equates to a boolean false).
     */
    public function createAndSendMessageFromTemplate($templateCode, TagManager $tagManager, $from = [], $to = [])
    {
        return $this->sendMessage(
            $this->createMessageFromTemplate(
                $templateCode,
                $tagManager,
                $from,
                $to
            )
        );
    }

    protected function sendMessage($message)
    {
        $return = 0;
        if ($message instanceof \Swift_Message) {
            $return = $this->mandatories['mailer']->send($message);
            if (!is_null($this->options['logger'])) {
                $this->options['logger']->info(
                    sprintf(
                        'mail "%s" sent to (%s) into %s.',
                        $message->getSubject(),
                        implode(',', $message->getTo()),
                        __FUNCTION__
                    )
                );
            }
        }
        return $return;
    }
    
    protected function getTemplate($templateCode)
    {
        $template = $this->mandatories['templateRepository']->findOneByCode($templateCode);
        if (is_null($template)) {
            throw new \Exception(sprintf('Mail template code %s unknown into %s', $templateCode, __FUNCTION__));
        }
        if (!$template->getIsActive() && isset($this->options['logger']) && !is_null($this->options['logger'])) {
            $this->options['logger']->info(
                sprintf(
                    '%s template is inactive so not sent to (%s) into %s.',
                    $templateCode,
                    implode(',', is_string($to) ? [$to] : $to),
                    __FUNCTION__
                )
            );
            return null;
        }
        return $template;
    }
}
