<?php
declare(strict_types=1);

namespace IntegerNet\CliScopeHint\Console\Command;

use IntegerNet\CliScopeHint\Service\ScopeHintService;
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
                InputArgument::OPTIONAL,
                'config path'
            )
            ->addOption(
                'output',
                null,
                InputOption::VALUE_OPTIONAL,
                'Options are table, json. table is default.'
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Output all config scope paths'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $all_configs = $this->scopeHintService->getAllScopes();

        // get all configurations
        if($input->getOption('all')) {

             $scopeValues = array();
             foreach($all_configs as $element) {
                 $result_values = $this->scopeHintService->getConfigValuesForScopes($element);
                 foreach($result_values as $element2) {
                     $scopeValues[] = $element2;
                 }
             }
        }
        else {

            $input_arg = $input->getArgument('config_path');

            // if path exists then values for config path are shown
            if(in_array($input_arg, $all_configs)) {
                $scopeValues = $this->scopeHintService->getConfigValuesForScopes($input->getArgument('config_path'));
            }
            else {
                // if config path has sub paths those are determined
                // get all config paths matching to the search string
                foreach($all_configs as $element) {
                    if(str_contains($element, $input_arg))
                    {
                        $config = $this->scopeHintService->getConfigValuesForScopes($element);
                        foreach($config as $config_element)
                        {
                            $scopeValues[] = $config_element;
                        }
                    }
                }
            }
        }

        if ($input->getOption('output') === 'json') {
            $output->writeln(json_encode($scopeValues, JSON_PRETTY_PRINT));
        } else {
            $table = new Table($output);
            $table
                ->setHeaders(['scope', 'scope_id', 'path', 'values'])
                ->setRows($scopeValues)
                ->setColumnMaxWidth(2, 60)
                ->setColumnMaxWidth(3, 100);
            $table->render();
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
