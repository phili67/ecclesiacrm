<?php

namespace PluginStore\Map;

use PluginStore\ToDoListDashboard;
use PluginStore\ToDoListDashboardQuery;
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
 * This class defines the structure of the 'tdl_list' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class ToDoListDashboardTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'PluginStore.Map.ToDoListDashboardTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'pluginstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'tdl_list';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'ToDoListDashboard';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PluginStore\\ToDoListDashboard';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'PluginStore.ToDoListDashboard';

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
     * the column name for the tdl_l_id field
     */
    public const COL_TDL_L_ID = 'tdl_list.tdl_l_id';

    /**
     * the column name for the tdl_l_name field
     */
    public const COL_TDL_L_NAME = 'tdl_list.tdl_l_name';

    /**
     * the column name for the tdl_l_user_id field
     */
    public const COL_TDL_L_USER_ID = 'tdl_list.tdl_l_user_id';

    /**
     * the column name for the tdl_l_visible field
     */
    public const COL_TDL_L_VISIBLE = 'tdl_list.tdl_l_visible';

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
        self::TYPE_PHPNAME       => ['Id', 'Name', 'UserId', 'Visible', ],
        self::TYPE_CAMELNAME     => ['id', 'name', 'userId', 'visible', ],
        self::TYPE_COLNAME       => [ToDoListDashboardTableMap::COL_TDL_L_ID, ToDoListDashboardTableMap::COL_TDL_L_NAME, ToDoListDashboardTableMap::COL_TDL_L_USER_ID, ToDoListDashboardTableMap::COL_TDL_L_VISIBLE, ],
        self::TYPE_FIELDNAME     => ['tdl_l_id', 'tdl_l_name', 'tdl_l_user_id', 'tdl_l_visible', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'Name' => 1, 'UserId' => 2, 'Visible' => 3, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'name' => 1, 'userId' => 2, 'visible' => 3, ],
        self::TYPE_COLNAME       => [ToDoListDashboardTableMap::COL_TDL_L_ID => 0, ToDoListDashboardTableMap::COL_TDL_L_NAME => 1, ToDoListDashboardTableMap::COL_TDL_L_USER_ID => 2, ToDoListDashboardTableMap::COL_TDL_L_VISIBLE => 3, ],
        self::TYPE_FIELDNAME     => ['tdl_l_id' => 0, 'tdl_l_name' => 1, 'tdl_l_user_id' => 2, 'tdl_l_visible' => 3, ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'TDL_L_ID',
        'ToDoListDashboard.Id' => 'TDL_L_ID',
        'id' => 'TDL_L_ID',
        'toDoListDashboard.id' => 'TDL_L_ID',
        'ToDoListDashboardTableMap::COL_TDL_L_ID' => 'TDL_L_ID',
        'COL_TDL_L_ID' => 'TDL_L_ID',
        'tdl_l_id' => 'TDL_L_ID',
        'tdl_list.tdl_l_id' => 'TDL_L_ID',
        'Name' => 'TDL_L_NAME',
        'ToDoListDashboard.Name' => 'TDL_L_NAME',
        'name' => 'TDL_L_NAME',
        'toDoListDashboard.name' => 'TDL_L_NAME',
        'ToDoListDashboardTableMap::COL_TDL_L_NAME' => 'TDL_L_NAME',
        'COL_TDL_L_NAME' => 'TDL_L_NAME',
        'tdl_l_name' => 'TDL_L_NAME',
        'tdl_list.tdl_l_name' => 'TDL_L_NAME',
        'UserId' => 'TDL_L_USER_ID',
        'ToDoListDashboard.UserId' => 'TDL_L_USER_ID',
        'userId' => 'TDL_L_USER_ID',
        'toDoListDashboard.userId' => 'TDL_L_USER_ID',
        'ToDoListDashboardTableMap::COL_TDL_L_USER_ID' => 'TDL_L_USER_ID',
        'COL_TDL_L_USER_ID' => 'TDL_L_USER_ID',
        'tdl_l_user_id' => 'TDL_L_USER_ID',
        'tdl_list.tdl_l_user_id' => 'TDL_L_USER_ID',
        'Visible' => 'TDL_L_VISIBLE',
        'ToDoListDashboard.Visible' => 'TDL_L_VISIBLE',
        'visible' => 'TDL_L_VISIBLE',
        'toDoListDashboard.visible' => 'TDL_L_VISIBLE',
        'ToDoListDashboardTableMap::COL_TDL_L_VISIBLE' => 'TDL_L_VISIBLE',
        'COL_TDL_L_VISIBLE' => 'TDL_L_VISIBLE',
        'tdl_l_visible' => 'TDL_L_VISIBLE',
        'tdl_list.tdl_l_visible' => 'TDL_L_VISIBLE',
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
        $this->setName('tdl_list');
        $this->setPhpName('ToDoListDashboard');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PluginStore\\ToDoListDashboard');
        $this->setPackage('PluginStore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('tdl_l_id', 'Id', 'SMALLINT', true, null, null);
        $this->addColumn('tdl_l_name', 'Name', 'VARCHAR', true, 255, '');
        $this->addColumn('tdl_l_user_id', 'UserId', 'SMALLINT', true, null, 0);
        $this->addColumn('tdl_l_visible', 'Visible', 'BOOLEAN', true, 1, false);
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
        return $withPrefix ? ToDoListDashboardTableMap::CLASS_DEFAULT : ToDoListDashboardTableMap::OM_CLASS;
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
     * @return array (ToDoListDashboard object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = ToDoListDashboardTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ToDoListDashboardTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ToDoListDashboardTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ToDoListDashboardTableMap::OM_CLASS;
            /** @var ToDoListDashboard $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ToDoListDashboardTableMap::addInstanceToPool($obj, $key);
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
            $key = ToDoListDashboardTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ToDoListDashboardTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var ToDoListDashboard $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ToDoListDashboardTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(ToDoListDashboardTableMap::COL_TDL_L_ID);
            $criteria->addSelectColumn(ToDoListDashboardTableMap::COL_TDL_L_NAME);
            $criteria->addSelectColumn(ToDoListDashboardTableMap::COL_TDL_L_USER_ID);
            $criteria->addSelectColumn(ToDoListDashboardTableMap::COL_TDL_L_VISIBLE);
        } else {
            $criteria->addSelectColumn($alias . '.tdl_l_id');
            $criteria->addSelectColumn($alias . '.tdl_l_name');
            $criteria->addSelectColumn($alias . '.tdl_l_user_id');
            $criteria->addSelectColumn($alias . '.tdl_l_visible');
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
            $criteria->removeSelectColumn(ToDoListDashboardTableMap::COL_TDL_L_ID);
            $criteria->removeSelectColumn(ToDoListDashboardTableMap::COL_TDL_L_NAME);
            $criteria->removeSelectColumn(ToDoListDashboardTableMap::COL_TDL_L_USER_ID);
            $criteria->removeSelectColumn(ToDoListDashboardTableMap::COL_TDL_L_VISIBLE);
        } else {
            $criteria->removeSelectColumn($alias . '.tdl_l_id');
            $criteria->removeSelectColumn($alias . '.tdl_l_name');
            $criteria->removeSelectColumn($alias . '.tdl_l_user_id');
            $criteria->removeSelectColumn($alias . '.tdl_l_visible');
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
        return Propel::getServiceContainer()->getDatabaseMap(ToDoListDashboardTableMap::DATABASE_NAME)->getTable(ToDoListDashboardTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a ToDoListDashboard or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or ToDoListDashboard object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ToDoListDashboardTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PluginStore\ToDoListDashboard) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ToDoListDashboardTableMap::DATABASE_NAME);
            $criteria->add(ToDoListDashboardTableMap::COL_TDL_L_ID, (array) $values, Criteria::IN);
        }

        $query = ToDoListDashboardQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ToDoListDashboardTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ToDoListDashboardTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the tdl_list table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return ToDoListDashboardQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a ToDoListDashboard or Criteria object.
     *
     * @param mixed $criteria Criteria or ToDoListDashboard object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ToDoListDashboardTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from ToDoListDashboard object
        }

        if ($criteria->containsKey(ToDoListDashboardTableMap::COL_TDL_L_ID) && $criteria->keyContainsValue(ToDoListDashboardTableMap::COL_TDL_L_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.ToDoListDashboardTableMap::COL_TDL_L_ID.')');
        }


        // Set the correct dbName
        $query = ToDoListDashboardQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
