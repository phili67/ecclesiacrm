<?php

namespace PluginStore\Base;

use \Exception;
use \PDO;
use PluginStore\ToDoListDashboardItem as ChildToDoListDashboardItem;
use PluginStore\ToDoListDashboardItemQuery as ChildToDoListDashboardItemQuery;
use PluginStore\Map\ToDoListDashboardItemTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'tdl_l_item' table.
 *
 *
 *
 * @method     ChildToDoListDashboardItemQuery orderById($order = Criteria::ASC) Order by the tdl_l_i_id column
 * @method     ChildToDoListDashboardItemQuery orderByList($order = Criteria::ASC) Order by the tdl_l_i_list column
 * @method     ChildToDoListDashboardItemQuery orderByChecked($order = Criteria::ASC) Order by the tdl_l_i_checked column
 * @method     ChildToDoListDashboardItemQuery orderByName($order = Criteria::ASC) Order by the tdl_l_i_name column
 * @method     ChildToDoListDashboardItemQuery orderByDateTime($order = Criteria::ASC) Order by the tdl_l_i_date_time column
 * @method     ChildToDoListDashboardItemQuery orderByPlace($order = Criteria::ASC) Order by the tdl_l_i_place column
 *
 * @method     ChildToDoListDashboardItemQuery groupById() Group by the tdl_l_i_id column
 * @method     ChildToDoListDashboardItemQuery groupByList() Group by the tdl_l_i_list column
 * @method     ChildToDoListDashboardItemQuery groupByChecked() Group by the tdl_l_i_checked column
 * @method     ChildToDoListDashboardItemQuery groupByName() Group by the tdl_l_i_name column
 * @method     ChildToDoListDashboardItemQuery groupByDateTime() Group by the tdl_l_i_date_time column
 * @method     ChildToDoListDashboardItemQuery groupByPlace() Group by the tdl_l_i_place column
 *
 * @method     ChildToDoListDashboardItemQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildToDoListDashboardItemQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildToDoListDashboardItemQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildToDoListDashboardItemQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildToDoListDashboardItemQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildToDoListDashboardItemQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildToDoListDashboardItem|null findOne(ConnectionInterface $con = null) Return the first ChildToDoListDashboardItem matching the query
 * @method     ChildToDoListDashboardItem findOneOrCreate(ConnectionInterface $con = null) Return the first ChildToDoListDashboardItem matching the query, or a new ChildToDoListDashboardItem object populated from the query conditions when no match is found
 *
 * @method     ChildToDoListDashboardItem|null findOneById(int $tdl_l_i_id) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_id column
 * @method     ChildToDoListDashboardItem|null findOneByList(int $tdl_l_i_list) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_list column
 * @method     ChildToDoListDashboardItem|null findOneByChecked(boolean $tdl_l_i_checked) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_checked column
 * @method     ChildToDoListDashboardItem|null findOneByName(string $tdl_l_i_name) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_name column
 * @method     ChildToDoListDashboardItem|null findOneByDateTime(string $tdl_l_i_date_time) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_date_time column
 * @method     ChildToDoListDashboardItem|null findOneByPlace(int $tdl_l_i_place) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_place column *

 * @method     ChildToDoListDashboardItem requirePk($key, ConnectionInterface $con = null) Return the ChildToDoListDashboardItem by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboardItem requireOne(ConnectionInterface $con = null) Return the first ChildToDoListDashboardItem matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildToDoListDashboardItem requireOneById(int $tdl_l_i_id) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboardItem requireOneByList(int $tdl_l_i_list) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_list column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboardItem requireOneByChecked(boolean $tdl_l_i_checked) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_checked column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboardItem requireOneByName(string $tdl_l_i_name) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboardItem requireOneByDateTime(string $tdl_l_i_date_time) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_date_time column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboardItem requireOneByPlace(int $tdl_l_i_place) Return the first ChildToDoListDashboardItem filtered by the tdl_l_i_place column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildToDoListDashboardItem[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildToDoListDashboardItem objects based on current ModelCriteria
 * @psalm-method ObjectCollection&\Traversable<ChildToDoListDashboardItem> find(ConnectionInterface $con = null) Return ChildToDoListDashboardItem objects based on current ModelCriteria
 * @method     ChildToDoListDashboardItem[]|ObjectCollection findById(int $tdl_l_i_id) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_id column
 * @psalm-method ObjectCollection&\Traversable<ChildToDoListDashboardItem> findById(int $tdl_l_i_id) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_id column
 * @method     ChildToDoListDashboardItem[]|ObjectCollection findByList(int $tdl_l_i_list) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_list column
 * @psalm-method ObjectCollection&\Traversable<ChildToDoListDashboardItem> findByList(int $tdl_l_i_list) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_list column
 * @method     ChildToDoListDashboardItem[]|ObjectCollection findByChecked(boolean $tdl_l_i_checked) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_checked column
 * @psalm-method ObjectCollection&\Traversable<ChildToDoListDashboardItem> findByChecked(boolean $tdl_l_i_checked) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_checked column
 * @method     ChildToDoListDashboardItem[]|ObjectCollection findByName(string $tdl_l_i_name) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_name column
 * @psalm-method ObjectCollection&\Traversable<ChildToDoListDashboardItem> findByName(string $tdl_l_i_name) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_name column
 * @method     ChildToDoListDashboardItem[]|ObjectCollection findByDateTime(string $tdl_l_i_date_time) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_date_time column
 * @psalm-method ObjectCollection&\Traversable<ChildToDoListDashboardItem> findByDateTime(string $tdl_l_i_date_time) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_date_time column
 * @method     ChildToDoListDashboardItem[]|ObjectCollection findByPlace(int $tdl_l_i_place) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_place column
 * @psalm-method ObjectCollection&\Traversable<ChildToDoListDashboardItem> findByPlace(int $tdl_l_i_place) Return ChildToDoListDashboardItem objects filtered by the tdl_l_i_place column
 * @method     ChildToDoListDashboardItem[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildToDoListDashboardItem> paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class ToDoListDashboardItemQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PluginStore\Base\ToDoListDashboardItemQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'pluginstore', $modelName = '\\PluginStore\\ToDoListDashboardItem', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildToDoListDashboardItemQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildToDoListDashboardItemQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildToDoListDashboardItemQuery) {
            return $criteria;
        }
        $query = new ChildToDoListDashboardItemQuery();
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
     * @return ChildToDoListDashboardItem|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ToDoListDashboardItemTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ToDoListDashboardItemTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildToDoListDashboardItem A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT tdl_l_i_id, tdl_l_i_list, tdl_l_i_checked, tdl_l_i_name, tdl_l_i_date_time, tdl_l_i_place FROM tdl_l_item WHERE tdl_l_i_id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildToDoListDashboardItem $obj */
            $obj = new ChildToDoListDashboardItem();
            $obj->hydrate($row);
            ToDoListDashboardItemTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildToDoListDashboardItem|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildToDoListDashboardItemQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildToDoListDashboardItemQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the tdl_l_i_id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE tdl_l_i_id = 1234
     * $query->filterById(array(12, 34)); // WHERE tdl_l_i_id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE tdl_l_i_id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildToDoListDashboardItemQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID, $id, $comparison);
    }

    /**
     * Filter the query on the tdl_l_i_list column
     *
     * Example usage:
     * <code>
     * $query->filterByList(1234); // WHERE tdl_l_i_list = 1234
     * $query->filterByList(array(12, 34)); // WHERE tdl_l_i_list IN (12, 34)
     * $query->filterByList(array('min' => 12)); // WHERE tdl_l_i_list > 12
     * </code>
     *
     * @param     mixed $list The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildToDoListDashboardItemQuery The current query, for fluid interface
     */
    public function filterByList($list = null, $comparison = null)
    {
        if (is_array($list)) {
            $useMinMax = false;
            if (isset($list['min'])) {
                $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_LIST, $list['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($list['max'])) {
                $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_LIST, $list['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_LIST, $list, $comparison);
    }

    /**
     * Filter the query on the tdl_l_i_checked column
     *
     * Example usage:
     * <code>
     * $query->filterByChecked(true); // WHERE tdl_l_i_checked = true
     * $query->filterByChecked('yes'); // WHERE tdl_l_i_checked = true
     * </code>
     *
     * @param     boolean|string $checked The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildToDoListDashboardItemQuery The current query, for fluid interface
     */
    public function filterByChecked($checked = null, $comparison = null)
    {
        if (is_string($checked)) {
            $checked = in_array(strtolower($checked), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_CHECKED, $checked, $comparison);
    }

    /**
     * Filter the query on the tdl_l_i_name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE tdl_l_i_name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE tdl_l_i_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildToDoListDashboardItemQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_NAME, $name, $comparison);
    }

    /**
     * Filter the query on the tdl_l_i_date_time column
     *
     * Example usage:
     * <code>
     * $query->filterByDateTime('2011-03-14'); // WHERE tdl_l_i_date_time = '2011-03-14'
     * $query->filterByDateTime('now'); // WHERE tdl_l_i_date_time = '2011-03-14'
     * $query->filterByDateTime(array('max' => 'yesterday')); // WHERE tdl_l_i_date_time > '2011-03-13'
     * </code>
     *
     * @param     mixed $dateTime The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildToDoListDashboardItemQuery The current query, for fluid interface
     */
    public function filterByDateTime($dateTime = null, $comparison = null)
    {
        if (is_array($dateTime)) {
            $useMinMax = false;
            if (isset($dateTime['min'])) {
                $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_DATE_TIME, $dateTime['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateTime['max'])) {
                $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_DATE_TIME, $dateTime['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_DATE_TIME, $dateTime, $comparison);
    }

    /**
     * Filter the query on the tdl_l_i_place column
     *
     * Example usage:
     * <code>
     * $query->filterByPlace(1234); // WHERE tdl_l_i_place = 1234
     * $query->filterByPlace(array(12, 34)); // WHERE tdl_l_i_place IN (12, 34)
     * $query->filterByPlace(array('min' => 12)); // WHERE tdl_l_i_place > 12
     * </code>
     *
     * @param     mixed $place The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildToDoListDashboardItemQuery The current query, for fluid interface
     */
    public function filterByPlace($place = null, $comparison = null)
    {
        if (is_array($place)) {
            $useMinMax = false;
            if (isset($place['min'])) {
                $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_PLACE, $place['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($place['max'])) {
                $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_PLACE, $place['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_PLACE, $place, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildToDoListDashboardItem $toDoListDashboardItem Object to remove from the list of results
     *
     * @return $this|ChildToDoListDashboardItemQuery The current query, for fluid interface
     */
    public function prune($toDoListDashboardItem = null)
    {
        if ($toDoListDashboardItem) {
            $this->addUsingAlias(ToDoListDashboardItemTableMap::COL_TDL_L_I_ID, $toDoListDashboardItem->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the tdl_l_item table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ToDoListDashboardItemTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ToDoListDashboardItemTableMap::clearInstancePool();
            ToDoListDashboardItemTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ToDoListDashboardItemTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ToDoListDashboardItemTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ToDoListDashboardItemTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ToDoListDashboardItemTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // ToDoListDashboardItemQuery
