<?php

use SRAG\Hub2\Exception\AbortOriginSyncException;
use SRAG\Hub2\Exception\ConnectionFailedException;
use SRAG\Hub2\Exception\ParseDataFailedException;
use SRAG\Hub2\Object\IObject;
use SRAG\Hub2\Object\UserDTO;

require_once(dirname(__DIR__) . '/AbstractHub2Tests.php');

/**
 * Class OriginSyncTest
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @backupGlobals  disabled
 * @backupStaticAttributes disabled
 */
class OriginSyncTest extends AbstractHub2Tests {

	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

	/**
	 * @var Mockery\MockInterface
	 */
	protected $originImplementation;

	/**
	 * @var Mockery\MockInterface
	 */
	protected $origin;

	/**
	 * @var Mockery\MockInterface
	 */
	protected $repository;

	/**
	 * @var Mockery\MockInterface
	 */
	protected $factory;

	/**
	 * @var Mockery\MockInterface
	 */
	protected $processor;

	/**
	 * @var Mockery\MockInterface
	 */
	protected $statusTransition;

	/**
	 * @var Mockery\MockInterface
	 */
	protected $originConfig;


	protected function setUp() {
		$this->originImplementation = \Mockery::mock('\SRAG\Hub2\Origin\IOriginImplementation');
		$this->originImplementation->shouldReceive('beforeSync')->once();
		$this->origin = \Mockery::mock("SRAG\Hub2\Origin\IOrigin");
		$this->origin->shouldReceive('implementation')->andReturn($this->originImplementation);
		$this->repository = \Mockery::mock('\SRAG\Hub2\Object\IObjectRepository');
		$this->factory = \Mockery::mock('\SRAG\Hub2\Object\IObjectFactory');
		$this->processor = \Mockery::mock('\SRAG\Hub2\Sync\Processor\IObjectSyncProcessor');
		$this->originConfig = \Mockery::mock("SRAG\Hub2\Origin\Config\IOriginConfig");
		$this->statusTransition = \Mockery::mock('SRAG\Hub2\Sync\IObjectStatusTransition');
	}

	public function test_fail_connect() {
		$this->originImplementation->shouldReceive('connect')
			->andThrow(ConnectionFailedException::class, 'Unable to connect');
		$originSync = new \SRAG\Hub2\Sync\OriginSync(
			$this->origin,
			$this->repository,
			$this->factory,
			$this->processor,
			$this->statusTransition
		);
		$this->expectException(ConnectionFailedException::class);
		$originSync->execute();
		$this->assertInstanceOf(ConnectionFailedException::class, array_pop($originSync->getExceptions()));
	}

	public function test_fail_parse_data() {
		$this->originImplementation->shouldReceive('connect')->once();
		$this->originImplementation->shouldReceive('parseData')->andThrow(ParseDataFailedException::class);
		$originSync = new \SRAG\Hub2\Sync\OriginSync(
			$this->origin,
			$this->repository,
			$this->factory,
			$this->processor,
			$this->statusTransition
		);
		$this->expectException(ParseDataFailedException::class);
		$originSync->execute();
		$this->assertInstanceOf(ParseDataFailedException::class, array_pop($originSync->getExceptions()));
	}

	public function test_that_origin_sync_gets_aborted_if_not_enough_data_delivered() {
		$this->originImplementation->shouldReceive('connect');
		// 100 data sets delivered
		$this->originImplementation->shouldReceive('parseData')->andReturn(100);
		$this->origin->shouldReceive('config')->andReturn($this->originConfig);
		$this->originConfig->shouldReceive('getCheckAmountData')->andReturn(true);
		// Need at least 50% data sets to be delivered of existing data
		$this->originConfig->shouldReceive('getCheckAmountDataPercentage')->andReturn(50);
		// 1000 data sets exists -> 10% is delivered
		$this->repository->shouldReceive('count')->andReturn(1000);
		$originSync = new \SRAG\Hub2\Sync\OriginSync(
			$this->origin,
			$this->repository,
			$this->factory,
			$this->processor,
			$this->statusTransition
		);
		$this->expectException(AbortOriginSyncException::class);
		$originSync->execute();
		$this->assertInstanceOf(AbortOriginSyncException::class, array_pop($originSync->getExceptions()));
	}

	public function test_that_origin_sync_does_not_abort_if_enough_data_delivered() {
		$this->originImplementation->shouldReceive('connect');
		// 100 data sets delivered
		$this->originImplementation->shouldReceive('parseData')->andReturn(100);
		$this->origin->shouldReceive('config')->andReturn($this->originConfig);
		$this->originConfig->shouldReceive('getCheckAmountData')->andReturn(true);
		// Need at least 10% data sets to be delivered of existing data
		$this->originConfig->shouldReceive('getCheckAmountDataPercentage')->andReturn(10);
		// 1000 data sets exists -> 10% is delivered, which is just enough
		$this->repository->shouldReceive('count')->andReturn(1000);
		$this->originImplementation->shouldReceive('buildObjects')->andReturn([]);
		$this->origin->shouldReceive('getObjectType');
		$this->repository->shouldReceive('getToDelete')->andReturn([]);
		$this->originImplementation->shouldReceive('afterSync');
		$originSync = new \SRAG\Hub2\Sync\OriginSync(
			$this->origin,
			$this->repository,
			$this->factory,
			$this->processor,
			$this->statusTransition
		);
		$originSync->execute();
		$this->assertEquals(100, $originSync->getCountDelivered());
		$this->assertEquals(0, $originSync->getCountProcessedTotal());
		$this->assertEquals([], $originSync->getExceptions());
	}

	public function test_processing() {
		$this->originImplementation->shouldReceive('connect');
		$this->originImplementation->shouldReceive('parseData')->andReturn(4);
		$this->origin->shouldReceive('config')->andReturn($this->originConfig);
		$this->originConfig->shouldReceive('getCheckAmountData')->andReturn(false);
		$this->repository->shouldReceive('count');
		$this->origin->shouldReceive('getObjectType')->andReturn('dummy');
		// Build 4 dummyDTOs returned by the origin implementation
		$dummyDTOs = [];
		for ($i = 1; $i <= 4; $i++) {
			$dummyDTO = \Mockery::mock('\SRAG\Hub2\Object\IDataTransferObject');
			$dummyDTO->shouldReceive('getExtId');
			$dummyDTO->shouldReceive('getData')->andReturn([]);
			$dummyDTO->shouldReceive('setData');
			$dummyDTOs[] = $dummyDTO;
		}
		$this->originImplementation->shouldReceive('buildObjects')->andReturn($dummyDTOs);
		$status = [IObject::STATUS_CREATED, IObject::STATUS_UPDATED, IObject::STATUS_DELETED, IObject::STATUS_IGNORED];
		// Build 4 dummy objects that correspond to a dummyDTO, each having a different final status
		$objects = [];
		for ($i = 0; $i < 4; $i++) {
			$object = \Mockery::mock('\SRAG\Hub2\Object\IObject');
			$object->shouldReceive('setDeliveryDate');
			$object->shouldReceive('getData')->andReturn([]);
			$object->shouldReceive('setStatus');
			$object->shouldReceive('getStatus')->andReturn($status[$i]);
			$object->shouldReceive('save');
			$objects[] = $object;
		}
		$this->statusTransition->shouldReceive('finalToIntermediate');
		$this->factory->shouldReceive('dummy')->times(4)->andReturn($objects[0], $objects[1], $objects[2], $objects[3]);
		$this->repository->shouldReceive('getToDelete')->andReturn([]);
		$this->processor->shouldReceive('process')->times(4);
		$this->originImplementation->shouldReceive('afterSync');
		$originSync = new \SRAG\Hub2\Sync\OriginSync(
			$this->origin,
			$this->repository,
			$this->factory,
			$this->processor,
			$this->statusTransition
		);
		$originSync->execute();
		$this->assertEquals(4, $originSync->getCountDelivered());
		$this->assertEquals(4, $originSync->getCountProcessedTotal());
		$this->assertEquals(1, $originSync->getCountProcessedByStatus(IObject::STATUS_CREATED));
		$this->assertEquals(1, $originSync->getCountProcessedByStatus(IObject::STATUS_UPDATED));
		$this->assertEquals(1, $originSync->getCountProcessedByStatus(IObject::STATUS_DELETED));
		$this->assertEquals(1, $originSync->getCountProcessedByStatus(IObject::STATUS_IGNORED));
		$this->assertEquals([], $originSync->getExceptions());
	}

	public function test_that_any_exception_during_processing_is_forwarded_to_the_origin_implementation() {
		$this->originImplementation->shouldReceive('connect');
		$this->originImplementation->shouldReceive('parseData')->andReturn(1);
		$this->origin->shouldReceive('config')->andReturn($this->originConfig);
		$this->originConfig->shouldReceive('getCheckAmountData')->andReturn(false);
		$this->repository->shouldReceive('count');
		$this->origin->shouldReceive('getObjectType')->andReturn('user');
		$this->originImplementation->shouldReceive('buildObjects')->andReturn([new UserDTO(1)]);
		$this->statusTransition->shouldReceive('finalToIntermediate');
		$userMock = \Mockery::mock('\SRAG\Hub2\Object\IUser');
		$userMock->shouldReceive('setDeliveryDate');
		$userMock->shouldReceive('getData')->andReturn([]);
		$userMock->shouldReceive('setStatus');
		$this->factory->shouldReceive('user')->andReturn($userMock);
		$exception = new Exception();
		$this->processor->shouldReceive('process')->andThrow($exception);
		$this->originImplementation->shouldReceive('handleException')->with($exception);
		$userMock->shouldReceive('save');
		$this->repository->shouldReceive('getToDelete')->andReturn([]);
		$this->originImplementation->shouldReceive('afterSync');
		$originSync = new \SRAG\Hub2\Sync\OriginSync(
			$this->origin,
			$this->repository,
			$this->factory,
			$this->processor,
			$this->statusTransition
		);
		$originSync->execute();
		$this->assertEquals($exception, array_pop($originSync->getExceptions()));
		$this->assertEquals(0, $originSync->getCountProcessedTotal());
		$this->assertEquals(1, $originSync->getCountDelivered());
	}
}