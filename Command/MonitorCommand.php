<?php


namespace KaroIO\MessengerMonitorBundle\Command;


use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

class MonitorCommand extends Command
{
    protected static $defaultName = 'messenger:monitor';

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var ReceiverLocator
     */
    private $locator;

    /**
     * @var array
     */
    private $receivers;

    public function __construct(string $name = null, ReceiverLocator $locator)
    {
        parent::__construct($name);
        $this->locator = $locator;

    }

    protected function configure()
    {
        $this->addOption('interval', 'i', InputOption::VALUE_REQUIRED, 'Interval to refresh the information', 3);
        $this->setHelp(
            'Prints information about the configured transports'.PHP_EOL.PHP_EOL.'Default refresh interval is 3 seconds.'.PHP_EOL.'Change it with -i|--interval. Use interval 0 to run once'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->receivers = $this->locator->getReceiverMapping();
        $interval = (int) $input->getOption('interval');
        $looping = ($interval > 0);

        do {
            // clear screen
            $io->write(sprintf("\033\143"));
            $io->title('Transport Queue Length');
            $io->text((new \DateTime('now'))->format('Y-m-d H:i:s'));

            $receivers = $this->locator->getReceiverMapping();
            $rows = [];
            foreach ($receivers as $name => $receiver) {
                /** @var ReceiverInterface $receiver */
                $receiver = $receivers[$name];
                $queueLength = -1;
                if ($receiver instanceof MessageCountAwareInterface) {
                    /** @var MessageCountAwareInterface $receiver */
                    $queueLength = $receiver->getMessageCount();
                }
                $rows[] = [$name, $queueLength];
            }
            $io->table(['Transport', 'Queue Length'], $rows);

            if ($looping) {
                if ($interval === 1) {
                    $io->writeln('(Refreshing every second)');
                } else {
                    $io->writeln('(Refreshing every '.$interval.' seconds)');
                }
            }

            sleep($interval);
        } while ($looping);

        return 0;
    }
}
