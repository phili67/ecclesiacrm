<?php

namespace PluginStore\Base;

use \Exception;
use \PDO;
use PluginStore\ToDoListDashboard as ChildToDoListDashboard;
use PluginStore\ToDoListDashboardQuery as ChildToDoListDashboardQuery;
use PluginStore\Map\ToDoListDashboardTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the `tdl_list` table.
 *
 * @method     ChildToDoListDashboardQuery orderById($order = Criteria::ASC) Order by the tdl_l_id column
 * @method     ChildToDoListDashboardQuery orderByName($order = Criteria::ASC) Order by the tdl_l_name column
 * @method     ChildToDoListDashboardQuery orderByUserId($order = Criteria::ASC) Order by the tdl_l_user_id column
 * @method     ChildToDoListDashboardQuery orderByVisible($order = Criteria::ASC) Order by the tdl_l_visible column
 *
 * @method     ChildToDoListDashboardQuery groupById() Group by the tdl_l_id column
 * @method     ChildToDoListDashboardQuery groupByName() Group by the tdl_l_name column
 * @method     ChildToDoListDashboardQuery groupByUserId() Group by the tdl_l_user_id column
 * @method     ChildToDoListDashboardQuery groupByVisible() Group by the tdl_l_visible column
 *
 * @method     ChildToDoListDashboardQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildToDoListDashboardQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildToDoListDashboardQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildToDoListDashboardQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildToDoListDashboardQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildToDoListDashboardQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildToDoListDashboard|null findOne(?ConnectionInterface $con = null) Return the first ChildToDoListDashboard matching the query
 * @method     ChildToDoListDashboard findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildToDoListDashboard matching the query, or a new ChildToDoListDashboard object populated from the query conditions when no match is found
 *
 * @method     ChildToDoListDashboard|null findOneById(int $tdl_l_id) Return the first ChildToDoListDashboard filtered by the tdl_l_id column
 * @method     ChildToDoListDashboard|null findOneByName(string $tdl_l_name) Return the first ChildToDoListDashboard filtered by the tdl_l_name column
 * @method     ChildToDoListDashboard|null findOneByUserId(int $tdl_l_user_id) Return the first ChildToDoListDashboard filtered by the tdl_l_user_id column
 * @method     ChildToDoListDashboard|null findOneByVisible(boolean $tdl_l_visible) Return the first ChildToDoListDashboard filtered by the tdl_l_visible column
 *
 * @method     ChildToDoListDashboard requirePk($key, ?ConnectionInterface $con = null) Return the ChildToDoListDashboard by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboard requireOne(?ConnectionInterface $con = null) Return the first ChildToDoListDashboard matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildToDoListDashboard requireOneById(int $tdl_l_id) Return the first ChildToDoListDashboard filtered by the tdl_l_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboard requireOneByName(string $tdl_l_name) Return the first ChildToDoListDashboard filtered by the tdl_l_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboard requireOneByUserId(int $tdl_l_user_id) Return the first ChildToDoListDashboard filtered by the tdl_l_user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildToDoListDashboard requireOneByVisible(boolean $tdl_l_visible) Return the first ChildToDoListDashboard filtered by the tdl_l_visible column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildToDoListDashboard[]|Collection find(?ConnectionInterface $con = null) Return ChildToDoListDashboard objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildToDoListDashboard> find(?ConnectionInterface $con = null) Return ChildToDoListDashboard objects based on current ModelCriteria
 *
 * @method     ChildToDoListDashboard[]|Collection findById(int|array<int> $tdl_l_id) Return ChildToDoListDashboard objects filtered by the tdl_l_id column
 * @psalm-method Collection&\Traversable<ChildToDoListDashboard> findById(int|array<int> $tdl_l_id) Return ChildToDoListDashboard objects filtered by the tdl_l_id column
 * @method     ChildToDoListDashboard[]|Collection findByName(string|array<string> $tdl_l_name) Return ChildToDoListDashboard objects filtered by the tdl_l_name column
 * @psalm-method Collection&\Traversable<ChildToDoListDashboard> findByName(string|array<string> $tdl_l_name) Return ChildToDoListDashboard objects filtered by the tdl_l_name column
 * @method     ChildToDoListDashboard[]|Collection findByUserId(int|array<int> $tdl_l_user_id) Return ChildToDoListDashboard objects filtered by the tdl_l_user_id column
 * @psalm-method Collection&\Traversable<ChildToDoListDashboard> findByUserId(int|array<int> $tdl_l_user_id) Return ChildToDoListDashboard objects filtered by the tdl_l_user_id column
 * @method     ChildToDoListDashboard[]|Collection findByVisible(boolean|array<boolean> $tdl_l_visible) Return ChildToDoListDashboard objects filtered by the tdl_l_visible column
 * @psalm-method Collection&\Traversable<ChildToDoListDashboard> findByVisible(boolean|array<boolean> $tdl_l_visible) Return ChildToDoListDashboard objects filtered by the tdl_l_visible column
 *
 * @method     ChildToDoListDashboard[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildToDoListDashboard> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ToDoListDashboardQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PluginStore\Base\ToDoListDashboardQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'pluginstore', $modelName = '\\PluginStore\\ToDoListDashboard', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildToDoListDashboardQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildToDoListDashboardQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildToDoListDashboardQuery) {
            return $criteria;
        }
        $query = new ChildToDoListDashboardQuery();
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
     * @return ChildToDoListDashboard|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ToDoListDashboardTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ToDoListDashboardTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildToDoListDashboard A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT tdl_l_id, tdl_l_name, tdl_l_user_id, tdl_l_visible FROM tdl_list WHERE tdl_l_id = :p0';
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
            /** @var ChildToDoListDashboard $obj */
            $obj = new ChildToDoListDashboard();
            $obj->hydrate($row);
            ToDoListDashboardTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @return ChildToDoListDashboard|array|mixed the result, formatted by the current formatter
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
     * @param array $keys Primary keys to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return Collection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ?ConnectionInterface $con = null)
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
     * @param mixed $key Primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_ID, $key, Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param array|int $keys The list of primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the tdl_l_id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE tdl_l_id = 1234
     * $query->filterById(array(12, 34)); // WHERE tdl_l_id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE tdl_l_id > 12
     * </code>
     *
     * @param mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterById($id = null, ?string $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the tdl_l_name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE tdl_l_name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE tdl_l_name LIKE '%fooValue%'
     * $query->filterByName(['foo', 'bar']); // WHERE tdl_l_name IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $name The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByName($name = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_NAME, $name, $comparison);

        return $this;
    }

    /**
     * Filter the query on the tdl_l_user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserId(1234); // WHERE tdl_l_user_id = 1234
     * $query->filterByUserId(array(12, 34)); // WHERE tdl_l_user_id IN (12, 34)
     * $query->filterByUserId(array('min' => 12)); // WHERE tdl_l_user_id > 12
     * </code>
     *
     * @param mixed $userId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByUserId($userId = null, ?string $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_USER_ID, $userId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the tdl_l_visible column
     *
     * Example usage:
     * <code>
     * $query->filterByVisible(true); // WHERE tdl_l_visible = true
     * $query->filterByVisible('yes'); // WHERE tdl_l_visible = true
     * </code>
     *
     * @param bool|string $visible The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByVisible($visible = null, ?string $comparison = null)
    {
        if (is_string($visible)) {
            $visible = in_array(strtolower($visible), array('false', 'off', '-', 'no', 'n', '0', ''), true) ? false : true;
        }

        $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_VISIBLE, $visible, $comparison);

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildToDoListDashboard $toDoListDashboard Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($toDoListDashboard = null)
    {
        if ($toDoListDashboard) {
            $this->addUsingAlias(ToDoListDashboardTableMap::COL_TDL_L_ID, $toDoListDashboard->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the tdl_list table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ToDoListDashboardTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ToDoListDashboardTableMap::clearInstancePool();
            ToDoListDashboardTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ToDoListDashboardTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ToDoListDashboardTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ToDoListDashboardTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ToDoListDashboardTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
