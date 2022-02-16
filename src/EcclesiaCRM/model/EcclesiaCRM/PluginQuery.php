<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\PluginQuery as BasePluginQuery;

use EcclesiaCRM\PluginMenuBarreQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'plugin' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class PluginQuery extends BasePluginQuery
{
    public function preDelete(ConnectionInterface $con = null)
    {
        $pluginMenuBarItems = PluginMenuBarreQuery::create()->findByPluginName($this->getName());

        foreach ($pluginMenuBarItems as $pluginMenuBarItem) {
            $pluginMenuBarItem->delete();
        }

        return parent::preDelete($con);
    }
}
