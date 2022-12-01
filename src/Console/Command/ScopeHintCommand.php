<?php
declare(strict_types=1);

namespace IntegerNet\CliScopeHint\Console\Command;

use IntegerNet\CliScopeHint\Service\ScopeHintService;
use Magento\Backend\Block\Widget\Tab;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ScopeHintCommand extends Command
{
    private ScopeHintService $scopeHintService;

    public function __construct(
        ScopeHintService $scopeHintService,
        string $name = null
    ) {
        parent::__construct($name);
        $this->scopeHintService = $scopeHintService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('config:scopes')
            ->setDescription('Displays the configuration values in all scopes')
            ->addArgument(
                'config_path',
                InputArgument::REQUIRED,
                'config path'
            )
            ->addOption(
                'output',
                null,
                InputOption::VALUE_OPTIONAL,
                'Options are table, json. table is default.'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scopeValues = $this->scopeHintService->getConfigValuesForScopes($input->getArgument('config_path'));

        if ($input->getOption('output') === 'json') {
            $output->writeln(json_encode($scopeValues, JSON_PRETTY_PRINT));
        } else {
            $table = new Table($output);
            $table
                ->setHeaders(['website', 'store', 'values'])
                ->setRows($scopeValues);
            $table->render();
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
