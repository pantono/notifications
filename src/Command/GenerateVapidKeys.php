<?php

namespace Pantono\Notifications\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Minishlink\WebPush\VAPID;
use Symfony\Component\Console\Command\Command;

class GenerateVapidKeys extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('web-push:generate-vapid-keys');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keys = VAPID::createVapidKeys();
        $output->writeln('Add the following config variables:');
        $output->writeln('web_push.vapid.public_key: ' . $keys['publicKey']);
        $output->writeln('web_push.vapid.private_key: ' . $keys['privateKey']);
        $output->writeln('web_push.vapid.subject: mailto:your@email.com');
        return 0;
    }
}
