<?php

namespace Itwmw\Validate\Ide\Helper;

use Composer\Autoload\ClassMapGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class IdeHelperCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:validate-ide')->setDescription('生成验证器提示')
            ->addArgument('file', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, '文件名', [])
            ->addOption('vendor', 'iv', InputOption::VALUE_OPTIONAL, '是否忽略Vendor目录，默认为true', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files        = $input->getArgument('file');
        $ignoreVendor = (bool)$input->getOption('vendor');

        $finder = new Finder();
        if ($ignoreVendor) {
            $finder->exclude('vendor');
        }

        $finder = $finder->files();

        if (!empty($files)) {
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if (is_dir($file)) {
                    $finder->in($file);
                } else {
                    $finder->append([$file]);
                }
            }
        }

        $finder->name('*.php');

        $classMap = array_keys(ClassMapGenerator::createMap($finder));

        if (empty($classMap)) {
            $output->writeln('<info>Success</info>');
            return 1;
        }

        foreach ($classMap as $name) {
            (new ValidateGenerate($name))->generateValidatorCommon();
            $output->writeln("- $name \033[0;32mok\033[0m");
        }

        $output->writeln('<info>Success</info>');
        return 1;
    }
}
