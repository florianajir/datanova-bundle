<?php
namespace Laposte\DatanovaBundle\Command;

use Laposte\DatanovaBundle\Service\Downloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadRecordsCommand extends Command
{
    /** @var Downloader $downloader */
    private $downloader;

    /**
     * @param Downloader $downloader
     */
    public function __construct(Downloader $downloader)
    {
        $this->downloader = $downloader;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('datanova:download:records')
            ->setDescription('Download dataset records to use it locally')
            ->addArgument(
                'dataset',
                InputArgument::REQUIRED,
                'Which dataset to download?'
            )
            ->addArgument(
                'format',
                InputArgument::OPTIONAL,
                'Data file format : CSV (default), JSON',
                'CSV'
            )
            ->addArgument(
                'q',
                InputArgument::OPTIONAL,
                'query filter, by default all results will be download'
            )
            ->addOption(
                'force-replace',
                'f',
                InputOption::VALUE_NONE,
                'If set, the command will replace local storage'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataset = $input->getArgument('dataset');
        $format = strtolower($input->getArgument('format'));
        $success = $this->downloader->download(
            $dataset,
            $format,
            $input->getArgument('q'),
            $input->getOption('force-replace')
        );
        if ($success) {
            $output->writeln(sprintf('Dataset %s downloaded to %s.', $dataset, $success));
        } else {
            if ($this->downloader->exists($dataset, $format)) {
                if (false === $input->getOption('force-replace')) {
                    $output->writeln('Existing data locally. If you want to overwrite it, try with --force-replace option');
                } else {
                    $output->writeln('Error during update of local dataset.');
                }
            } else {
                $output->writeln('Error during dataset download.');
            }
        }
    }
}