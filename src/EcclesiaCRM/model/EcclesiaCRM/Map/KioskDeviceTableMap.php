<?php

namespace EcclesiaCRM\Map;

use EcclesiaCRM\KioskDevice;
use EcclesiaCRM\KioskDeviceQuery;
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
 * This class defines the structure of the 'kioskdevice_kdev' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class KioskDeviceTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'EcclesiaCRM.Map.KioskDeviceTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'kioskdevice_kdev';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\EcclesiaCRM\\KioskDevice';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'EcclesiaCRM.KioskDevice';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 7;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 7;

    /**
     * the column name for the kdev_ID field
     */
    const COL_KDEV_ID = 'kioskdevice_kdev.kdev_ID';

    /**
     * the column name for the kdev_GUIDHash field
     */
    const COL_KDEV_GUIDHASH = 'kioskdevice_kdev.kdev_GUIDHash';

    /**
     * the column name for the kdev_Name field
     */
    const COL_KDEV_NAME = 'kioskdevice_kdev.kdev_Name';

    /**
     * the column name for the kdev_deviceType field
     */
    const COL_KDEV_DEVICETYPE = 'kioskdevice_kdev.kdev_deviceType';

    /**
     * the column name for the kdev_lastHeartbeat field
     */
    const COL_KDEV_LASTHEARTBEAT = 'kioskdevice_kdev.kdev_lastHeartbeat';

    /**
     * the column name for the kdev_Accepted field
     */
    const COL_KDEV_ACCEPTED = 'kioskdevice_kdev.kdev_Accepted';

    /**
     * the column name for the kdev_PendingCommands field
     */
    const COL_KDEV_PENDINGCOMMANDS = 'kioskdevice_kdev.kdev_PendingCommands';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'GUIDHash', 'Name', 'DeviceType', 'LastHeartbeat', 'Accepted', 'PendingCommands', ),
        self::TYPE_CAMELNAME     => array('id', 'gUIDHash', 'name', 'deviceType', 'lastHeartbeat', 'accepted', 'pendingCommands', ),
        self::TYPE_COLNAME       => array(KioskDeviceTableMap::COL_KDEV_ID, KioskDeviceTableMap::COL_KDEV_GUIDHASH, KioskDeviceTableMap::COL_KDEV_NAME, KioskDeviceTableMap::COL_KDEV_DEVICETYPE, KioskDeviceTableMap::COL_KDEV_LASTHEARTBEAT, KioskDeviceTableMap::COL_KDEV_ACCEPTED, KioskDeviceTableMap::COL_KDEV_PENDINGCOMMANDS, ),
        self::TYPE_FIELDNAME     => array('kdev_ID', 'kdev_GUIDHash', 'kdev_Name', 'kdev_deviceType', 'kdev_lastHeartbeat', 'kdev_Accepted', 'kdev_PendingCommands', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'GUIDHash' => 1, 'Name' => 2, 'DeviceType' => 3, 'LastHeartbeat' => 4, 'Accepted' => 5, 'PendingCommands' => 6, ),
        self::TYPE_CAMELNAME     => array('id' => 0, 'gUIDHash' => 1, 'name' => 2, 'deviceType' => 3, 'lastHeartbeat' => 4, 'accepted' => 5, 'pendingCommands' => 6, ),
        self::TYPE_COLNAME       => array(KioskDeviceTableMap::COL_KDEV_ID => 0, KioskDeviceTableMap::COL_KDEV_GUIDHASH => 1, KioskDeviceTableMap::COL_KDEV_NAME => 2, KioskDeviceTableMap::COL_KDEV_DEVICETYPE => 3, KioskDeviceTableMap::COL_KDEV_LASTHEARTBEAT => 4, KioskDeviceTableMap::COL_KDEV_ACCEPTED => 5, KioskDeviceTableMap::COL_KDEV_PENDINGCOMMANDS => 6, ),
        self::TYPE_FIELDNAME     => array('kdev_ID' => 0, 'kdev_GUIDHash' => 1, 'kdev_Name' => 2, 'kdev_deviceType' => 3, 'kdev_lastHeartbeat' => 4, 'kdev_Accepted' => 5, 'kdev_PendingCommands' => 6, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('kioskdevice_kdev');
        $this->setPhpName('KioskDevice');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\EcclesiaCRM\\KioskDevice');
        $this->setPackage('EcclesiaCRM');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('kdev_ID', 'Id', 'INTEGER', true, 9, null);
        $this->addColumn('kdev_GUIDHash', 'GUIDHash', 'VARCHAR', false, 36, null);
        $this->addColumn('kdev_Name', 'Name', 'VARCHAR', false, 50, null);
        $this->addColumn('kdev_deviceType', 'DeviceType', 'LONGVARCHAR', false, null, null);
        $this->addColumn('kdev_lastHeartbeat', 'LastHeartbeat', 'LONGVARCHAR', false, null, null);
        $this->addColumn('kdev_Accepted', 'Accepted', 'BOOLEAN', false, 1, null);
        $this->addColumn('kdev_PendingCommands', 'PendingCommands', 'LONGVARCHAR', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('KioskAssignment', '\\EcclesiaCRM\\KioskAssignment', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':kasm_kdevId',
    1 => ':kdev_ID',
  ),
), null, null, 'KioskAssignments', false);
    } // buildRelations()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
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
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
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
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? KioskDeviceTableMap::CLASS_DEFAULT : KioskDeviceTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array           (KioskDevice object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = KioskDeviceTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = KioskDeviceTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + KioskDeviceTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = KioskDeviceTableMap::OM_CLASS;
            /** @var KioskDevice $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            KioskDeviceTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = KioskDeviceTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = KioskDeviceTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var KioskDevice $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                KioskDeviceTableMap::addInstanceToPool($obj, $key);
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
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(KioskDeviceTableMap::COL_KDEV_ID);
            $criteria->addSelectColumn(KioskDeviceTableMap::COL_KDEV_GUIDHASH);
            $criteria->addSelectColumn(KioskDeviceTableMap::COL_KDEV_NAME);
            $criteria->addSelectColumn(KioskDeviceTableMap::COL_KDEV_DEVICETYPE);
            $criteria->addSelectColumn(KioskDeviceTableMap::COL_KDEV_LASTHEARTBEAT);
            $criteria->addSelectColumn(KioskDeviceTableMap::COL_KDEV_ACCEPTED);
            $criteria->addSelectColumn(KioskDeviceTableMap::COL_KDEV_PENDINGCOMMANDS);
        } else {
            $criteria->addSelectColumn($alias . '.kdev_ID');
            $criteria->addSelectColumn($alias . '.kdev_GUIDHash');
            $criteria->addSelectColumn($alias . '.kdev_Name');
            $criteria->addSelectColumn($alias . '.kdev_deviceType');
            $criteria->addSelectColumn($alias . '.kdev_lastHeartbeat');
            $criteria->addSelectColumn($alias . '.kdev_Accepted');
            $criteria->addSelectColumn($alias . '.kdev_PendingCommands');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(KioskDeviceTableMap::DATABASE_NAME)->getTable(KioskDeviceTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(KioskDeviceTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(KioskDeviceTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new KioskDeviceTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a KioskDevice or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or KioskDevice object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param  ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(KioskDeviceTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \EcclesiaCRM\KioskDevice) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(KioskDeviceTableMap::DATABASE_NAME);
            $criteria->add(KioskDeviceTableMap::COL_KDEV_ID, (array) $values, Criteria::IN);
        }

        $query = KioskDeviceQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            KioskDeviceTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                KioskDeviceTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the kioskdevice_kdev table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return KioskDeviceQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a KioskDevice or Criteria object.
     *
     * @param mixed               $criteria Criteria or KioskDevice object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(KioskDeviceTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from KioskDevice object
        }

        if ($criteria->containsKey(KioskDeviceTableMap::COL_KDEV_ID) && $criteria->keyContainsValue(KioskDeviceTableMap::COL_KDEV_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.KioskDeviceTableMap::COL_KDEV_ID.')');
        }


        // Set the correct dbName
        $query = KioskDeviceQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // KioskDeviceTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
KioskDeviceTableMap::buildTableMap();
