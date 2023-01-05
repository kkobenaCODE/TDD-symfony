<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Stock;

class StockTest extends KernelTestCase
{
    /**@var EntityManagerInterface*/
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        DatabasePrimer::prime($kernel);
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testStockRecordCanBeSavedInDB() : void
    {
        $price = 1000;
        $previousClose = 1100;
        $priceChange = $price - $previousClose;

        $stock = new Stock();
        $stock
            ->setSymbol('AMZN')
            ->setShortName('Amazon Inc')
            ->setCurrency('USD')
            ->setExchangeName('Nasdaq')
            ->setRegion('US')
            ->setPrice($price)
            ->setPreviousClose($previousClose)
            ->setPriceChange($priceChange);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        $stockRepository = $this->entityManager->getRepository(Stock::class);
        $stockRecord = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        $this->assertEquals('Amazon Inc' , $stockRecord->getShortName());
        $this->assertEquals('USD' , $stockRecord->getCurrency());
        $this->assertEquals('Nasdaq' , $stockRecord->getExchangeName());
        $this->assertEquals('US' , $stockRecord->getRegion());
        $this->assertEquals('1000' , $stockRecord->getPrice());
        $this->assertEquals('1100' , $stockRecord->getPreviousClose());
        $this->assertEquals('-100' , $stockRecord->getPriceChange());

    }
}