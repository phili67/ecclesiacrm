<?php

namespace PluginStore\Map;

use PluginStore\ToDoListDashboardItem;
use PluginStore\ToDoListDashboardItemQuery;
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
 * This class defines the structure of the 'tdl_l_item' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class ToDoListDashboardItemTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'PluginStore.Map.ToDoListDashboardItemTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'pluginstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'tdl_l_item';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'ToDoListDashboardItem';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PluginStore\\ToDoListDashboardItem';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'PluginStore.ToDoListDashboardItem';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 6;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 6;

    /**
     * the column name for the tdl_l_i_id field
     */
    public const COL_TDL_L_I_ID = 'tdl_l_item.tdl_l_i_id';

    /**
     * the column name for the tdl_l_i_list field
     */
    public const COL_TDL_L_I_LIST = 'tdl_l_item.tdl_l_i_list';

    /**
     * the column name for the tdl_l_i_checked field
     */
    public const COL_TDL_L_I_CHECKED = 'tdl_l_item.tdl_l_i_checked';

    /**
     * the column name for the tdl_l_i_name field
     */
    public const COL_TDL_L_I_NAME = 'tdl_l_item.tdl_l_i_name';

    /**
     * the column name for the tdl_l_i_date_time field
     */
    public const COL_TDL_L_I_DATE_TIME = 'tdl_l_item.tdl_l_i_date_time';

    /**
     * the column name for the tdl_l_i_place field
     */
    public const COL_TDL_L_I_PLACE = 'tdl_l_item.tdl_l_i_place';

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
        self::TYPE_PHPNAME       => ['Id', 'List', 'Checked', 'Name', 'DateTime', 'Place', ],
        self::TYPE_CAMELNAME     => ['id', 'list', 'checked', 'name', 'dateTime', 'place', ],
        self::TYPE_COLNAME       => [ToDoListDashboardItemTableMap::COL_TDL_L_I_ID, ToDoListDashboardItemTableMap::COL_TDL_L_I_LIST, ToDoListDashboardItemTableMap::COL_TDL_L_I_CHECKED, ToDoListDashboardItemTableMap::COL_TDL_L_I_NAME, ToDoListDashboardItemTableMap::COL_TDL_L_I_DATE_TIME, ToDoListDashboardItemTableMap::COL_TDL_L_I_PLACE, ],
        self::TYPE_FIELDNAME     => ['tdl_l_i_id', 'tdl_l_i_list', 'tdl_l_i_checked', 'tdl_l_i_name', 'tdl_l_i_date_time', 'tdl_l_i_place', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, ]
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'List' => 1, 'Checked' => 2, 'Name' => 3, 'DateTime' => 4, 'Place' => 5, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'list' => 1, 'checked' => 2, 'name' => 3, 'dateTime' => 4, 'place' => 5, ],
        self::TYPE_COLNAME       => [ToDoListDashboardItemTableMap::COL_TDL_L_I_ID => 0, ToDoListDashboardItemTableMap::COL_TDL_L_I_LIST => 1, ToDoListDashboardItemTableMap::COL_TDL_L_I_CHECKED => 2, ToDoListDashboardItemTableMap::COL_TDL_L_I_NAME => 3, ToDoListDashboardItemTableMap::COL_TDL_L_I_DATE_TIME => 4, ToDoListDashboardItemTableMap::COL_TDL_L_I_PLACE => 5, ],
        self::TYPE_FIELDNAME     => ['tdl_l_i_id' => 0, 'tdl_l_i_list' => 1, 'tdl_l_i_checked' => 2, 'tdl_l_i_name' => 3, 'tdl_l_i_date_time' => 4, 'tdl_l_i_place' => 5, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'TDL_L_I_ID',
        'ToDoListDashboardItem.Id' => 'TDL_L_I_ID',
        'id' => 'TDL_L_I_ID',
        'toDoListDashboardItem.id' => 'TDL_L_I_ID',
        'ToDoListDashboardItemTableMap::COL_TDL_L_I_ID' => 'TDL_L_I_ID',
        'COL_TDL_L_I_ID' => 'TDL_L_I_ID',
        'tdl_l_i_id' => 'TDL_L_I_ID',
        'tdl_l_item.tdl_l_i_id' => 'TDL_L_I_ID',
        'List' => 'TDL_L_I_LIST',
        'ToDoListDashboardItem.List' => 'TDL_L_I_LIST',
        'list' => 'TDL_L_I_LIST',
        'toDoListDashboardItem.list' => 'TDL_L_I_LIST',
        'ToDoListDashboardItemTableMap::COL_TDL_L_I_LIST' => 'TDL_L_I_LIST',
        'COL_TDL_L_I_LIST' => 'TDL_L_I_LIST',
        'tdl_l_i_list' => 'TDL_L_I_LIST',
        'tdl_l_item.tdl_l_i_list' => 'TDL_L_I_LIST',
        'Checked' => 'TDL_L_I_CHECKED',
        'ToDoListDashboardItem.Checked' => 'TDL_L_I_CHECKED',
        'checked' => 'TDL_L_I_CHECKED',
        'toDoListDashboardItem.checked' => 'TDL_L_I_CHECKED',
        'ToDoListDashboardItemTableMap::COL_TDL_L_I_CHECKED' => 'TDL_L_I_CHECKED',
        'COL_TDL_L_I_CHECKED' => 'TDL_L_I_CHECKED',
        'tdl_l_i_checked' => 'TDL_L_I_CHECKED',
        'tdl_l_item.tdl_l_i_checked' => 'TDL_L_I_CHECKED',
        'Name' => 'TDL_L_I_NAME',
        'ToDoListDashboardItem.Name' => 'TDL_L_I_NAME',
        'name' => 'TDL_L_I_NAME',
        'toDoListDashboardItem.name' => 'TDL_L_I_NAME',
        'ToDoListDashboardItemTableMap::COL_TDL_L_I_NAME' => 'TDL_L_I_NAME',
        'COL_TDL_L_I_NAME' => 'TDL_L_I_NAME',
        'tdl_l_i_name' => 'TDL_L_I_NAME',
        'tdl_l_item.tdl_l_i_name' => 'TDL_L_I_NAME',
        'DateTime' => 'TDL_L_I_DATE_TIME',
        'ToDoListDashboardItem.DateTime' => 'TDL_L_I_DATE_TIME',
        'dateTime' => 'TDL_L_I_DATE_TIME',
        'toDoListDashboardItem.dateTime' => 'TDL_L_I_DATE_TIME',
        'ToDoListDashboardItemTableMap::COL_TDL_L_I_DATE_TIME' => 'TDL_L_I_DATE_TIME',
        'COL_TDL_L_I_DATE_TIME' => 'TDL_L_I_DATE_TIME',
        'tdl_l_i_date_time' => 'TDL_L_I_DATE_TIME',
        'tdl_l_item.tdl_l_i_date_time' => 'TDL_L_I_DATE_TIME',
        'Place' => 'TDL_L_I_PLACE',
        'ToDoListDashboardItem.Place' => 'TDL_L_I_PLACE',
        'place' => 'TDL_L_I_PLACE',
        'toDoListDashboardItem.place' => 'TDL_L_I_PLACE',
        'ToDoListDashboardItemTableMap::COL_TDL_L_I_PLACE' => 'TDL_L_I_PLACE',
        'COL_TDL_L_I_PLACE' => 'TDL_L_I_PLACE',
        'tdl_l_i_place' => 'TDL_L_I_PLACE',
        'tdl_l_item.tdl_l_i_place' => 'TDL_L_I_PLACE',
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
        $this->setName('tdl_l_item');
        $this->setPhpName('ToDoListDashboardItem');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PluginStore\\ToDoListDashboardItem');
        $this->setPackage('PluginStore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('tdl_l_i_id', 'Id', 'SMALLINT', true, null, null);
        $this->addColumn('tdl_l_i_list', 'List', 'SMALLINT', true, null, 0);
        $this->addColumn('tdl_l_i_checked', 'Checked', 'BOOLEAN', true, 1, false);
        $this->addColumn('tdl_l_i_name', 'Name', 'VARCHAR', true, 255, '');
        $this->addColumn('tdl_l_i_date_time', 'DateTime', 'TIMESTAMP', false, null, null);
        $this->addColumn('tdl_l_i_place', 'Place', 'SMALLINT', false, null, 0);
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
        return $withPrefix ? ToDoListDashboardItemTableMap::CLASS_DEFAULT : ToDoListDashboardItemTableMap::OM_CLASS;
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
     * @return array (ToDoListDashboardItem object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = ToDoListDashboardItemTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ToDoListDashboardItemTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ToDoListDashboardItemTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ToDoListDashboardItemTableMap::OM_CLASS;
            /** @var ToDoListDashboardItem $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ToDoListDashboardItemTableMap::addInstanceToPool($obj, $key);
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
            $key = ToDoListDashboardItemTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ToDoListDashboardItemTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var ToDoListDashboardItem $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ToDoListDashboardItemTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID);
            $criteria->addSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_LIST);
            $criteria->addSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_CHECKED);
            $criteria->addSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_NAME);
            $criteria->addSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_DATE_TIME);
            $criteria->addSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_PLACE);
        } else {
            $criteria->addSelectColumn($alias . '.tdl_l_i_id');
            $criteria->addSelectColumn($alias . '.tdl_l_i_list');
            $criteria->addSelectColumn($alias . '.tdl_l_i_checked');
            $criteria->addSelectColumn($alias . '.tdl_l_i_name');
            $criteria->addSelectColumn($alias . '.tdl_l_i_date_time');
            $criteria->addSelectColumn($alias . '.tdl_l_i_place');
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
            $criteria->removeSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID);
            $criteria->removeSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_LIST);
            $criteria->removeSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_CHECKED);
            $criteria->removeSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_NAME);
            $criteria->removeSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_DATE_TIME);
            $criteria->removeSelectColumn(ToDoListDashboardItemTableMap::COL_TDL_L_I_PLACE);
        } else {
            $criteria->removeSelectColumn($alias . '.tdl_l_i_id');
            $criteria->removeSelectColumn($alias . '.tdl_l_i_list');
            $criteria->removeSelectColumn($alias . '.tdl_l_i_checked');
            $criteria->removeSelectColumn($alias . '.tdl_l_i_name');
            $criteria->removeSelectColumn($alias . '.tdl_l_i_date_time');
            $criteria->removeSelectColumn($alias . '.tdl_l_i_place');
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
        return Propel::getServiceContainer()->getDatabaseMap(ToDoListDashboardItemTableMap::DATABASE_NAME)->getTable(ToDoListDashboardItemTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a ToDoListDashboardItem or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or ToDoListDashboardItem object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ToDoListDashboardItemTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PluginStore\ToDoListDashboardItem) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ToDoListDashboardItemTableMap::DATABASE_NAME);
            $criteria->add(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID, (array) $values, Criteria::IN);
        }

        $query = ToDoListDashboardItemQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ToDoListDashboardItemTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ToDoListDashboardItemTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the tdl_l_item table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return ToDoListDashboardItemQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a ToDoListDashboardItem or Criteria object.
     *
     * @param mixed $criteria Criteria or ToDoListDashboardItem object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ToDoListDashboardItemTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from ToDoListDashboardItem object
        }

        if ($criteria->containsKey(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID) && $criteria->keyContainsValue(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.ToDoListDashboardItemTableMap::COL_TDL_L_I_ID.')');
        }


        // Set the correct dbName
        $query = ToDoListDashboardItemQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
