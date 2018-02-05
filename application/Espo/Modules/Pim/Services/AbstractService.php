<?php
declare(strict_types = 1);

namespace Espo\Modules\Pim\Services;

use Espo\Core\Templates\Services\Base;

/**
 * Class of AbstractService
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
abstract class AbstractService extends Base
{

    /**
     * Construct
     */
    public function __construct(...$args)
    {
        parent::__construct(...$args);

        // add dependencies
        $this->addDependency('language');
        $this->addDependency('eventManager');
    }

    /**
     * Get ACL "where" SQL
     *
     * @param string $entityName
     * @param string $entityAlias
     *
     * @return string
     */
    public function getAclWhereSql(string $entityName, string $entityAlias): string
    {
        // prepare sql
        $sql = '';

        if (!$this->getUser()->isAdmin()) {
            // prepare data
            $userId = $this->getUser()->get('id');

            if ($this->getAcl()->checkReadOnlyOwn($entityName)) {
                $sql .= " AND $entityAlias.assigned_user_id = '$userId'";
            }
            if ($this->getAcl()->checkReadOnlyTeam($entityName)) {
                $sql .= " AND $entityAlias.id IN ("
                    ."SELECT et.entity_id "
                    ."FROM entity_team AS et "
                    ."JOIN team_user AS tu ON tu.team_id=et.team_id "
                    ."WHERE et.deleted=0 AND tu.deleted=0 AND tu.user_id = '$userId' AND et.entity_type='$entityName')";
            }
        }

        return $sql;
    }

    /**
     * Get translated message
     *
     * @param string $label
     * @param string $category
     * @param string $scope
     * @param null   $requiredOptions
     *
     * @return string
     */
    protected function getTranslate(string $label, string $category, string $scope, $requiredOptions = null): string
    {
        return $this->getInjection('language')->translate($label, $category, $scope, $requiredOptions);
    }
}
