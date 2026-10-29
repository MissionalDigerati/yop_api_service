<?php
/**
 * This file is part of Year of Prayer Service.
 *
 * Year of Prayer Service is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Year of Prayer Service is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Missional Digerati <info@missionaldigerati.org>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */
namespace YearOfPrayer\ApiService\Tests;

use YearOfPrayer\ApiService\ApiService;
use YearOfPrayer\ApiService\ConsumerService;
use YearOfPrayer\ApiService\PrayerService;
use PHPUnit\Framework\TestCase;

class ApiServiceTest extends TestCase
{
    /**
     * The ApiService being tested
     *
     * @var ApiService
     */
    private $apiService;

    /**
     * The ConsumerService Mock
     *
     * @var ConsumerService
     */
    private $consumerService;

    /**
     * The PrayerService Mock
     *
     * @var PrayerService
     */
    private $prayerService;

    /**
     * A factory of the base details for a consumer
     *
     * @var array
     * @access private
     */
    private $consumerFactory = [
        'client_id'         =>  '909ae8a3-88b7-4e8e-920c-291caefa72c5',
        'device_model'      =>  'Web Browser',
        'device_platform'   =>  'NA',
        'device_version'    =>  'NA',
        'device_uuid'       =>  '920a5209-1648-4ccc-9782-269a6cfb1d59',
        'push_token'        =>  '78d71cd1-6c07-4a4d-a926-e49d17a0de39',
        'push_at'           =>  '10:00:00',
        'push_lang'         =>  'eng',
        'time_zone'         =>  '',
        'receive_push'      =>  0
    ];

    /**
     * Setup the testing
     *
     * @access public
     */
    public function setUp(): void
    {
        $this->consumerService = $this->createStub(ConsumerService::class);
        $this->prayerService = $this->createStub(PrayerService::class);
        $this->apiService = new ApiService($this->consumerService, $this->prayerService);
    }

    /**
     * Replace the consumerService stub with a mock so expectations can be set
     *
     * @return void
     * @access private
     */
    private function mockConsumerService(): void
    {
        $this->consumerService = $this->createMock(ConsumerService::class);
        $this->apiService = new ApiService($this->consumerService, $this->prayerService);
    }

    /**
     * Replace the prayerService stub with a mock so expectations can be set
     *
     * @return void
     * @access private
     */
    private function mockPrayerService(): void
    {
        $this->prayerService = $this->createMock(PrayerService::class);
        $this->apiService = new ApiService($this->consumerService, $this->prayerService);
    }

    /**
     * registerConsumer() should actually register them
     *
     * @return void
     * @access public
     */
    public function testRegisterConsumerShouldRegisterANewConsumer(): void
    {
        $this->mockConsumerService();
        $data = $this->consumerFactory;
        $expectedApiKey = 'frogger123@';
        $data['api_key'] = $expectedApiKey;

        $this->consumerService->expects($this->once())
                                ->method('validate')
                                ->with($this->consumerFactory)
                                ->willReturn(true);

        $this->consumerService->expects($this->once())
                                ->method('register')
                                ->with('client-546', $this->consumerFactory)
                                ->willReturn($data);


        $apiKey = $this->apiService->registerConsumer('client-546', $this->consumerFactory);

        $this->assertEquals($expectedApiKey, $apiKey);
    }

    /**
     * registerConsumer should throw an error if the data is invalid
     *
     * @return void
     * @access public
     */
    public function testRegisterConsumerShouldThrowAnErrorIfTheDataIsInvalid(): void
    {
        $this->mockConsumerService();
        $this->expectException(\InvalidArgumentException::class);
        $this->consumerService->expects($this->once())
                                ->method('validate')
                                ->with($this->consumerFactory)
                                ->willReturn(false);

        $this->apiService->registerConsumer('test-client-54', $this->consumerFactory);
    }

    /**
     * updateConsumer should update the consumer data
     *
     * @return void
     */
    public function testUpdateConsumerShouldUpdateTheConsumerData(): void
    {
        $this->mockConsumerService();
        $apiKey = 'Y654$3#rre';
        $this->consumerService->expects($this->once())
                                ->method('update')
                                ->with($apiKey, ['push_at'  =>  '12:00:00'])
                                ->willReturn(true);

        $success = $this->apiService->updateConsumer($apiKey, ['push_at'   =>  '12:00:00']);
        $this->assertTrue($success);
    }

    /**
     * updateConsumer should throw an error if the API key is empty
     *
     * @return void
     * @access public
     */
    public function testUpdateConsumerShouldThrowAnErrorIfAPIKeyIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->apiService->updateConsumer('', ['device_model'   =>  'iOS']);
    }

    /**
     * updateConsumer should throw an error if you send no data to update
     *
     * @return void
     * @access public
     */
    public function testUpdateConsumerShouldThrowAnErrorIfEmptyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->apiService->updateConsumer('myApiKey23', []);
    }

    /**
     * updateConsumer should throw an error if you send an api_key in the data
     *
     * @return void
     * @access public
     */
    public function testUpdateConsumerShouldThrowAnErrorIfInvalidData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->apiService->updateConsumer('myApiKey23', ['api_key'  =>  'nyNewApiKey']);
    }


    /**
     * praying() should allow a Consumer to indicate praying
     *
     * @return void
     * @access public
     */
    public function testPrayingShouldIndicateAPrayer(): void
    {
        $this->mockPrayerService();
        $prayerId = '01-11';
        $apiKey = 'myApiKey23';
        $prayingData = ['id'    =>  $prayerId];

        $this->prayerService->expects($this->once())
                                ->method('validate')
                                ->with($prayingData)
                                ->willReturn(true);

        $this->prayerService->expects($this->once())
                                ->method('praying')
                                ->with($apiKey, $prayingData)
                                ->willReturn(true);

        $this->assertTrue($this->apiService->praying($apiKey, $prayerId));
    }

    /**
     * prayerStats() should return the correct stats
     *
     * @return void
     * @access public
     */
    public function testPrayerStatsAsConsumerShouldReturnValidStats(): void
    {
        $this->mockPrayerService();
        $prayerId = '02-29';
        $apiKey = 'FunnyKeySee';
        $prayingData = ['id'    =>  $prayerId];
        $expected = [
            'prayer_request_id'     =>  '02-29',
            'total_prayers'         =>  10,
            'your_prayers'          =>  1,
            'your_last_prayer_on'   => '2016-01-12 11:12:00'
        ];

        $this->prayerService->expects($this->once())
                                ->method('validate')
                                ->with($prayingData)
                                ->willReturn(true);

        $this->prayerService->expects($this->once())
                                ->method('prayerStats')
                                ->with($apiKey, 'consumer', $prayingData)
                                ->willReturn($expected);

        $actual = $this->apiService->prayerStats($apiKey, 'consumer', '02-29');
        $this->assertEquals($expected, $actual);
    }

    /**
     * praying() should throw an error if the apiKey is empty
     *
     * @return void
     *
     * @access public
     */
    public function testPrayingShouldThrowErrorIfApiKeyIsEmpty(): void
    {
        $this->mockPrayerService();
        $this->expectException(\InvalidArgumentException::class);
        $this->prayerService->expects($this->never())
                                ->method('validate');
        $this->apiService->praying('', '12-02');
    }

    /**
     * praying() should throw an error if the prayerId is empty
     *
     * @return void
     *
     * @access public
     */
    public function testPrayingShouldThrowErrorIfPrayerIdIsEmpty(): void
    {
        $this->mockPrayerService();
        $this->expectException(\InvalidArgumentException::class);
        $this->prayerService->expects($this->once())
                                ->method('validate')
                                ->with(['id'    =>  ''])
                                ->willReturn(false);
        $this->apiService->praying('myApiKey', '');
    }

    /**
     * praying() should throw an error if the prayerId is malformed
     *
     * @return void
     *
     * @access public
     */
    public function testPrayingShouldThrowErrorIfPrayerIdIsMalformed(): void
    {
        $this->mockPrayerService();
        $this->expectException(\InvalidArgumentException::class);
        $this->prayerService->expects($this->once())
                                ->method('validate')
                                ->with(['id'    =>  '2103-2'])
                                ->willReturn(false);
        $this->apiService->praying('myApiKey', '2103-2');
    }

    /**
     * prayerStats() should throw an error if the apiKey is empty
     *
     * @return void
     *
     * @access public
     */
    public function testPrayerStatsShouldThrowErrorIfApiKeyIsEmpty(): void
    {
        $this->mockPrayerService();
        $this->expectException(\InvalidArgumentException::class);
        $this->prayerService->expects($this->never())
                                ->method('validate');
        $this->apiService->prayerStats('', 'client', '12-02');
    }

    /**
     * prayerStats() should throw an error if the keyType is empty
     *
     * @return void
     *
     * @access public
     */
    public function testPrayerStatsShouldThrowErrorIfKeyTypeIsEmpty(): void
    {
        $this->mockPrayerService();
        $this->expectException(\InvalidArgumentException::class);
        $this->prayerService->expects($this->never())
                                ->method('validate');
        $this->apiService->prayerStats('myspecialKEY', '', '12-02');
    }

    /**
     * prayerStats() should throw an error if the keyType is empty
     *
     * @return void
     *
     * @access public
     */
    public function testPrayerStatsShouldThrowErrorIfKeyTypeIsWrong(): void
    {
        $this->mockPrayerService();
        $this->expectException(\InvalidArgumentException::class);
        $this->prayerService->expects($this->never())
                                ->method('validate');
        $this->apiService->prayerStats('myspecialKEY', 'guest', '12-02');
    }

    /**
     * prayerStats() should throw an error if the prayerId is empty
     *
     * @return void
     *
     * @access public
     */
    public function testPrayerStatsShouldThrowErrorIfPrayerIdIsEmpty(): void
    {
        $this->mockPrayerService();
        $this->expectException(\InvalidArgumentException::class);
        $this->prayerService->expects($this->once())
                                ->method('validate')
                                ->with(['id'    =>  ''])
                                ->willReturn(false);
        $this->apiService->prayerStats('myApiKey', 'consumer', '');
    }

    /**
     * prayerStats() should throw an error if the prayerId is empty
     *
     * @return void
     *
     * @access public
     */
    public function testPrayerStatsShouldThrowErrorIfPrayerIdIsMalformed(): void
    {
        $this->mockPrayerService();
        $this->expectException(\InvalidArgumentException::class);
        $this->prayerService->expects($this->once())
                                ->method('validate')
                                ->with(['id'    =>  '03-243'])
                                ->willReturn(false);
        $this->apiService->prayerStats('myApiKey', 'consumer', '03-243');
    }
}
