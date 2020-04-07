<?php
/**
 * This file is part of EspoCRM and/or TreoCore, and/or KennerCore.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2020 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoCore is EspoCRM-based Open Source application.
 * Copyright (C) 2017-2020 TreoLabs GmbH
 * Website: https://treolabs.com
 *
 * KennerCore is TreoCore-based Open Source application.
 * Copyright (C) 2020 KenerSoft Service GmbH
 * Website: https://kennersoft.de
 *
 * KennerCore as well as TreoCore and EspoCRM is free software:
 * you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * KennerCore as well as TreoCore and EspoCRM is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of
 * the "KennerCore", "EspoCRM" and "TreoCore" words.
 */

namespace Treo\Core\Acl;

use Espo\Core\Utils\Config;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Treo\Core\Container;
use Treo\Core\Utils\Metadata;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseTest
 *
 * @author r.zablodskiy@treolabs.com
 */
class BaseTest extends TestCase
{
    protected $entityType = 'TestType';

    /**
     * Test checkIsOwner method return true
     */
    public function testCheckIsOwnerReturnTrue()
    {
        $user = $this->createMockService(User::class);
        $user->id = 'some-id';

        // test 1
        $metadata = $this->createMockService(Metadata::class, ['get']);
        $metadata
            ->method('get')
            ->withConsecutive(
                ['scopes.' . $this->entityType . '.hasOwner']
            )->willReturnOnConsecutiveCalls(true);

        $service = $this->createMockService(Base::class, ['getInjection']);
        $service
            ->expects($this->any())
            ->method('getInjection')
            ->willReturn($metadata);

        $entity = $this->createMockService(Entity::class, ['getEntityType', 'has', 'get']);
        $entity
            ->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityType);
        $entity
            ->method('has')
            ->withConsecutive(['ownerUserId'])
            ->willReturnOnConsecutiveCalls(true);
        $entity
            ->method('get')
            ->withConsecutive(['ownerUserId'])
            ->willReturnOnConsecutiveCalls('some-id');

        $this->assertTrue($service->checkIsOwner($user, $entity));

        // test 2
        $metadata = $this->createMockService(Metadata::class, ['get']);
        $metadata
            ->method('get')
            ->withConsecutive(
                ['scopes.' . $this->entityType . '.hasOwner'],
                ['scopes.' . $this->entityType . '.hasAssignedUser']
            )->willReturnOnConsecutiveCalls(false, true);
        $service = $this->createMockService(Base::class, ['getInjection']);
        $service
            ->expects($this->any())
            ->method('getInjection')
            ->willReturn($metadata);

        $entity = $this->createMockService(Entity::class, ['getEntityType', 'has', 'get']);
        $entity
            ->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityType);
        $entity
            ->method('has')
            ->withConsecutive(['assignedUserId'])
            ->willReturnOnConsecutiveCalls(true);
        $entity
            ->method('get')
            ->withConsecutive(['assignedUserId'])
            ->willReturnOnConsecutiveCalls('some-id');

        $this->assertTrue($service->checkIsOwner($user, $entity));

        // test 3
        $metadata = $this->createMockService(Metadata::class, ['get']);
        $metadata
            ->method('get')
            ->withConsecutive(
                ['scopes.' . $this->entityType . '.hasOwner'],
                ['scopes.' . $this->entityType . '.hasAssignedUser']
            )->willReturnOnConsecutiveCalls(false, false);
        $service = $this->createMockService(Base::class, ['getInjection']);
        $service
            ->expects($this->any())
            ->method('getInjection')
            ->willReturn($metadata);

        $entity = $this->createMockService(Entity::class, ['getEntityType', 'hasAttribute', 'has', 'get']);
        $entity
            ->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityType);
        $entity
            ->method('hasAttribute')
            ->withConsecutive(['createdById'])
            ->willReturnOnConsecutiveCalls(true);
        $entity
            ->method('has')
            ->withConsecutive(['createdById'])
            ->willReturnOnConsecutiveCalls(true);
        $entity
            ->method('get')
            ->withConsecutive(['createdById'])
            ->willReturnOnConsecutiveCalls('some-id');

        $this->assertTrue($service->checkIsOwner($user, $entity));

        // test 4
        $metadata = $this->createMockService(Metadata::class, ['get']);
        $metadata
            ->method('get')
            ->withConsecutive(
                ['scopes.' . $this->entityType . '.hasOwner'],
                ['scopes.' . $this->entityType . '.hasAssignedUser']
            )->willReturnOnConsecutiveCalls(false, false);
        $service = $this->createMockService(Base::class, ['getInjection']);
        $service
            ->expects($this->any())
            ->method('getInjection')
            ->willReturn($metadata);

        $entity = $this->createMockService(
            Entity::class,
            ['getEntityType', 'hasAttribute', 'hasRelation', 'hasLinkMultipleId']
        );
        $entity
            ->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityType);
        $entity
            ->method('hasAttribute')
            ->withConsecutive(['createdById'], ['assignedUsersIds'])
            ->willReturnOnConsecutiveCalls(false, true);
        $entity
            ->method('hasRelation')
            ->withConsecutive(['assignedUsers'])
            ->willReturnOnConsecutiveCalls(true);
        $entity
            ->method('hasLinkMultipleId')
            ->withConsecutive(['assignedUsers', 'some-id'])
            ->willReturnOnConsecutiveCalls(true);

        $this->assertTrue($service->checkIsOwner($user, $entity));
    }

    public function testCheckIsOwnerReturnFalse()
    {
        $user = $this->createMockService(User::class);

        $metadata = $this->createMockService(Metadata::class, ['get']);
        $metadata
            ->method('get')
            ->withConsecutive(
                ['scopes.' . $this->entityType . '.hasOwner'],
                ['scopes.' . $this->entityType . '.hasAssignedUser']
            )->willReturnOnConsecutiveCalls(false, false);
        $service = $this->createMockService(Base::class, ['getInjection']);
        $service
            ->expects($this->any())
            ->method('getInjection')
            ->willReturn($metadata);

        $entity = $this->createMockService(
            Entity::class,
            ['getEntityType', 'hasAttribute', 'hasRelation', 'hasLinkMultipleId']
        );
        $entity
            ->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityType);
        $entity
            ->method('hasAttribute')
            ->withConsecutive(['createdById'], ['assignedUsersIds'])
            ->willReturnOnConsecutiveCalls(false, false);

        // test
        $this->assertFalse($service->checkIsOwner($user, $entity));
    }

    /**
     * Create mock service
     *
     * @param string $name
     * @param array  $methods
     *
     * @return mixed
     */
    protected function createMockService(string $name, array $methods = [])
    {
        // define path to core app
        if (!defined('CORE_PATH')) {
            define('CORE_PATH', dirname(dirname(dirname(__DIR__))));
        }

        $service = $this->createPartialMock($name, array_merge(['getContainer', 'getConfig'], $methods));
        $service
            ->expects($this->any())
            ->method('getContainer')
            ->willReturn($this->getContainer());
        $service
            ->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->getConfig());

        return $service;
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        $container = $this->createPartialMock(Container::class, ['getConfig']);
        $container
            ->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->getConfig());

        return $container;
    }

    /**
     * @return Config
     */
    protected function getConfig()
    {
        $config = $this->createPartialMock(Config::class, ['set', 'get', 'save']);
        $config
            ->expects($this->any())
            ->method('set')
            ->willReturn(true);
        $config
            ->expects($this->any())
            ->method('get')
            ->willReturn(true);
        $config
            ->expects($this->any())
            ->method('save')
            ->willReturn(true);

        return $config;
    }
}
