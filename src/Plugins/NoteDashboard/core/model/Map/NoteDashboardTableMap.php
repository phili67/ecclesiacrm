<?php

namespace PluginStore\Map;

use PluginStore\NoteDashboard;
use PluginStore\NoteDashboardQuery;
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
 * This class defines the structure of the 'NoteDashboard_nd' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class NoteDashboardTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'PluginStore.Map.NoteDashboardTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'pluginstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'NoteDashboard_nd';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'NoteDashboard';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PluginStore\\NoteDashboard';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'PluginStore.NoteDashboard';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 3;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 3;

    /**
     * the column name for the nd_id field
     */
    public const COL_ND_ID = 'NoteDashboard_nd.nd_id';

    /**
     * the column name for the nd_user_id field
     */
    public const COL_ND_USER_ID = 'NoteDashboard_nd.nd_user_id';

    /**
     * the column name for the nd_note field
     */
    public const COL_ND_NOTE = 'NoteDashboard_nd.nd_note';

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
        self::TYPE_PHPNAME       => ['Id', 'UserId', 'Note', ],
        self::TYPE_CAMELNAME     => ['id', 'userId', 'note', ],
        self::TYPE_COLNAME       => [NoteDashboardTableMap::COL_ND_ID, NoteDashboardTableMap::COL_ND_USER_ID, NoteDashboardTableMap::COL_ND_NOTE, ],
        self::TYPE_FIELDNAME     => ['nd_id', 'nd_user_id', 'nd_note', ],
        self::TYPE_NUM           => [0, 1, 2, ]
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'UserId' => 1, 'Note' => 2, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'userId' => 1, 'note' => 2, ],
        self::TYPE_COLNAME       => [NoteDashboardTableMap::COL_ND_ID => 0, NoteDashboardTableMap::COL_ND_USER_ID => 1, NoteDashboardTableMap::COL_ND_NOTE => 2, ],
        self::TYPE_FIELDNAME     => ['nd_id' => 0, 'nd_user_id' => 1, 'nd_note' => 2, ],
        self::TYPE_NUM           => [0, 1, 2, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'ND_ID',
        'NoteDashboard.Id' => 'ND_ID',
        'id' => 'ND_ID',
        'noteDashboard.id' => 'ND_ID',
        'NoteDashboardTableMap::COL_ND_ID' => 'ND_ID',
        'COL_ND_ID' => 'ND_ID',
        'nd_id' => 'ND_ID',
        'NoteDashboard_nd.nd_id' => 'ND_ID',
        'UserId' => 'ND_USER_ID',
        'NoteDashboard.UserId' => 'ND_USER_ID',
        'userId' => 'ND_USER_ID',
        'noteDashboard.userId' => 'ND_USER_ID',
        'NoteDashboardTableMap::COL_ND_USER_ID' => 'ND_USER_ID',
        'COL_ND_USER_ID' => 'ND_USER_ID',
        'nd_user_id' => 'ND_USER_ID',
        'NoteDashboard_nd.nd_user_id' => 'ND_USER_ID',
        'Note' => 'ND_NOTE',
        'NoteDashboard.Note' => 'ND_NOTE',
        'note' => 'ND_NOTE',
        'noteDashboard.note' => 'ND_NOTE',
        'NoteDashboardTableMap::COL_ND_NOTE' => 'ND_NOTE',
        'COL_ND_NOTE' => 'ND_NOTE',
        'nd_note' => 'ND_NOTE',
        'NoteDashboard_nd.nd_note' => 'ND_NOTE',
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
        $this->setName('NoteDashboard_nd');
        $this->setPhpName('NoteDashboard');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PluginStore\\NoteDashboard');
        $this->setPackage('PluginStore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('nd_id', 'Id', 'SMALLINT', true, null, null);
        $this->addColumn('nd_user_id', 'UserId', 'SMALLINT', true, null, 0);
        $this->addColumn('nd_note', 'Note', 'LONGVARCHAR', false, null, null);
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
        return $withPrefix ? NoteDashboardTableMap::CLASS_DEFAULT : NoteDashboardTableMap::OM_CLASS;
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
     * @return array (NoteDashboard object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = NoteDashboardTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = NoteDashboardTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + NoteDashboardTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = NoteDashboardTableMap::OM_CLASS;
            /** @var NoteDashboard $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            NoteDashboardTableMap::addInstanceToPool($obj, $key);
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
            $key = NoteDashboardTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = NoteDashboardTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var NoteDashboard $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                NoteDashboardTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(NoteDashboardTableMap::COL_ND_ID);
            $criteria->addSelectColumn(NoteDashboardTableMap::COL_ND_USER_ID);
            $criteria->addSelectColumn(NoteDashboardTableMap::COL_ND_NOTE);
        } else {
            $criteria->addSelectColumn($alias . '.nd_id');
            $criteria->addSelectColumn($alias . '.nd_user_id');
            $criteria->addSelectColumn($alias . '.nd_note');
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
            $criteria->removeSelectColumn(NoteDashboardTableMap::COL_ND_ID);
            $criteria->removeSelectColumn(NoteDashboardTableMap::COL_ND_USER_ID);
            $criteria->removeSelectColumn(NoteDashboardTableMap::COL_ND_NOTE);
        } else {
            $criteria->removeSelectColumn($alias . '.nd_id');
            $criteria->removeSelectColumn($alias . '.nd_user_id');
            $criteria->removeSelectColumn($alias . '.nd_note');
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
        return Propel::getServiceContainer()->getDatabaseMap(NoteDashboardTableMap::DATABASE_NAME)->getTable(NoteDashboardTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a NoteDashboard or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or NoteDashboard object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(NoteDashboardTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PluginStore\NoteDashboard) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(NoteDashboardTableMap::DATABASE_NAME);
            $criteria->add(NoteDashboardTableMap::COL_ND_ID, (array) $values, Criteria::IN);
        }

        $query = NoteDashboardQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            NoteDashboardTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                NoteDashboardTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the NoteDashboard_nd table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return NoteDashboardQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a NoteDashboard or Criteria object.
     *
     * @param mixed $criteria Criteria or NoteDashboard object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(NoteDashboardTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from NoteDashboard object
        }

        if ($criteria->containsKey(NoteDashboardTableMap::COL_ND_ID) && $criteria->keyContainsValue(NoteDashboardTableMap::COL_ND_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.NoteDashboardTableMap::COL_ND_ID.')');
        }


        // Set the correct dbName
        $query = NoteDashboardQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
