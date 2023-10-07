<?php

namespace PluginStore\Map;

use PluginStore\PluginPrefJitsiMeeting;
use PluginStore\PluginPrefJitsiMeetingQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'plugin_pref_jitsimeeting_pjmp' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class PluginPrefJitsiMeetingTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'PluginStore.Map.PluginPrefJitsiMeetingTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'pluginstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'plugin_pref_jitsimeeting_pjmp';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'PluginPrefJitsiMeeting';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PluginStore\\PluginPrefJitsiMeeting';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'PluginStore.PluginPrefJitsiMeeting';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 4;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 4;

    /**
     * the column name for the jm_pjmp_ID field
     */
    public const COL_JM_PJMP_ID = 'plugin_pref_jitsimeeting_pjmp.jm_pjmp_ID';

    /**
     * the column name for the jm_pjmp_personmeeting_pm_id field
     */
    public const COL_JM_PJMP_PERSONMEETING_PM_ID = 'plugin_pref_jitsimeeting_pjmp.jm_pjmp_personmeeting_pm_id';

    /**
     * the column name for the jm_pjmp_domain field
     */
    public const COL_JM_PJMP_DOMAIN = 'plugin_pref_jitsimeeting_pjmp.jm_pjmp_domain';

    /**
     * the column name for the jm_pjmp_domainscriptpath field
     */
    public const COL_JM_PJMP_DOMAINSCRIPTPATH = 'plugin_pref_jitsimeeting_pjmp.jm_pjmp_domainscriptpath';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Id', 'PersonId', 'Domain', 'DomainScriptPath', ],
        self::TYPE_CAMELNAME     => ['id', 'personId', 'domain', 'domainScriptPath', ],
        self::TYPE_COLNAME       => [PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID, PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_PERSONMEETING_PM_ID, PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAIN, PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAINSCRIPTPATH, ],
        self::TYPE_FIELDNAME     => ['jm_pjmp_ID', 'jm_pjmp_personmeeting_pm_id', 'jm_pjmp_domain', 'jm_pjmp_domainscriptpath', ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     *
     * @var array<string, mixed>
     */
    protected static $fieldKeys = [
        self::TYPE_PHPNAME       => ['Id' => 0, 'PersonId' => 1, 'Domain' => 2, 'DomainScriptPath' => 3, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'personId' => 1, 'domain' => 2, 'domainScriptPath' => 3, ],
        self::TYPE_COLNAME       => [PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID => 0, PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_PERSONMEETING_PM_ID => 1, PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAIN => 2, PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAINSCRIPTPATH => 3, ],
        self::TYPE_FIELDNAME     => ['jm_pjmp_ID' => 0, 'jm_pjmp_personmeeting_pm_id' => 1, 'jm_pjmp_domain' => 2, 'jm_pjmp_domainscriptpath' => 3, ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'JM_PJMP_ID',
        'PluginPrefJitsiMeeting.Id' => 'JM_PJMP_ID',
        'id' => 'JM_PJMP_ID',
        'pluginPrefJitsiMeeting.id' => 'JM_PJMP_ID',
        'PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID' => 'JM_PJMP_ID',
        'COL_JM_PJMP_ID' => 'JM_PJMP_ID',
        'jm_pjmp_ID' => 'JM_PJMP_ID',
        'plugin_pref_jitsimeeting_pjmp.jm_pjmp_ID' => 'JM_PJMP_ID',
        'PersonId' => 'JM_PJMP_PERSONMEETING_PM_ID',
        'PluginPrefJitsiMeeting.PersonId' => 'JM_PJMP_PERSONMEETING_PM_ID',
        'personId' => 'JM_PJMP_PERSONMEETING_PM_ID',
        'pluginPrefJitsiMeeting.personId' => 'JM_PJMP_PERSONMEETING_PM_ID',
        'PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_PERSONMEETING_PM_ID' => 'JM_PJMP_PERSONMEETING_PM_ID',
        'COL_JM_PJMP_PERSONMEETING_PM_ID' => 'JM_PJMP_PERSONMEETING_PM_ID',
        'jm_pjmp_personmeeting_pm_id' => 'JM_PJMP_PERSONMEETING_PM_ID',
        'plugin_pref_jitsimeeting_pjmp.jm_pjmp_personmeeting_pm_id' => 'JM_PJMP_PERSONMEETING_PM_ID',
        'Domain' => 'JM_PJMP_DOMAIN',
        'PluginPrefJitsiMeeting.Domain' => 'JM_PJMP_DOMAIN',
        'domain' => 'JM_PJMP_DOMAIN',
        'pluginPrefJitsiMeeting.domain' => 'JM_PJMP_DOMAIN',
        'PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAIN' => 'JM_PJMP_DOMAIN',
        'COL_JM_PJMP_DOMAIN' => 'JM_PJMP_DOMAIN',
        'jm_pjmp_domain' => 'JM_PJMP_DOMAIN',
        'plugin_pref_jitsimeeting_pjmp.jm_pjmp_domain' => 'JM_PJMP_DOMAIN',
        'DomainScriptPath' => 'JM_PJMP_DOMAINSCRIPTPATH',
        'PluginPrefJitsiMeeting.DomainScriptPath' => 'JM_PJMP_DOMAINSCRIPTPATH',
        'domainScriptPath' => 'JM_PJMP_DOMAINSCRIPTPATH',
        'pluginPrefJitsiMeeting.domainScriptPath' => 'JM_PJMP_DOMAINSCRIPTPATH',
        'PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAINSCRIPTPATH' => 'JM_PJMP_DOMAINSCRIPTPATH',
        'COL_JM_PJMP_DOMAINSCRIPTPATH' => 'JM_PJMP_DOMAINSCRIPTPATH',
        'jm_pjmp_domainscriptpath' => 'JM_PJMP_DOMAINSCRIPTPATH',
        'plugin_pref_jitsimeeting_pjmp.jm_pjmp_domainscriptpath' => 'JM_PJMP_DOMAINSCRIPTPATH',
    ];

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function initialize(): void
    {
        // attributes
        $this->setName('plugin_pref_jitsimeeting_pjmp');
        $this->setPhpName('PluginPrefJitsiMeeting');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PluginStore\\PluginPrefJitsiMeeting');
        $this->setPackage('PluginStore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('jm_pjmp_ID', 'Id', 'SMALLINT', true, null, null);
        $this->addColumn('jm_pjmp_personmeeting_pm_id', 'PersonId', 'SMALLINT', true, null, null);
        $this->addColumn('jm_pjmp_domain', 'Domain', 'VARCHAR', true, 255, 'meet.jit.si');
        $this->addColumn('jm_pjmp_domainscriptpath', 'DomainScriptPath', 'VARCHAR', true, 255, 'https://meet.jit.si/external_api.js');
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string|null The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): ?string
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param bool $withPrefix Whether to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass(bool $withPrefix = true): string
    {
        return $withPrefix ? PluginPrefJitsiMeetingTableMap::CLASS_DEFAULT : PluginPrefJitsiMeetingTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array $row Row returned by DataFetcher->fetch().
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array (PluginPrefJitsiMeeting object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = PluginPrefJitsiMeetingTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = PluginPrefJitsiMeetingTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + PluginPrefJitsiMeetingTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = PluginPrefJitsiMeetingTableMap::OM_CLASS;
            /** @var PluginPrefJitsiMeeting $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            PluginPrefJitsiMeetingTableMap::addInstanceToPool($obj, $key);
        }

        return [$obj, $col];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array<object>
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher): array
    {
        $results = [];

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = PluginPrefJitsiMeetingTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = PluginPrefJitsiMeetingTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var PluginPrefJitsiMeeting $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                PluginPrefJitsiMeetingTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria Object containing the columns to add.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function addSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->addSelectColumn(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID);
            $criteria->addSelectColumn(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_PERSONMEETING_PM_ID);
            $criteria->addSelectColumn(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAIN);
            $criteria->addSelectColumn(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAINSCRIPTPATH);
        } else {
            $criteria->addSelectColumn($alias . '.jm_pjmp_ID');
            $criteria->addSelectColumn($alias . '.jm_pjmp_personmeeting_pm_id');
            $criteria->addSelectColumn($alias . '.jm_pjmp_domain');
            $criteria->addSelectColumn($alias . '.jm_pjmp_domainscriptpath');
        }
    }

    /**
     * Remove all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be removed as they are only loaded on demand.
     *
     * @param Criteria $criteria Object containing the columns to remove.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function removeSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->removeSelectColumn(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID);
            $criteria->removeSelectColumn(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_PERSONMEETING_PM_ID);
            $criteria->removeSelectColumn(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAIN);
            $criteria->removeSelectColumn(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAINSCRIPTPATH);
        } else {
            $criteria->removeSelectColumn($alias . '.jm_pjmp_ID');
            $criteria->removeSelectColumn($alias . '.jm_pjmp_personmeeting_pm_id');
            $criteria->removeSelectColumn($alias . '.jm_pjmp_domain');
            $criteria->removeSelectColumn($alias . '.jm_pjmp_domainscriptpath');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap(): TableMap
    {
        return Propel::getServiceContainer()->getDatabaseMap(PluginPrefJitsiMeetingTableMap::DATABASE_NAME)->getTable(PluginPrefJitsiMeetingTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a PluginPrefJitsiMeeting or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or PluginPrefJitsiMeeting object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?ConnectionInterface $con = null): int
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PluginPrefJitsiMeetingTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PluginStore\PluginPrefJitsiMeeting) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(PluginPrefJitsiMeetingTableMap::DATABASE_NAME);
            $criteria->add(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID, (array) $values, Criteria::IN);
        }

        $query = PluginPrefJitsiMeetingQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            PluginPrefJitsiMeetingTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                PluginPrefJitsiMeetingTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the plugin_pref_jitsimeeting_pjmp table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return PluginPrefJitsiMeetingQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a PluginPrefJitsiMeeting or Criteria object.
     *
     * @param mixed $criteria Criteria or PluginPrefJitsiMeeting object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PluginPrefJitsiMeetingTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from PluginPrefJitsiMeeting object
        }

        if ($criteria->containsKey(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID) && $criteria->keyContainsValue(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID.')');
        }


        // Set the correct dbName
        $query = PluginPrefJitsiMeetingQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
