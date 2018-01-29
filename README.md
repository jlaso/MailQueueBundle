[![Latest Stable Version](https://poser.pugx.org/jlaso/MailQueueBundle/v/stable.svg)](https://packagist.org/packages/jlaso/mail-queue-bundle) 
[![Total Downloads](https://poser.pugx.org/jlaso/MailQueueBundle/downloads.svg)](https://packagist.org/packages/jlaso/mail-queue-bundle) 
[![Latest Unstable Version](https://poser.pugx.org/jlaso/MailQueueBundle/v/unstable.svg)](https://packagist.org/packages/jlaso/mail-queue-bundle) 
[![License](https://poser.pugx.org/jlaso/MailQueueBundle/license.svg)](https://packagist.org/packages/jlaso/mail-queue-bundle)

========
Overview
========

This bundle handles a mail queue

In order to install this bundle you need to pay attention with requirements: 

    php >= 5.6
    redis >= 1.0


Installation
------------


```composer require jlaso/mail-queue-bundle```


Then register the bundle with your kernel:

    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new JLaso\MailQueueBundle\MailQueueBundle(),
        // ...
    );


Test if works
-------------

    bin/console jlaso:mail:test mail@example.com 
    
    
Setting up the cron
-------------------

```crontab -e``` to add more jobs to the cron list

```
* * * * * php /path/to/your/project/bin/console jlaso:mail-queue:process --env=prod >> /var/log/mail-queue.log
```

Using in a Controller
---------------------

```
/** @var MailQueueService $mailQueueService */
$mailQueueService = $this->get('jlaso_mail_queue_service');
$mailQueueService->queueMail(
    'sender@example.com',
    'dest@example.com,
    'This a test email',
    'The body of the email comes here'
);
```



Remember that the instructions above don't send actually the mail. We are just queueing the mail, the cron will process pending mails in the next round.