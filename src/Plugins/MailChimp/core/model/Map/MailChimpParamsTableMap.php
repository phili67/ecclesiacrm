<?php

namespace PluginStore\Map;

use PluginStore\MailChimpParams;
use PluginStore\MailChimpParamsQuery;
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
 * This class defines the structure of the 'mc_params' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class MailChimpParamsTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'PluginStore.Map.MailChimpParamsTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'pluginstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'mc_params';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'MailChimpParams';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PluginStore\\MailChimpParams';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'PluginStore.MailChimpParams';

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
     * the column name for the mc_p_id field
     */
    public const COL_MC_P_ID = 'mc_params.mc_p_id';

    /**
     * the column name for the mc_p_api_key field
     */
    public const COL_MC_P_API_KEY = 'mc_params.mc_p_api_key';

    /**
     * the column name for the mc_p_request_timeout field
     */
    public const COL_MC_P_REQUEST_TIMEOUT = 'mc_params.mc_p_request_timeout';

    /**
     * the column name for the mc_p_with_address_phone field
     */
    public const COL_MC_P_WITH_ADDRESS_PHONE = 'mc_params.mc_p_with_address_phone';

    /**
     * the column name for the mc_p_email_sender field
     */
    public const COL_MC_P_EMAIL_SENDER = 'mc_params.mc_p_email_sender';

    /**
     * the column name for the mc_p_contents_external_css_font field
     */
    public const COL_MC_P_CONTENTS_EXTERNAL_CSS_FONT = 'mc_params.mc_p_contents_external_css_font';

    /**
     * the column name for the mc_p_extra_font field
     */
    public const COL_MC_P_EXTRA_FONT = 'mc_params.mc_p_extra_font';

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
        self::TYPE_PHPNAME       => ['Id', 'ApiKey', 'RequestTimeout', 'WithAddressPhone', 'EmailSender', 'ContentsExternalCssFont', 'ExtraFont', ],
        self::TYPE_CAMELNAME     => ['id', 'apiKey', 'requestTimeout', 'withAddressPhone', 'emailSender', 'contentsExternalCssFont', 'extraFont', ],
        self::TYPE_COLNAME       => [MailChimpParamsTableMap::COL_MC_P_ID, MailChimpParamsTableMap::COL_MC_P_API_KEY, MailChimpParamsTableMap::COL_MC_P_REQUEST_TIMEOUT, MailChimpParamsTableMap::COL_MC_P_WITH_ADDRESS_PHONE, MailChimpParamsTableMap::COL_MC_P_EMAIL_SENDER, MailChimpParamsTableMap::COL_MC_P_CONTENTS_EXTERNAL_CSS_FONT, MailChimpParamsTableMap::COL_MC_P_EXTRA_FONT, ],
        self::TYPE_FIELDNAME     => ['mc_p_id', 'mc_p_api_key', 'mc_p_request_timeout', 'mc_p_with_address_phone', 'mc_p_email_sender', 'mc_p_contents_external_css_font', 'mc_p_extra_font', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'ApiKey' => 1, 'RequestTimeout' => 2, 'WithAddressPhone' => 3, 'EmailSender' => 4, 'ContentsExternalCssFont' => 5, 'ExtraFont' => 6, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'apiKey' => 1, 'requestTimeout' => 2, 'withAddressPhone' => 3, 'emailSender' => 4, 'contentsExternalCssFont' => 5, 'extraFont' => 6, ],
        self::TYPE_COLNAME       => [MailChimpParamsTableMap::COL_MC_P_ID => 0, MailChimpParamsTableMap::COL_MC_P_API_KEY => 1, MailChimpParamsTableMap::COL_MC_P_REQUEST_TIMEOUT => 2, MailChimpParamsTableMap::COL_MC_P_WITH_ADDRESS_PHONE => 3, MailChimpParamsTableMap::COL_MC_P_EMAIL_SENDER => 4, MailChimpParamsTableMap::COL_MC_P_CONTENTS_EXTERNAL_CSS_FONT => 5, MailChimpParamsTableMap::COL_MC_P_EXTRA_FONT => 6, ],
        self::TYPE_FIELDNAME     => ['mc_p_id' => 0, 'mc_p_api_key' => 1, 'mc_p_request_timeout' => 2, 'mc_p_with_address_phone' => 3, 'mc_p_email_sender' => 4, 'mc_p_contents_external_css_font' => 5, 'mc_p_extra_font' => 6, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'MC_P_ID',
        'MailChimpParams.Id' => 'MC_P_ID',
        'id' => 'MC_P_ID',
        'mailChimpParams.id' => 'MC_P_ID',
        'MailChimpParamsTableMap::COL_MC_P_ID' => 'MC_P_ID',
        'COL_MC_P_ID' => 'MC_P_ID',
        'mc_p_id' => 'MC_P_ID',
        'mc_params.mc_p_id' => 'MC_P_ID',
        'ApiKey' => 'MC_P_API_KEY',
        'MailChimpParams.ApiKey' => 'MC_P_API_KEY',
        'apiKey' => 'MC_P_API_KEY',
        'mailChimpParams.apiKey' => 'MC_P_API_KEY',
        'MailChimpParamsTableMap::COL_MC_P_API_KEY' => 'MC_P_API_KEY',
        'COL_MC_P_API_KEY' => 'MC_P_API_KEY',
        'mc_p_api_key' => 'MC_P_API_KEY',
        'mc_params.mc_p_api_key' => 'MC_P_API_KEY',
        'RequestTimeout' => 'MC_P_REQUEST_TIMEOUT',
        'MailChimpParams.RequestTimeout' => 'MC_P_REQUEST_TIMEOUT',
        'requestTimeout' => 'MC_P_REQUEST_TIMEOUT',
        'mailChimpParams.requestTimeout' => 'MC_P_REQUEST_TIMEOUT',
        'MailChimpParamsTableMap::COL_MC_P_REQUEST_TIMEOUT' => 'MC_P_REQUEST_TIMEOUT',
        'COL_MC_P_REQUEST_TIMEOUT' => 'MC_P_REQUEST_TIMEOUT',
        'mc_p_request_timeout' => 'MC_P_REQUEST_TIMEOUT',
        'mc_params.mc_p_request_timeout' => 'MC_P_REQUEST_TIMEOUT',
        'WithAddressPhone' => 'MC_P_WITH_ADDRESS_PHONE',
        'MailChimpParams.WithAddressPhone' => 'MC_P_WITH_ADDRESS_PHONE',
        'withAddressPhone' => 'MC_P_WITH_ADDRESS_PHONE',
        'mailChimpParams.withAddressPhone' => 'MC_P_WITH_ADDRESS_PHONE',
        'MailChimpParamsTableMap::COL_MC_P_WITH_ADDRESS_PHONE' => 'MC_P_WITH_ADDRESS_PHONE',
        'COL_MC_P_WITH_ADDRESS_PHONE' => 'MC_P_WITH_ADDRESS_PHONE',
        'mc_p_with_address_phone' => 'MC_P_WITH_ADDRESS_PHONE',
        'mc_params.mc_p_with_address_phone' => 'MC_P_WITH_ADDRESS_PHONE',
        'EmailSender' => 'MC_P_EMAIL_SENDER',
        'MailChimpParams.EmailSender' => 'MC_P_EMAIL_SENDER',
        'emailSender' => 'MC_P_EMAIL_SENDER',
        'mailChimpParams.emailSender' => 'MC_P_EMAIL_SENDER',
        'MailChimpParamsTableMap::COL_MC_P_EMAIL_SENDER' => 'MC_P_EMAIL_SENDER',
        'COL_MC_P_EMAIL_SENDER' => 'MC_P_EMAIL_SENDER',
        'mc_p_email_sender' => 'MC_P_EMAIL_SENDER',
        'mc_params.mc_p_email_sender' => 'MC_P_EMAIL_SENDER',
        'ContentsExternalCssFont' => 'MC_P_CONTENTS_EXTERNAL_CSS_FONT',
        'MailChimpParams.ContentsExternalCssFont' => 'MC_P_CONTENTS_EXTERNAL_CSS_FONT',
        'contentsExternalCssFont' => 'MC_P_CONTENTS_EXTERNAL_CSS_FONT',
        'mailChimpParams.contentsExternalCssFont' => 'MC_P_CONTENTS_EXTERNAL_CSS_FONT',
        'MailChimpParamsTableMap::COL_MC_P_CONTENTS_EXTERNAL_CSS_FONT' => 'MC_P_CONTENTS_EXTERNAL_CSS_FONT',
        'COL_MC_P_CONTENTS_EXTERNAL_CSS_FONT' => 'MC_P_CONTENTS_EXTERNAL_CSS_FONT',
        'mc_p_contents_external_css_font' => 'MC_P_CONTENTS_EXTERNAL_CSS_FONT',
        'mc_params.mc_p_contents_external_css_font' => 'MC_P_CONTENTS_EXTERNAL_CSS_FONT',
        'ExtraFont' => 'MC_P_EXTRA_FONT',
        'MailChimpParams.ExtraFont' => 'MC_P_EXTRA_FONT',
        'extraFont' => 'MC_P_EXTRA_FONT',
        'mailChimpParams.extraFont' => 'MC_P_EXTRA_FONT',
        'MailChimpParamsTableMap::COL_MC_P_EXTRA_FONT' => 'MC_P_EXTRA_FONT',
        'COL_MC_P_EXTRA_FONT' => 'MC_P_EXTRA_FONT',
        'mc_p_extra_font' => 'MC_P_EXTRA_FONT',
        'mc_params.mc_p_extra_font' => 'MC_P_EXTRA_FONT',
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
        $this->setName('mc_params');
        $this->setPhpName('MailChimpParams');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PluginStore\\MailChimpParams');
        $this->setPackage('PluginStore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('mc_p_id', 'Id', 'SMALLINT', true, 9, null);
        $this->addColumn('mc_p_api_key', 'ApiKey', 'VARCHAR', true, 255, '');
        $this->addColumn('mc_p_request_timeout', 'RequestTimeout', 'INTEGER', true, null, 3600);
        $this->addColumn('mc_p_with_address_phone', 'WithAddressPhone', 'BOOLEAN', true, 1, false);
        $this->addColumn('mc_p_email_sender', 'EmailSender', 'VARCHAR', true, 255, '');
        $this->addColumn('mc_p_contents_external_css_font', 'ContentsExternalCssFont', 'LONGVARCHAR', false, null, null);
        $this->addColumn('mc_p_extra_font', 'ExtraFont', 'LONGVARCHAR', false, null, null);
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
        return $withPrefix ? MailChimpParamsTableMap::CLASS_DEFAULT : MailChimpParamsTableMap::OM_CLASS;
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
     * @return array (MailChimpParams object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = MailChimpParamsTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = MailChimpParamsTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + MailChimpParamsTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = MailChimpParamsTableMap::OM_CLASS;
            /** @var MailChimpParams $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            MailChimpParamsTableMap::addInstanceToPool($obj, $key);
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
            $key = MailChimpParamsTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = MailChimpParamsTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var MailChimpParams $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                MailChimpParamsTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(MailChimpParamsTableMap::COL_MC_P_ID);
            $criteria->addSelectColumn(MailChimpParamsTableMap::COL_MC_P_API_KEY);
            $criteria->addSelectColumn(MailChimpParamsTableMap::COL_MC_P_REQUEST_TIMEOUT);
            $criteria->addSelectColumn(MailChimpParamsTableMap::COL_MC_P_WITH_ADDRESS_PHONE);
            $criteria->addSelectColumn(MailChimpParamsTableMap::COL_MC_P_EMAIL_SENDER);
            $criteria->addSelectColumn(MailChimpParamsTableMap::COL_MC_P_CONTENTS_EXTERNAL_CSS_FONT);
            $criteria->addSelectColumn(MailChimpParamsTableMap::COL_MC_P_EXTRA_FONT);
        } else {
            $criteria->addSelectColumn($alias . '.mc_p_id');
            $criteria->addSelectColumn($alias . '.mc_p_api_key');
            $criteria->addSelectColumn($alias . '.mc_p_request_timeout');
            $criteria->addSelectColumn($alias . '.mc_p_with_address_phone');
            $criteria->addSelectColumn($alias . '.mc_p_email_sender');
            $criteria->addSelectColumn($alias . '.mc_p_contents_external_css_font');
            $criteria->addSelectColumn($alias . '.mc_p_extra_font');
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
            $criteria->removeSelectColumn(MailChimpParamsTableMap::COL_MC_P_ID);
            $criteria->removeSelectColumn(MailChimpParamsTableMap::COL_MC_P_API_KEY);
            $criteria->removeSelectColumn(MailChimpParamsTableMap::COL_MC_P_REQUEST_TIMEOUT);
            $criteria->removeSelectColumn(MailChimpParamsTableMap::COL_MC_P_WITH_ADDRESS_PHONE);
            $criteria->removeSelectColumn(MailChimpParamsTableMap::COL_MC_P_EMAIL_SENDER);
            $criteria->removeSelectColumn(MailChimpParamsTableMap::COL_MC_P_CONTENTS_EXTERNAL_CSS_FONT);
            $criteria->removeSelectColumn(MailChimpParamsTableMap::COL_MC_P_EXTRA_FONT);
        } else {
            $criteria->removeSelectColumn($alias . '.mc_p_id');
            $criteria->removeSelectColumn($alias . '.mc_p_api_key');
            $criteria->removeSelectColumn($alias . '.mc_p_request_timeout');
            $criteria->removeSelectColumn($alias . '.mc_p_with_address_phone');
            $criteria->removeSelectColumn($alias . '.mc_p_email_sender');
            $criteria->removeSelectColumn($alias . '.mc_p_contents_external_css_font');
            $criteria->removeSelectColumn($alias . '.mc_p_extra_font');
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
        return Propel::getServiceContainer()->getDatabaseMap(MailChimpParamsTableMap::DATABASE_NAME)->getTable(MailChimpParamsTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a MailChimpParams or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or MailChimpParams object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(MailChimpParamsTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PluginStore\MailChimpParams) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(MailChimpParamsTableMap::DATABASE_NAME);
            $criteria->add(MailChimpParamsTableMap::COL_MC_P_ID, (array) $values, Criteria::IN);
        }

        $query = MailChimpParamsQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            MailChimpParamsTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                MailChimpParamsTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the mc_params table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return MailChimpParamsQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a MailChimpParams or Criteria object.
     *
     * @param mixed $criteria Criteria or MailChimpParams object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(MailChimpParamsTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from MailChimpParams object
        }

        if ($criteria->containsKey(MailChimpParamsTableMap::COL_MC_P_ID) && $criteria->keyContainsValue(MailChimpParamsTableMap::COL_MC_P_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.MailChimpParamsTableMap::COL_MC_P_ID.')');
        }


        // Set the correct dbName
        $query = MailChimpParamsQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
