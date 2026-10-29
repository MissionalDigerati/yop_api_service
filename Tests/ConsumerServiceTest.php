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

use YearOfPrayer\ApiService\ConsumerService;
use YearOfPrayer\ApiService\Contracts\HttpServiceInterface;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ConsumerServiceTest extends TestCase
{

    /**
     * The ConsumerService that is being tested
     *
     * @var ConsumerService
     * @access private
     */
    private $consumerService;

    /**
     * The Guzzle HTTP Client Object
     *
     * @var Client
     */
    private $httpService;

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
     * Setup the tests
     *
     * @access public
     */
    public function setUp(): void
    {
        $this->httpService = $this->createStub(HttpServiceInterface::class);
        $this->consumerService = new ConsumerService($this->httpService);
    }

    /**
     * Replace the httpService stub with a mock so expectations can be set
     *
     * @return void
     * @access private
     */
    private function mockHttpService(): void
    {
        $this->httpService = $this->createMock(HttpServiceInterface::class);
        $this->consumerService = new ConsumerService($this->httpService);
    }

    /**
     * validate() should require a device uuid
     *
     * @return void
     * @access public
     */
    public function testValidateShouldRequireADeviceUUID(): void
    {
        $data = $this->consumerFactory;
        $data['device_uuid'] = '';
        $this->assertFalse($this->consumerService->validate($data));
    }

    /**
     * validate() should return true if the data is correct
     *
     * @return void
     * @access public
     */
    public function testValidateShouldPassIfAllRequiredAreSet(): void
    {
        $data = $this->consumerFactory;
        $this->assertTrue($this->consumerService->validate($data));
    }

    /**
     * register() should make a new consumer
     *
     * @return void
     * @access public
     */
    public function testRegisterShouldRegisterANewConsumer(): void
    {
        $this->mockHttpService();
        $data = $this->consumerFactory;
        $data['api_key'] = 'AWizardOfAKey123';
        $responseReturnData = [
            'status'    =>  'success',
            'error'     =>  [],
            'success'   =>  [
                'message'   =>  'Thank You! The consumer has been registered with our API.',
                'data'      =>  $data
            ]
        ];

        $this->httpService->expects($this->once())
                            ->method('post')
                            ->willReturn($responseReturnData);

        $consumer = $this->consumerService->register('myClientId', $this->consumerFactory);
        $this->assertEquals($data, $consumer);
    }

    /**
     * register() should throw an error if the status is not 200
     *
     * @return void
     * @access public
     */
    public function testRegisterShouldThrowErrorIfStatusIsNot200(): void
    {
        $this->mockHttpService();
        $this->expectException(\Exception::class);
        $this->httpService->expects($this->once())
                            ->method('post')
                            ->will($this->throwException(new \Exception));

        $this->consumerService->register('myClientId', $this->consumerFactory);
    }

    /**
     * update() should update the consumer data
     *
     * @return void
     * @access public
     */
    public function testUpdateShouldUpdateTheConsumer(): void
    {
        $this->mockHttpService();
        $apiKey = 'myUniqueKey123';
        $data = $this->consumerFactory;
        $data['push_at'] = '21:00:00';
        $data['api_key'] = $apiKey;
        $responseReturnData = [
            'status'    =>  'success',
            'error'     =>  [],
            'success'   =>  [
                'message'   =>  'Thank You! The consumer has been registered with our API.',
                'data'      =>  $data
            ]
        ];

        $this->httpService->expects($this->once())
                            ->method('put')
                            ->willReturn($responseReturnData);

        $success = $this->consumerService->update($apiKey, ['push_at'   =>  '21:00:00']);
        $this->assertTrue($success);
    }

    /**
     * update() should return false if the data failed to update
     *
     * @return void
     * @access public
     */
    public function testUpdateShouldReturnFalseIfNotSuccessful(): void
    {
        $this->mockHttpService();
        $apiKey = 'myNewUniqueKey789';
        $responseReturnData = [
            'status'    =>  'error',
            'success'     =>  [],
            'error'   =>  [
                'message'   =>  'Invalid API key.',
                'data'      =>  []
            ]
        ];

        $this->httpService->expects($this->once())
                            ->method('put')
                            ->willReturn($responseReturnData);

        $success = $this->consumerService->update($apiKey, ['push_at'   =>  '21:00:00']);
        $this->assertFalse($success);
    }
}
