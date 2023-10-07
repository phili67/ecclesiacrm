<?php

namespace PluginStore\Map;

use PluginStore\PersonJitsiMeeting;
use PluginStore\PersonJitsiMeetingQuery;
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
 * This class defines the structure of the 'personjitsimeeting_pm' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class PersonJitsiMeetingTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'PluginStore.Map.PersonJitsiMeetingTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'pluginstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'personjitsimeeting_pm';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'PersonJitsiMeeting';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PluginStore\\PersonJitsiMeeting';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'PluginStore.PersonJitsiMeeting';

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
     * the column name for the jm_pm_ID field
     */
    public const COL_JM_PM_ID = 'personjitsimeeting_pm.jm_pm_ID';

    /**
     * the column name for the jm_pm_person_id field
     */
    public const COL_JM_PM_PERSON_ID = 'personjitsimeeting_pm.jm_pm_person_id';

    /**
     * the column name for the jm_pm_code field
     */
    public const COL_JM_PM_CODE = 'personjitsimeeting_pm.jm_pm_code';

    /**
     * the column name for the jm_pm_cr_date field
     */
    public const COL_JM_PM_CR_DATE = 'personjitsimeeting_pm.jm_pm_cr_date';

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
        self::TYPE_PHPNAME       => ['Id', 'PersonId', 'Code', 'CreationDate', ],
        self::TYPE_CAMELNAME     => ['id', 'personId', 'code', 'creationDate', ],
        self::TYPE_COLNAME       => [PersonJitsiMeetingTableMap::COL_JM_PM_ID, PersonJitsiMeetingTableMap::COL_JM_PM_PERSON_ID, PersonJitsiMeetingTableMap::COL_JM_PM_CODE, PersonJitsiMeetingTableMap::COL_JM_PM_CR_DATE, ],
        self::TYPE_FIELDNAME     => ['jm_pm_ID', 'jm_pm_person_id', 'jm_pm_code', 'jm_pm_cr_date', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'PersonId' => 1, 'Code' => 2, 'CreationDate' => 3, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'personId' => 1, 'code' => 2, 'creationDate' => 3, ],
        self::TYPE_COLNAME       => [PersonJitsiMeetingTableMap::COL_JM_PM_ID => 0, PersonJitsiMeetingTableMap::COL_JM_PM_PERSON_ID => 1, PersonJitsiMeetingTableMap::COL_JM_PM_CODE => 2, PersonJitsiMeetingTableMap::COL_JM_PM_CR_DATE => 3, ],
        self::TYPE_FIELDNAME     => ['jm_pm_ID' => 0, 'jm_pm_person_id' => 1, 'jm_pm_code' => 2, 'jm_pm_cr_date' => 3, ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'JM_PM_ID',
        'PersonJitsiMeeting.Id' => 'JM_PM_ID',
        'id' => 'JM_PM_ID',
        'personJitsiMeeting.id' => 'JM_PM_ID',
        'PersonJitsiMeetingTableMap::COL_JM_PM_ID' => 'JM_PM_ID',
        'COL_JM_PM_ID' => 'JM_PM_ID',
        'jm_pm_ID' => 'JM_PM_ID',
        'personjitsimeeting_pm.jm_pm_ID' => 'JM_PM_ID',
        'PersonId' => 'JM_PM_PERSON_ID',
        'PersonJitsiMeeting.PersonId' => 'JM_PM_PERSON_ID',
        'personId' => 'JM_PM_PERSON_ID',
        'personJitsiMeeting.personId' => 'JM_PM_PERSON_ID',
        'PersonJitsiMeetingTableMap::COL_JM_PM_PERSON_ID' => 'JM_PM_PERSON_ID',
        'COL_JM_PM_PERSON_ID' => 'JM_PM_PERSON_ID',
        'jm_pm_person_id' => 'JM_PM_PERSON_ID',
        'personjitsimeeting_pm.jm_pm_person_id' => 'JM_PM_PERSON_ID',
        'Code' => 'JM_PM_CODE',
        'PersonJitsiMeeting.Code' => 'JM_PM_CODE',
        'code' => 'JM_PM_CODE',
        'personJitsiMeeting.code' => 'JM_PM_CODE',
        'PersonJitsiMeetingTableMap::COL_JM_PM_CODE' => 'JM_PM_CODE',
        'COL_JM_PM_CODE' => 'JM_PM_CODE',
        'jm_pm_code' => 'JM_PM_CODE',
        'personjitsimeeting_pm.jm_pm_code' => 'JM_PM_CODE',
        'CreationDate' => 'JM_PM_CR_DATE',
        'PersonJitsiMeeting.CreationDate' => 'JM_PM_CR_DATE',
        'creationDate' => 'JM_PM_CR_DATE',
        'personJitsiMeeting.creationDate' => 'JM_PM_CR_DATE',
        'PersonJitsiMeetingTableMap::COL_JM_PM_CR_DATE' => 'JM_PM_CR_DATE',
        'COL_JM_PM_CR_DATE' => 'JM_PM_CR_DATE',
        'jm_pm_cr_date' => 'JM_PM_CR_DATE',
        'personjitsimeeting_pm.jm_pm_cr_date' => 'JM_PM_CR_DATE',
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
        $this->setName('personjitsimeeting_pm');
        $this->setPhpName('PersonJitsiMeeting');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PluginStore\\PersonJitsiMeeting');
        $this->setPackage('PluginStore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('jm_pm_ID', 'Id', 'SMALLINT', true, null, null);
        $this->addColumn('jm_pm_person_id', 'PersonId', 'SMALLINT', false, null, null);
        $this->addColumn('jm_pm_code', 'Code', 'VARCHAR', true, 255, '');
        $this->addColumn('jm_pm_cr_date', 'CreationDate', 'TIMESTAMP', false, null, null);
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
        return $withPrefix ? PersonJitsiMeetingTableMap::CLASS_DEFAULT : PersonJitsiMeetingTableMap::OM_CLASS;
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
     * @return array (PersonJitsiMeeting object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = PersonJitsiMeetingTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = PersonJitsiMeetingTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + PersonJitsiMeetingTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = PersonJitsiMeetingTableMap::OM_CLASS;
            /** @var PersonJitsiMeeting $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            PersonJitsiMeetingTableMap::addInstanceToPool($obj, $key);
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
            $key = PersonJitsiMeetingTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = PersonJitsiMeetingTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var PersonJitsiMeeting $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                PersonJitsiMeetingTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(PersonJitsiMeetingTableMap::COL_JM_PM_ID);
            $criteria->addSelectColumn(PersonJitsiMeetingTableMap::COL_JM_PM_PERSON_ID);
            $criteria->addSelectColumn(PersonJitsiMeetingTableMap::COL_JM_PM_CODE);
            $criteria->addSelectColumn(PersonJitsiMeetingTableMap::COL_JM_PM_CR_DATE);
        } else {
            $criteria->addSelectColumn($alias . '.jm_pm_ID');
            $criteria->addSelectColumn($alias . '.jm_pm_person_id');
            $criteria->addSelectColumn($alias . '.jm_pm_code');
            $criteria->addSelectColumn($alias . '.jm_pm_cr_date');
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
            $criteria->removeSelectColumn(PersonJitsiMeetingTableMap::COL_JM_PM_ID);
            $criteria->removeSelectColumn(PersonJitsiMeetingTableMap::COL_JM_PM_PERSON_ID);
            $criteria->removeSelectColumn(PersonJitsiMeetingTableMap::COL_JM_PM_CODE);
            $criteria->removeSelectColumn(PersonJitsiMeetingTableMap::COL_JM_PM_CR_DATE);
        } else {
            $criteria->removeSelectColumn($alias . '.jm_pm_ID');
            $criteria->removeSelectColumn($alias . '.jm_pm_person_id');
            $criteria->removeSelectColumn($alias . '.jm_pm_code');
            $criteria->removeSelectColumn($alias . '.jm_pm_cr_date');
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
        return Propel::getServiceContainer()->getDatabaseMap(PersonJitsiMeetingTableMap::DATABASE_NAME)->getTable(PersonJitsiMeetingTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a PersonJitsiMeeting or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or PersonJitsiMeeting object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(PersonJitsiMeetingTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PluginStore\PersonJitsiMeeting) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(PersonJitsiMeetingTableMap::DATABASE_NAME);
            $criteria->add(PersonJitsiMeetingTableMap::COL_JM_PM_ID, (array) $values, Criteria::IN);
        }

        $query = PersonJitsiMeetingQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            PersonJitsiMeetingTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                PersonJitsiMeetingTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the personjitsimeeting_pm table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return PersonJitsiMeetingQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a PersonJitsiMeeting or Criteria object.
     *
     * @param mixed $criteria Criteria or PersonJitsiMeeting object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PersonJitsiMeetingTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from PersonJitsiMeeting object
        }

        if ($criteria->containsKey(PersonJitsiMeetingTableMap::COL_JM_PM_ID) && $criteria->keyContainsValue(PersonJitsiMeetingTableMap::COL_JM_PM_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.PersonJitsiMeetingTableMap::COL_JM_PM_ID.')');
        }


        // Set the correct dbName
        $query = PersonJitsiMeetingQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
