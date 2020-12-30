<?php

namespace App\Traits;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait RenderAdsTable
{

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $dataRows
     * @param $options
     * @return int
     */
    public function renderAdsTable(InputInterface $input, OutputInterface $output, array $dataRows, $options): int
    {
        $format = $input->getOption('json');
        if ($format) {
            $output->write(json_encode($dataRows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return 0;
        }

        $table = new Table($output);
        $headers = ['payment', 'price', 'min', 'max'];
        if ($options) {
            $headers[] = 'user';
        }
        $table->setHeaders($headers)->setRows($dataRows);
        $table->render();

        return 0;
    }
}