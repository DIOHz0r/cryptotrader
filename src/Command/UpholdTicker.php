<?php
/**
 * cryptotrader
 * Copyright (C) 2018 Domingo Oropeza
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Command;


use App\HttpClient\HttpClient;
use App\Uphold\UpholdClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpholdTicker extends Command
{
    protected $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('uphold:ticker')
            // the short description shown while running "php bin/console list"
            ->setDescription('List Uphold ticker.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Returns the current rates Uphold has on record for all currency pairs.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $urlBase = UpholdClient::UPHOLD_API_URL;
        $response = $this->client->get($urlBase.'/v0/ticker');
        $contents = json_decode($response->getBody()->getContents(), true);
        $table = new Table($output);
        $table->setHeaders(['ask', 'bid', 'currency', 'pair'])->setRows($contents);
        $table->render();
    }
}