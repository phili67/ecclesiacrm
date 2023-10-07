<?php

namespace PluginStore\Base;

use \Exception;
use \PDO;
use PluginStore\NoteDashboard as ChildNoteDashboard;
use PluginStore\NoteDashboardQuery as ChildNoteDashboardQuery;
use PluginStore\Map\NoteDashboardTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the `NoteDashboard_nd` table.
 *
 * @method     ChildNoteDashboardQuery orderById($order = Criteria::ASC) Order by the nd_id column
 * @method     ChildNoteDashboardQuery orderByUserId($order = Criteria::ASC) Order by the nd_user_id column
 * @method     ChildNoteDashboardQuery orderByNote($order = Criteria::ASC) Order by the nd_note column
 *
 * @method     ChildNoteDashboardQuery groupById() Group by the nd_id column
 * @method     ChildNoteDashboardQuery groupByUserId() Group by the nd_user_id column
 * @method     ChildNoteDashboardQuery groupByNote() Group by the nd_note column
 *
 * @method     ChildNoteDashboardQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildNoteDashboardQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildNoteDashboardQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildNoteDashboardQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildNoteDashboardQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildNoteDashboardQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildNoteDashboard|null findOne(?ConnectionInterface $con = null) Return the first ChildNoteDashboard matching the query
 * @method     ChildNoteDashboard findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildNoteDashboard matching the query, or a new ChildNoteDashboard object populated from the query conditions when no match is found
 *
 * @method     ChildNoteDashboard|null findOneById(int $nd_id) Return the first ChildNoteDashboard filtered by the nd_id column
 * @method     ChildNoteDashboard|null findOneByUserId(int $nd_user_id) Return the first ChildNoteDashboard filtered by the nd_user_id column
 * @method     ChildNoteDashboard|null findOneByNote(string $nd_note) Return the first ChildNoteDashboard filtered by the nd_note column
 *
 * @method     ChildNoteDashboard requirePk($key, ?ConnectionInterface $con = null) Return the ChildNoteDashboard by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNoteDashboard requireOne(?ConnectionInterface $con = null) Return the first ChildNoteDashboard matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildNoteDashboard requireOneById(int $nd_id) Return the first ChildNoteDashboard filtered by the nd_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNoteDashboard requireOneByUserId(int $nd_user_id) Return the first ChildNoteDashboard filtered by the nd_user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNoteDashboard requireOneByNote(string $nd_note) Return the first ChildNoteDashboard filtered by the nd_note column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildNoteDashboard[]|Collection find(?ConnectionInterface $con = null) Return ChildNoteDashboard objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildNoteDashboard> find(?ConnectionInterface $con = null) Return ChildNoteDashboard objects based on current ModelCriteria
 *
 * @method     ChildNoteDashboard[]|Collection findById(int|array<int> $nd_id) Return ChildNoteDashboard objects filtered by the nd_id column
 * @psalm-method Collection&\Traversable<ChildNoteDashboard> findById(int|array<int> $nd_id) Return ChildNoteDashboard objects filtered by the nd_id column
 * @method     ChildNoteDashboard[]|Collection findByUserId(int|array<int> $nd_user_id) Return ChildNoteDashboard objects filtered by the nd_user_id column
 * @psalm-method Collection&\Traversable<ChildNoteDashboard> findByUserId(int|array<int> $nd_user_id) Return ChildNoteDashboard objects filtered by the nd_user_id column
 * @method     ChildNoteDashboard[]|Collection findByNote(string|array<string> $nd_note) Return ChildNoteDashboard objects filtered by the nd_note column
 * @psalm-method Collection&\Traversable<ChildNoteDashboard> findByNote(string|array<string> $nd_note) Return ChildNoteDashboard objects filtered by the nd_note column
 *
 * @method     ChildNoteDashboard[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildNoteDashboard> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class NoteDashboardQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PluginStore\Base\NoteDashboardQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'pluginstore', $modelName = '\\PluginStore\\NoteDashboard', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildNoteDashboardQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildNoteDashboardQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildNoteDashboardQuery) {
            return $criteria;
        }
        $query = new ChildNoteDashboardQuery();
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
     * @return ChildNoteDashboard|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(NoteDashboardTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = NoteDashboardTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildNoteDashboard A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT nd_id, nd_user_id, nd_note FROM NoteDashboard_nd WHERE nd_id = :p0';
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
            /** @var ChildNoteDashboard $obj */
            $obj = new ChildNoteDashboard();
            $obj->hydrate($row);
            NoteDashboardTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildNoteDashboard|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(NoteDashboardTableMap::COL_ND_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(NoteDashboardTableMap::COL_ND_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the nd_id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE nd_id = 1234
     * $query->filterById(array(12, 34)); // WHERE nd_id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE nd_id > 12
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
                $this->addUsingAlias(NoteDashboardTableMap::COL_ND_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(NoteDashboardTableMap::COL_ND_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NoteDashboardTableMap::COL_ND_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the nd_user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserId(1234); // WHERE nd_user_id = 1234
     * $query->filterByUserId(array(12, 34)); // WHERE nd_user_id IN (12, 34)
     * $query->filterByUserId(array('min' => 12)); // WHERE nd_user_id > 12
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
                $this->addUsingAlias(NoteDashboardTableMap::COL_ND_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(NoteDashboardTableMap::COL_ND_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NoteDashboardTableMap::COL_ND_USER_ID, $userId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the nd_note column
     *
     * Example usage:
     * <code>
     * $query->filterByNote('fooValue');   // WHERE nd_note = 'fooValue'
     * $query->filterByNote('%fooValue%', Criteria::LIKE); // WHERE nd_note LIKE '%fooValue%'
     * $query->filterByNote(['foo', 'bar']); // WHERE nd_note IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $note The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByNote($note = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($note)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NoteDashboardTableMap::COL_ND_NOTE, $note, $comparison);

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildNoteDashboard $noteDashboard Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($noteDashboard = null)
    {
        if ($noteDashboard) {
            $this->addUsingAlias(NoteDashboardTableMap::COL_ND_ID, $noteDashboard->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the NoteDashboard_nd table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(NoteDashboardTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            NoteDashboardTableMap::clearInstancePool();
            NoteDashboardTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(NoteDashboardTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(NoteDashboardTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            NoteDashboardTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            NoteDashboardTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
