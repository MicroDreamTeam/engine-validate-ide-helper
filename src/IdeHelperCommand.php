<?php

namespace W7\Validate\Ide\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IdeHelperCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:validate-ide')->setDescription('生成验证器提示')
            ->addArgument('name', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, '控制器完整命名空间', [])
            ->addOption('dir', 'D', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, '搜索目录', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $names = $input->getArgument('name');
        foreach ($names as $name) {
            $generate = new ValidateGenerate($name);
            $generate->getValidator();
        }
        return 1;
    }
}
