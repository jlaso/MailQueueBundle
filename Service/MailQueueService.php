<?php

namespace JLaso\MailQueueBundle\Service;

class MailQueueService
{
    const PROCESS_FILE_NAME = '/jlaso_mail_queue.job';
    const SIGNATURE = ':mail-queue:';

    /** @var string */
    private $processFolder;
    /** @var \Redis */
    private $redis;
    /** @var string */
    private $prefix;

    /**
     * MailQueueService constructor.
     */
    public function __construct(\Redis $redis)
    {
        $this->processFolder = sys_get_temp_dir();
        $this->redis = $redis;
        $this->prefix = '';
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param $from
     * @param $to
     * @param $subject
     * @param $body
     * @return string
     */
    function queueMail($from, $to, $subject, $body)
    {
        $data = [
            'to' => $to,
            'from' => $from,
            'subject' => $subject,
            'body' => $body
        ];
        $id = uniqid();

        $this->redis->set(self::SIGNATURE . $id, serialize($data));

        return $id;
    }

    /**
     * @return array
     */
    function getQueuedMails()
    {
        $result = [];
        $mails = $this->redis->keys(self::SIGNATURE . '*');

        foreach ($mails as $mail) {
            $mail = preg_replace(sprintf("/^%s/", $this->prefix), "", $mail);
            $id = (preg_replace(sprintf("/^%s/", self::SIGNATURE), "", $mail));
            $result[$id] = unserialize($this->redis->get($mail));
        }

        return $result;
    }

    /**
     * @param $id string
     */
    function removeMail($id)
    {
        $this->redis->del(self::SIGNATURE . $id);
    }

    /**
     * @return bool
     */
    function amIWorking()
    {
        if (file_exists($this->processFolder . self::PROCESS_FILE_NAME)) {
            $pid = intval(file_get_contents($this->processFolder . self::PROCESS_FILE_NAME));
            if (0 === $pid) {
                return false;
            }
            if ($pid == getmygid()) {
                return true;
            }
            $pids = null;
            exec("ps -p {$pid}", $pids);
            return count($pids) > 1;  // first row is the header of the table
        }

        return false;
    }

    function startWorking()
    {
        file_put_contents($this->processFolder . self::PROCESS_FILE_NAME, getmygid());
    }

    function finishWorking()
    {
        file_put_contents($this->processFolder . self::PROCESS_FILE_NAME, '0');
    }
}