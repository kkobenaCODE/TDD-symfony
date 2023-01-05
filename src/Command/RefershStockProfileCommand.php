<?php

namespace App\Command;

use App\Entity\Stock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:refresh-stock-profile',
    description: 'Retrieve a stock profile from the Yahoo Finance API.',
)]
class RefershStockProfileCommand extends Command
{
    /**
     * @var YahooFinanceApiClient
     */
    private YahooFinanceApiClient $yahooFinanceApiClient;

    public function __construct(private EntityManagerInterface $entityManager, private YahooFinanceApiClient $yahooFinanceApiClient)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock symbol e.g. AMZN for Amazon')
            ->addArgument('region', InputArgument::REQUIRED, 'The region of the company e.g. US for United States');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stockProfile = $this->yahooFinanceApiClient->fetchStockProfile($input->getArgument('symbol'),
            $input->getArgument('region'));

        $stock = new Stock();
        $priceChange = $stockProfile->price - $stockProfile->previousClose

        $stock
            ->setSymbol($stockProfile->symbol)
            ->setShortName($stockProfile->shortNmae)
            ->setCurrency($stockProfile->currency)
            ->setExchangeName($stockProfile->exchangeName)
            ->setRegion($stockProfile->region)
            ->setPrice($stockProfile->price)
            ->setPreviousClose($stockProfile->previousClose);
            ->setPriceChange($priceChange);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
