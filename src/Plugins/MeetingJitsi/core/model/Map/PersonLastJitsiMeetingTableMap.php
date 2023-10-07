<?php

namespace PluginStore\Map;

use PluginStore\PersonLastJitsiMeeting;
use PluginStore\PersonLastJitsiMeetingQuery;
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
 * This class defines the structure of the 'personlastjitsimeeting_plm' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class PersonLastJitsiMeetingTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'PluginStore.Map.PersonLastJitsiMeetingTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'pluginstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'personlastjitsimeeting_plm';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'PersonLastJitsiMeeting';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PluginStore\\PersonLastJitsiMeeting';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'PluginStore.PersonLastJitsiMeeting';

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
     * the column name for the jm_plm_ID field
     */
    public const COL_JM_PLM_ID = 'personlastjitsimeeting_plm.jm_plm_ID';

    /**
     * the column name for the jm_plm_person_id field
     */
    public const COL_JM_PLM_PERSON_ID = 'personlastjitsimeeting_plm.jm_plm_person_id';

    /**
     * the column name for the jm_plm_personmeeting_pm_id field
     */
    public const COL_JM_PLM_PERSONMEETING_PM_ID = 'personlastjitsimeeting_plm.jm_plm_personmeeting_pm_id';

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
        self::TYPE_PHPNAME       => ['Id', 'PersonId', 'PersonMeetingId', ],
        self::TYPE_CAMELNAME     => ['id', 'personId', 'personMeetingId', ],
        self::TYPE_COLNAME       => [PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID, PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSON_ID, PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSONMEETING_PM_ID, ],
        self::TYPE_FIELDNAME     => ['jm_plm_ID', 'jm_plm_person_id', 'jm_plm_personmeeting_pm_id', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'PersonId' => 1, 'PersonMeetingId' => 2, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'personId' => 1, 'personMeetingId' => 2, ],
        self::TYPE_COLNAME       => [PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID => 0, PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSON_ID => 1, PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSONMEETING_PM_ID => 2, ],
        self::TYPE_FIELDNAME     => ['jm_plm_ID' => 0, 'jm_plm_person_id' => 1, 'jm_plm_personmeeting_pm_id' => 2, ],
        self::TYPE_NUM           => [0, 1, 2, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'JM_PLM_ID',
        'PersonLastJitsiMeeting.Id' => 'JM_PLM_ID',
        'id' => 'JM_PLM_ID',
        'personLastJitsiMeeting.id' => 'JM_PLM_ID',
        'PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID' => 'JM_PLM_ID',
        'COL_JM_PLM_ID' => 'JM_PLM_ID',
        'jm_plm_ID' => 'JM_PLM_ID',
        'personlastjitsimeeting_plm.jm_plm_ID' => 'JM_PLM_ID',
        'PersonId' => 'JM_PLM_PERSON_ID',
        'PersonLastJitsiMeeting.PersonId' => 'JM_PLM_PERSON_ID',
        'personId' => 'JM_PLM_PERSON_ID',
        'personLastJitsiMeeting.personId' => 'JM_PLM_PERSON_ID',
        'PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSON_ID' => 'JM_PLM_PERSON_ID',
        'COL_JM_PLM_PERSON_ID' => 'JM_PLM_PERSON_ID',
        'jm_plm_person_id' => 'JM_PLM_PERSON_ID',
        'personlastjitsimeeting_plm.jm_plm_person_id' => 'JM_PLM_PERSON_ID',
        'PersonMeetingId' => 'JM_PLM_PERSONMEETING_PM_ID',
        'PersonLastJitsiMeeting.PersonMeetingId' => 'JM_PLM_PERSONMEETING_PM_ID',
        'personMeetingId' => 'JM_PLM_PERSONMEETING_PM_ID',
        'personLastJitsiMeeting.personMeetingId' => 'JM_PLM_PERSONMEETING_PM_ID',
        'PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSONMEETING_PM_ID' => 'JM_PLM_PERSONMEETING_PM_ID',
        'COL_JM_PLM_PERSONMEETING_PM_ID' => 'JM_PLM_PERSONMEETING_PM_ID',
        'jm_plm_personmeeting_pm_id' => 'JM_PLM_PERSONMEETING_PM_ID',
        'personlastjitsimeeting_plm.jm_plm_personmeeting_pm_id' => 'JM_PLM_PERSONMEETING_PM_ID',
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
        $this->setName('personlastjitsimeeting_plm');
        $this->setPhpName('PersonLastJitsiMeeting');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PluginStore\\PersonLastJitsiMeeting');
        $this->setPackage('PluginStore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('jm_plm_ID', 'Id', 'SMALLINT', true, null, null);
        $this->addColumn('jm_plm_person_id', 'PersonId', 'SMALLINT', true, null, null);
        $this->addColumn('jm_plm_personmeeting_pm_id', 'PersonMeetingId', 'SMALLINT', true, null, null);
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
        return $withPrefix ? PersonLastJitsiMeetingTableMap::CLASS_DEFAULT : PersonLastJitsiMeetingTableMap::OM_CLASS;
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
     * @return array (PersonLastJitsiMeeting object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = PersonLastJitsiMeetingTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = PersonLastJitsiMeetingTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + PersonLastJitsiMeetingTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = PersonLastJitsiMeetingTableMap::OM_CLASS;
            /** @var PersonLastJitsiMeeting $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            PersonLastJitsiMeetingTableMap::addInstanceToPool($obj, $key);
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
            $key = PersonLastJitsiMeetingTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = PersonLastJitsiMeetingTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var PersonLastJitsiMeeting $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                PersonLastJitsiMeetingTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID);
            $criteria->addSelectColumn(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSON_ID);
            $criteria->addSelectColumn(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSONMEETING_PM_ID);
        } else {
            $criteria->addSelectColumn($alias . '.jm_plm_ID');
            $criteria->addSelectColumn($alias . '.jm_plm_person_id');
            $criteria->addSelectColumn($alias . '.jm_plm_personmeeting_pm_id');
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
            $criteria->removeSelectColumn(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID);
            $criteria->removeSelectColumn(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSON_ID);
            $criteria->removeSelectColumn(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSONMEETING_PM_ID);
        } else {
            $criteria->removeSelectColumn($alias . '.jm_plm_ID');
            $criteria->removeSelectColumn($alias . '.jm_plm_person_id');
            $criteria->removeSelectColumn($alias . '.jm_plm_personmeeting_pm_id');
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
        return Propel::getServiceContainer()->getDatabaseMap(PersonLastJitsiMeetingTableMap::DATABASE_NAME)->getTable(PersonLastJitsiMeetingTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a PersonLastJitsiMeeting or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or PersonLastJitsiMeeting object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(PersonLastJitsiMeetingTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PluginStore\PersonLastJitsiMeeting) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(PersonLastJitsiMeetingTableMap::DATABASE_NAME);
            $criteria->add(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID, (array) $values, Criteria::IN);
        }

        $query = PersonLastJitsiMeetingQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            PersonLastJitsiMeetingTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                PersonLastJitsiMeetingTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the personlastjitsimeeting_plm table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return PersonLastJitsiMeetingQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a PersonLastJitsiMeeting or Criteria object.
     *
     * @param mixed $criteria Criteria or PersonLastJitsiMeeting object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PersonLastJitsiMeetingTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from PersonLastJitsiMeeting object
        }

        if ($criteria->containsKey(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID) && $criteria->keyContainsValue(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID.')');
        }


        // Set the correct dbName
        $query = PersonLastJitsiMeetingQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
