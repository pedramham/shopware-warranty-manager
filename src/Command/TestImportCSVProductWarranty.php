<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestImportCSVProductWarranty extends Command
{
    private ImportWarrantyProduct $importWarrantyProduct;

    protected static $defaultName = 'warrantyManager:importCsv';

    public function __construct(ImportWarrantyProduct $importWarrantyProduct, )
    {
        parent::__construct();
        $this->importWarrantyProduct = $importWarrantyProduct;
    }

    // Provides a description, printed out in bin/console
    protected function configure(): void
    {
        $this->setDescription('importer product customer Warranty');
    }

    // Actual code executed in the command
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importWarrantyProduct->importWarranty();
        $output->writeln('works');

        return 0;
    }
}
