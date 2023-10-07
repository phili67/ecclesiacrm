<?php

namespace PluginStore\Map;

use PluginStore\NewsDashboard;
use PluginStore\NewsDashboardQuery;
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
 * This class defines the structure of the 'news_nw' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class NewsDashboardTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'PluginStore.Map.NewsDashboardTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'pluginstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'news_nw';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'NewsDashboard';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PluginStore\\NewsDashboard';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'PluginStore.NewsDashboard';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 7;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 7;

    /**
     * the column name for the news_nw_id field
     */
    public const COL_NEWS_NW_ID = 'news_nw.news_nw_id';

    /**
     * the column name for the news_nw_user_id field
     */
    public const COL_NEWS_NW_USER_ID = 'news_nw.news_nw_user_id';

    /**
     * the column name for the news_nw_title field
     */
    public const COL_NEWS_NW_TITLE = 'news_nw.news_nw_title';

    /**
     * the column name for the news_nw_Text field
     */
    public const COL_NEWS_NW_TEXT = 'news_nw.news_nw_Text';

    /**
     * the column name for the news_nw_type field
     */
    public const COL_NEWS_NW_TYPE = 'news_nw.news_nw_type';

    /**
     * the column name for the news_nw_DateEntered field
     */
    public const COL_NEWS_NW_DATEENTERED = 'news_nw.news_nw_DateEntered';

    /**
     * the column name for the news_nw_DateLastEdited field
     */
    public const COL_NEWS_NW_DATELASTEDITED = 'news_nw.news_nw_DateLastEdited';

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
        self::TYPE_PHPNAME       => ['Id', 'UserId', 'Title', 'Text', 'Type', 'Dateentered', 'Datelastedited', ],
        self::TYPE_CAMELNAME     => ['id', 'userId', 'title', 'text', 'type', 'dateentered', 'datelastedited', ],
        self::TYPE_COLNAME       => [NewsDashboardTableMap::COL_NEWS_NW_ID, NewsDashboardTableMap::COL_NEWS_NW_USER_ID, NewsDashboardTableMap::COL_NEWS_NW_TITLE, NewsDashboardTableMap::COL_NEWS_NW_TEXT, NewsDashboardTableMap::COL_NEWS_NW_TYPE, NewsDashboardTableMap::COL_NEWS_NW_DATEENTERED, NewsDashboardTableMap::COL_NEWS_NW_DATELASTEDITED, ],
        self::TYPE_FIELDNAME     => ['news_nw_id', 'news_nw_user_id', 'news_nw_title', 'news_nw_Text', 'news_nw_type', 'news_nw_DateEntered', 'news_nw_DateLastEdited', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, ]
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'UserId' => 1, 'Title' => 2, 'Text' => 3, 'Type' => 4, 'Dateentered' => 5, 'Datelastedited' => 6, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'userId' => 1, 'title' => 2, 'text' => 3, 'type' => 4, 'dateentered' => 5, 'datelastedited' => 6, ],
        self::TYPE_COLNAME       => [NewsDashboardTableMap::COL_NEWS_NW_ID => 0, NewsDashboardTableMap::COL_NEWS_NW_USER_ID => 1, NewsDashboardTableMap::COL_NEWS_NW_TITLE => 2, NewsDashboardTableMap::COL_NEWS_NW_TEXT => 3, NewsDashboardTableMap::COL_NEWS_NW_TYPE => 4, NewsDashboardTableMap::COL_NEWS_NW_DATEENTERED => 5, NewsDashboardTableMap::COL_NEWS_NW_DATELASTEDITED => 6, ],
        self::TYPE_FIELDNAME     => ['news_nw_id' => 0, 'news_nw_user_id' => 1, 'news_nw_title' => 2, 'news_nw_Text' => 3, 'news_nw_type' => 4, 'news_nw_DateEntered' => 5, 'news_nw_DateLastEdited' => 6, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'NEWS_NW_ID',
        'NewsDashboard.Id' => 'NEWS_NW_ID',
        'id' => 'NEWS_NW_ID',
        'newsDashboard.id' => 'NEWS_NW_ID',
        'NewsDashboardTableMap::COL_NEWS_NW_ID' => 'NEWS_NW_ID',
        'COL_NEWS_NW_ID' => 'NEWS_NW_ID',
        'news_nw_id' => 'NEWS_NW_ID',
        'news_nw.news_nw_id' => 'NEWS_NW_ID',
        'UserId' => 'NEWS_NW_USER_ID',
        'NewsDashboard.UserId' => 'NEWS_NW_USER_ID',
        'userId' => 'NEWS_NW_USER_ID',
        'newsDashboard.userId' => 'NEWS_NW_USER_ID',
        'NewsDashboardTableMap::COL_NEWS_NW_USER_ID' => 'NEWS_NW_USER_ID',
        'COL_NEWS_NW_USER_ID' => 'NEWS_NW_USER_ID',
        'news_nw_user_id' => 'NEWS_NW_USER_ID',
        'news_nw.news_nw_user_id' => 'NEWS_NW_USER_ID',
        'Title' => 'NEWS_NW_TITLE',
        'NewsDashboard.Title' => 'NEWS_NW_TITLE',
        'title' => 'NEWS_NW_TITLE',
        'newsDashboard.title' => 'NEWS_NW_TITLE',
        'NewsDashboardTableMap::COL_NEWS_NW_TITLE' => 'NEWS_NW_TITLE',
        'COL_NEWS_NW_TITLE' => 'NEWS_NW_TITLE',
        'news_nw_title' => 'NEWS_NW_TITLE',
        'news_nw.news_nw_title' => 'NEWS_NW_TITLE',
        'Text' => 'NEWS_NW_TEXT',
        'NewsDashboard.Text' => 'NEWS_NW_TEXT',
        'text' => 'NEWS_NW_TEXT',
        'newsDashboard.text' => 'NEWS_NW_TEXT',
        'NewsDashboardTableMap::COL_NEWS_NW_TEXT' => 'NEWS_NW_TEXT',
        'COL_NEWS_NW_TEXT' => 'NEWS_NW_TEXT',
        'news_nw_Text' => 'NEWS_NW_TEXT',
        'news_nw.news_nw_Text' => 'NEWS_NW_TEXT',
        'Type' => 'NEWS_NW_TYPE',
        'NewsDashboard.Type' => 'NEWS_NW_TYPE',
        'type' => 'NEWS_NW_TYPE',
        'newsDashboard.type' => 'NEWS_NW_TYPE',
        'NewsDashboardTableMap::COL_NEWS_NW_TYPE' => 'NEWS_NW_TYPE',
        'COL_NEWS_NW_TYPE' => 'NEWS_NW_TYPE',
        'news_nw_type' => 'NEWS_NW_TYPE',
        'news_nw.news_nw_type' => 'NEWS_NW_TYPE',
        'Dateentered' => 'NEWS_NW_DATEENTERED',
        'NewsDashboard.Dateentered' => 'NEWS_NW_DATEENTERED',
        'dateentered' => 'NEWS_NW_DATEENTERED',
        'newsDashboard.dateentered' => 'NEWS_NW_DATEENTERED',
        'NewsDashboardTableMap::COL_NEWS_NW_DATEENTERED' => 'NEWS_NW_DATEENTERED',
        'COL_NEWS_NW_DATEENTERED' => 'NEWS_NW_DATEENTERED',
        'news_nw_DateEntered' => 'NEWS_NW_DATEENTERED',
        'news_nw.news_nw_DateEntered' => 'NEWS_NW_DATEENTERED',
        'Datelastedited' => 'NEWS_NW_DATELASTEDITED',
        'NewsDashboard.Datelastedited' => 'NEWS_NW_DATELASTEDITED',
        'datelastedited' => 'NEWS_NW_DATELASTEDITED',
        'newsDashboard.datelastedited' => 'NEWS_NW_DATELASTEDITED',
        'NewsDashboardTableMap::COL_NEWS_NW_DATELASTEDITED' => 'NEWS_NW_DATELASTEDITED',
        'COL_NEWS_NW_DATELASTEDITED' => 'NEWS_NW_DATELASTEDITED',
        'news_nw_DateLastEdited' => 'NEWS_NW_DATELASTEDITED',
        'news_nw.news_nw_DateLastEdited' => 'NEWS_NW_DATELASTEDITED',
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
        $this->setName('news_nw');
        $this->setPhpName('NewsDashboard');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PluginStore\\NewsDashboard');
        $this->setPackage('PluginStore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('news_nw_id', 'Id', 'SMALLINT', true, null, null);
        $this->addColumn('news_nw_user_id', 'UserId', 'SMALLINT', true, null, 0);
        $this->addColumn('news_nw_title', 'Title', 'VARCHAR', true, 255, '');
        $this->addColumn('news_nw_Text', 'Text', 'LONGVARCHAR', false, null, null);
        $this->addColumn('news_nw_type', 'Type', 'CHAR', true, null, 'infos');
        $this->addColumn('news_nw_DateEntered', 'Dateentered', 'TIMESTAMP', true, null, null);
        $this->addColumn('news_nw_DateLastEdited', 'Datelastedited', 'TIMESTAMP', false, null, null);
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
        return $withPrefix ? NewsDashboardTableMap::CLASS_DEFAULT : NewsDashboardTableMap::OM_CLASS;
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
     * @return array (NewsDashboard object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = NewsDashboardTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = NewsDashboardTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + NewsDashboardTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = NewsDashboardTableMap::OM_CLASS;
            /** @var NewsDashboard $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            NewsDashboardTableMap::addInstanceToPool($obj, $key);
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
            $key = NewsDashboardTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = NewsDashboardTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var NewsDashboard $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                NewsDashboardTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_ID);
            $criteria->addSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_USER_ID);
            $criteria->addSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_TITLE);
            $criteria->addSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_TEXT);
            $criteria->addSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_TYPE);
            $criteria->addSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_DATEENTERED);
            $criteria->addSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_DATELASTEDITED);
        } else {
            $criteria->addSelectColumn($alias . '.news_nw_id');
            $criteria->addSelectColumn($alias . '.news_nw_user_id');
            $criteria->addSelectColumn($alias . '.news_nw_title');
            $criteria->addSelectColumn($alias . '.news_nw_Text');
            $criteria->addSelectColumn($alias . '.news_nw_type');
            $criteria->addSelectColumn($alias . '.news_nw_DateEntered');
            $criteria->addSelectColumn($alias . '.news_nw_DateLastEdited');
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
            $criteria->removeSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_ID);
            $criteria->removeSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_USER_ID);
            $criteria->removeSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_TITLE);
            $criteria->removeSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_TEXT);
            $criteria->removeSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_TYPE);
            $criteria->removeSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_DATEENTERED);
            $criteria->removeSelectColumn(NewsDashboardTableMap::COL_NEWS_NW_DATELASTEDITED);
        } else {
            $criteria->removeSelectColumn($alias . '.news_nw_id');
            $criteria->removeSelectColumn($alias . '.news_nw_user_id');
            $criteria->removeSelectColumn($alias . '.news_nw_title');
            $criteria->removeSelectColumn($alias . '.news_nw_Text');
            $criteria->removeSelectColumn($alias . '.news_nw_type');
            $criteria->removeSelectColumn($alias . '.news_nw_DateEntered');
            $criteria->removeSelectColumn($alias . '.news_nw_DateLastEdited');
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
        return Propel::getServiceContainer()->getDatabaseMap(NewsDashboardTableMap::DATABASE_NAME)->getTable(NewsDashboardTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a NewsDashboard or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or NewsDashboard object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(NewsDashboardTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PluginStore\NewsDashboard) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(NewsDashboardTableMap::DATABASE_NAME);
            $criteria->add(NewsDashboardTableMap::COL_NEWS_NW_ID, (array) $values, Criteria::IN);
        }

        $query = NewsDashboardQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            NewsDashboardTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                NewsDashboardTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the news_nw table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return NewsDashboardQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a NewsDashboard or Criteria object.
     *
     * @param mixed $criteria Criteria or NewsDashboard object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(NewsDashboardTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from NewsDashboard object
        }

        if ($criteria->containsKey(NewsDashboardTableMap::COL_NEWS_NW_ID) && $criteria->keyContainsValue(NewsDashboardTableMap::COL_NEWS_NW_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.NewsDashboardTableMap::COL_NEWS_NW_ID.')');
        }


        // Set the correct dbName
        $query = NewsDashboardQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
