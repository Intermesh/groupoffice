 <?php
namespace go\modules\community\addressbook\controller;
use go\core\jmap\EntityController;
use go\modules\community\addressbook\model;

/**
 * The controller for the Addressbook entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class CustomFilter extends EntityController
{
    /**
     * The class name of the entity this controller is for.
     *
     * @return string
     */
    protected function entityClass()
    {
        return model\CustomFilter::class;
    }

    /**
     * Handles the Foo entity's Foo/query command
     *
     * @param array $params
     * @see https://jmap.io/spec-core.html#/query
     */
    public function query($params)
    {
        return $this->defaultQuery($params);
    }

    /**
     * Handles the Foo entity's Foo/get command
     *
     * @param array $params
     * @see https://jmap.io/spec-core.html#/get
     */
    public function get($params)
    {
        return $this->defaultGet($params);
    }

    /**
     * Handles the Foo entity's Foo/set command
     *
     * @see https://jmap.io/spec-core.html#/set
     * @param array $params
     */
    public function set($params)
    {
        return $this->defaultSet($params);
    }

    /**
     * Handles the Foo entity's Foo/changes command
     *
     * @param array $params
     * @see https://jmap.io/spec-core.html#/changes
     */
    public function changes($params)
    {
        return $this->defaultChanges($params);
    }
}