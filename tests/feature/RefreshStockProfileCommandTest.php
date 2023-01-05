<?php

namespace App\Tests\feature;

use App\Entity\Stock;
use App\Tests\DatabasePrimer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshStockProfileCommandTest extends KernelTestCase
{
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

    public function testRefreshStockProfileCommandBehavesCorrectlyWhenAStockRecordDoesNotExist()
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $repo = $this->entityManager->getRepository(Stock::class);
        $stock = $repo->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('Amazon Inc' , $stock->getShortName());
        $this->assertSame('USD' , $stock->getCurrency());
        $this->assertSame('Nasdaq' , $stock->getExchangeName());
        $this->assertSame('US' , $stock->getRegion());
        $this->assertGreaterThan('50' , $stock->getPrice());
        $this->assertGreaterThan('50' , $stock->getPreviousClose());

    }
}