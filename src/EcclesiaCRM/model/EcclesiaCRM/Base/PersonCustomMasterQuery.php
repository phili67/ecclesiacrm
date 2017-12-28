<?php

namespace EcclesiaCRM\Base;

use \Exception;
use \PDO;
use EcclesiaCRM\PersonCustomMaster as ChildPersonCustomMaster;
use EcclesiaCRM\PersonCustomMasterQuery as ChildPersonCustomMasterQuery;
use EcclesiaCRM\Map\PersonCustomMasterTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'person_custom_master' table.
 *
 * This contains definitions for the custom person fields
 *
 * @method     ChildPersonCustomMasterQuery orderByOrder($order = Criteria::ASC) Order by the custom_Order column
 * @method     ChildPersonCustomMasterQuery orderById($order = Criteria::ASC) Order by the custom_Field column
 * @method     ChildPersonCustomMasterQuery orderByName($order = Criteria::ASC) Order by the custom_Name column
 * @method     ChildPersonCustomMasterQuery orderBySpecial($order = Criteria::ASC) Order by the custom_Special column
 * @method     ChildPersonCustomMasterQuery orderBySide($order = Criteria::ASC) Order by the custom_Side column
 * @method     ChildPersonCustomMasterQuery orderByCustomFieldSec($order = Criteria::ASC) Order by the custom_FieldSec column
 * @method     ChildPersonCustomMasterQuery orderByTypeId($order = Criteria::ASC) Order by the type_ID column
 *
 * @method     ChildPersonCustomMasterQuery groupByOrder() Group by the custom_Order column
 * @method     ChildPersonCustomMasterQuery groupById() Group by the custom_Field column
 * @method     ChildPersonCustomMasterQuery groupByName() Group by the custom_Name column
 * @method     ChildPersonCustomMasterQuery groupBySpecial() Group by the custom_Special column
 * @method     ChildPersonCustomMasterQuery groupBySide() Group by the custom_Side column
 * @method     ChildPersonCustomMasterQuery groupByCustomFieldSec() Group by the custom_FieldSec column
 * @method     ChildPersonCustomMasterQuery groupByTypeId() Group by the type_ID column
 *
 * @method     ChildPersonCustomMasterQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPersonCustomMasterQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPersonCustomMasterQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPersonCustomMasterQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildPersonCustomMasterQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildPersonCustomMasterQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildPersonCustomMaster findOne(ConnectionInterface $con = null) Return the first ChildPersonCustomMaster matching the query
 * @method     ChildPersonCustomMaster findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPersonCustomMaster matching the query, or a new ChildPersonCustomMaster object populated from the query conditions when no match is found
 *
 * @method     ChildPersonCustomMaster findOneByOrder(int $custom_Order) Return the first ChildPersonCustomMaster filtered by the custom_Order column
 * @method     ChildPersonCustomMaster findOneById(string $custom_Field) Return the first ChildPersonCustomMaster filtered by the custom_Field column
 * @method     ChildPersonCustomMaster findOneByName(string $custom_Name) Return the first ChildPersonCustomMaster filtered by the custom_Name column
 * @method     ChildPersonCustomMaster findOneBySpecial(int $custom_Special) Return the first ChildPersonCustomMaster filtered by the custom_Special column
 * @method     ChildPersonCustomMaster findOneBySide(string $custom_Side) Return the first ChildPersonCustomMaster filtered by the custom_Side column
 * @method     ChildPersonCustomMaster findOneByCustomFieldSec(int $custom_FieldSec) Return the first ChildPersonCustomMaster filtered by the custom_FieldSec column
 * @method     ChildPersonCustomMaster findOneByTypeId(int $type_ID) Return the first ChildPersonCustomMaster filtered by the type_ID column *

 * @method     ChildPersonCustomMaster requirePk($key, ConnectionInterface $con = null) Return the ChildPersonCustomMaster by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonCustomMaster requireOne(ConnectionInterface $con = null) Return the first ChildPersonCustomMaster matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPersonCustomMaster requireOneByOrder(int $custom_Order) Return the first ChildPersonCustomMaster filtered by the custom_Order column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonCustomMaster requireOneById(string $custom_Field) Return the first ChildPersonCustomMaster filtered by the custom_Field column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonCustomMaster requireOneByName(string $custom_Name) Return the first ChildPersonCustomMaster filtered by the custom_Name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonCustomMaster requireOneBySpecial(int $custom_Special) Return the first ChildPersonCustomMaster filtered by the custom_Special column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonCustomMaster requireOneBySide(string $custom_Side) Return the first ChildPersonCustomMaster filtered by the custom_Side column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonCustomMaster requireOneByCustomFieldSec(int $custom_FieldSec) Return the first ChildPersonCustomMaster filtered by the custom_FieldSec column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonCustomMaster requireOneByTypeId(int $type_ID) Return the first ChildPersonCustomMaster filtered by the type_ID column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPersonCustomMaster[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildPersonCustomMaster objects based on current ModelCriteria
 * @method     ChildPersonCustomMaster[]|ObjectCollection findByOrder(int $custom_Order) Return ChildPersonCustomMaster objects filtered by the custom_Order column
 * @method     ChildPersonCustomMaster[]|ObjectCollection findById(string $custom_Field) Return ChildPersonCustomMaster objects filtered by the custom_Field column
 * @method     ChildPersonCustomMaster[]|ObjectCollection findByName(string $custom_Name) Return ChildPersonCustomMaster objects filtered by the custom_Name column
 * @method     ChildPersonCustomMaster[]|ObjectCollection findBySpecial(int $custom_Special) Return ChildPersonCustomMaster objects filtered by the custom_Special column
 * @method     ChildPersonCustomMaster[]|ObjectCollection findBySide(string $custom_Side) Return ChildPersonCustomMaster objects filtered by the custom_Side column
 * @method     ChildPersonCustomMaster[]|ObjectCollection findByCustomFieldSec(int $custom_FieldSec) Return ChildPersonCustomMaster objects filtered by the custom_FieldSec column
 * @method     ChildPersonCustomMaster[]|ObjectCollection findByTypeId(int $type_ID) Return ChildPersonCustomMaster objects filtered by the type_ID column
 * @method     ChildPersonCustomMaster[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class PersonCustomMasterQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \EcclesiaCRM\Base\PersonCustomMasterQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\EcclesiaCRM\\PersonCustomMaster', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPersonCustomMasterQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPersonCustomMasterQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildPersonCustomMasterQuery) {
            return $criteria;
        }
        $query = new ChildPersonCustomMasterQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildPersonCustomMaster|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PersonCustomMasterTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = PersonCustomMasterTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildPersonCustomMaster A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT custom_Order, custom_Field, custom_Name, custom_Special, custom_Side, custom_FieldSec, type_ID FROM person_custom_master WHERE custom_Field = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildPersonCustomMaster $obj */
            $obj = new ChildPersonCustomMaster();
            $obj->hydrate($row);
            PersonCustomMasterTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildPersonCustomMaster|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_FIELD, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_FIELD, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the custom_Order column
     *
     * Example usage:
     * <code>
     * $query->filterByOrder(1234); // WHERE custom_Order = 1234
     * $query->filterByOrder(array(12, 34)); // WHERE custom_Order IN (12, 34)
     * $query->filterByOrder(array('min' => 12)); // WHERE custom_Order > 12
     * </code>
     *
     * @param     mixed $order The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function filterByOrder($order = null, $comparison = null)
    {
        if (is_array($order)) {
            $useMinMax = false;
            if (isset($order['min'])) {
                $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_ORDER, $order['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($order['max'])) {
                $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_ORDER, $order['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_ORDER, $order, $comparison);
    }

    /**
     * Filter the query on the custom_Field column
     *
     * Example usage:
     * <code>
     * $query->filterById('fooValue');   // WHERE custom_Field = 'fooValue'
     * $query->filterById('%fooValue%', Criteria::LIKE); // WHERE custom_Field LIKE '%fooValue%'
     * </code>
     *
     * @param     string $id The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($id)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_FIELD, $id, $comparison);
    }

    /**
     * Filter the query on the custom_Name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE custom_Name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE custom_Name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_NAME, $name, $comparison);
    }

    /**
     * Filter the query on the custom_Special column
     *
     * Example usage:
     * <code>
     * $query->filterBySpecial(1234); // WHERE custom_Special = 1234
     * $query->filterBySpecial(array(12, 34)); // WHERE custom_Special IN (12, 34)
     * $query->filterBySpecial(array('min' => 12)); // WHERE custom_Special > 12
     * </code>
     *
     * @param     mixed $special The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function filterBySpecial($special = null, $comparison = null)
    {
        if (is_array($special)) {
            $useMinMax = false;
            if (isset($special['min'])) {
                $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_SPECIAL, $special['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($special['max'])) {
                $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_SPECIAL, $special['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_SPECIAL, $special, $comparison);
    }

    /**
     * Filter the query on the custom_Side column
     *
     * Example usage:
     * <code>
     * $query->filterBySide('fooValue');   // WHERE custom_Side = 'fooValue'
     * $query->filterBySide('%fooValue%', Criteria::LIKE); // WHERE custom_Side LIKE '%fooValue%'
     * </code>
     *
     * @param     string $side The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function filterBySide($side = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($side)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_SIDE, $side, $comparison);
    }

    /**
     * Filter the query on the custom_FieldSec column
     *
     * Example usage:
     * <code>
     * $query->filterByCustomFieldSec(1234); // WHERE custom_FieldSec = 1234
     * $query->filterByCustomFieldSec(array(12, 34)); // WHERE custom_FieldSec IN (12, 34)
     * $query->filterByCustomFieldSec(array('min' => 12)); // WHERE custom_FieldSec > 12
     * </code>
     *
     * @param     mixed $customFieldSec The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function filterByCustomFieldSec($customFieldSec = null, $comparison = null)
    {
        if (is_array($customFieldSec)) {
            $useMinMax = false;
            if (isset($customFieldSec['min'])) {
                $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_FIELDSEC, $customFieldSec['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($customFieldSec['max'])) {
                $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_FIELDSEC, $customFieldSec['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_FIELDSEC, $customFieldSec, $comparison);
    }

    /**
     * Filter the query on the type_ID column
     *
     * Example usage:
     * <code>
     * $query->filterByTypeId(1234); // WHERE type_ID = 1234
     * $query->filterByTypeId(array(12, 34)); // WHERE type_ID IN (12, 34)
     * $query->filterByTypeId(array('min' => 12)); // WHERE type_ID > 12
     * </code>
     *
     * @param     mixed $typeId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function filterByTypeId($typeId = null, $comparison = null)
    {
        if (is_array($typeId)) {
            $useMinMax = false;
            if (isset($typeId['min'])) {
                $this->addUsingAlias(PersonCustomMasterTableMap::COL_TYPE_ID, $typeId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($typeId['max'])) {
                $this->addUsingAlias(PersonCustomMasterTableMap::COL_TYPE_ID, $typeId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonCustomMasterTableMap::COL_TYPE_ID, $typeId, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildPersonCustomMaster $personCustomMaster Object to remove from the list of results
     *
     * @return $this|ChildPersonCustomMasterQuery The current query, for fluid interface
     */
    public function prune($personCustomMaster = null)
    {
        if ($personCustomMaster) {
            $this->addUsingAlias(PersonCustomMasterTableMap::COL_CUSTOM_FIELD, $personCustomMaster->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the person_custom_master table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PersonCustomMasterTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            PersonCustomMasterTableMap::clearInstancePool();
            PersonCustomMasterTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PersonCustomMasterTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PersonCustomMasterTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            PersonCustomMasterTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PersonCustomMasterTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // PersonCustomMasterQuery
