<?php

namespace PluginStore\Base;

use \Exception;
use \PDO;
use PluginStore\PersonLastJitsiMeeting as ChildPersonLastJitsiMeeting;
use PluginStore\PersonLastJitsiMeetingQuery as ChildPersonLastJitsiMeetingQuery;
use PluginStore\Map\PersonLastJitsiMeetingTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the `personlastjitsimeeting_plm` table.
 *
 * @method     ChildPersonLastJitsiMeetingQuery orderById($order = Criteria::ASC) Order by the jm_plm_ID column
 * @method     ChildPersonLastJitsiMeetingQuery orderByPersonId($order = Criteria::ASC) Order by the jm_plm_person_id column
 * @method     ChildPersonLastJitsiMeetingQuery orderByPersonMeetingId($order = Criteria::ASC) Order by the jm_plm_personmeeting_pm_id column
 *
 * @method     ChildPersonLastJitsiMeetingQuery groupById() Group by the jm_plm_ID column
 * @method     ChildPersonLastJitsiMeetingQuery groupByPersonId() Group by the jm_plm_person_id column
 * @method     ChildPersonLastJitsiMeetingQuery groupByPersonMeetingId() Group by the jm_plm_personmeeting_pm_id column
 *
 * @method     ChildPersonLastJitsiMeetingQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPersonLastJitsiMeetingQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPersonLastJitsiMeetingQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPersonLastJitsiMeetingQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildPersonLastJitsiMeetingQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildPersonLastJitsiMeetingQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildPersonLastJitsiMeeting|null findOne(?ConnectionInterface $con = null) Return the first ChildPersonLastJitsiMeeting matching the query
 * @method     ChildPersonLastJitsiMeeting findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildPersonLastJitsiMeeting matching the query, or a new ChildPersonLastJitsiMeeting object populated from the query conditions when no match is found
 *
 * @method     ChildPersonLastJitsiMeeting|null findOneById(int $jm_plm_ID) Return the first ChildPersonLastJitsiMeeting filtered by the jm_plm_ID column
 * @method     ChildPersonLastJitsiMeeting|null findOneByPersonId(int $jm_plm_person_id) Return the first ChildPersonLastJitsiMeeting filtered by the jm_plm_person_id column
 * @method     ChildPersonLastJitsiMeeting|null findOneByPersonMeetingId(int $jm_plm_personmeeting_pm_id) Return the first ChildPersonLastJitsiMeeting filtered by the jm_plm_personmeeting_pm_id column
 *
 * @method     ChildPersonLastJitsiMeeting requirePk($key, ?ConnectionInterface $con = null) Return the ChildPersonLastJitsiMeeting by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonLastJitsiMeeting requireOne(?ConnectionInterface $con = null) Return the first ChildPersonLastJitsiMeeting matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPersonLastJitsiMeeting requireOneById(int $jm_plm_ID) Return the first ChildPersonLastJitsiMeeting filtered by the jm_plm_ID column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonLastJitsiMeeting requireOneByPersonId(int $jm_plm_person_id) Return the first ChildPersonLastJitsiMeeting filtered by the jm_plm_person_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonLastJitsiMeeting requireOneByPersonMeetingId(int $jm_plm_personmeeting_pm_id) Return the first ChildPersonLastJitsiMeeting filtered by the jm_plm_personmeeting_pm_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPersonLastJitsiMeeting[]|Collection find(?ConnectionInterface $con = null) Return ChildPersonLastJitsiMeeting objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildPersonLastJitsiMeeting> find(?ConnectionInterface $con = null) Return ChildPersonLastJitsiMeeting objects based on current ModelCriteria
 *
 * @method     ChildPersonLastJitsiMeeting[]|Collection findById(int|array<int> $jm_plm_ID) Return ChildPersonLastJitsiMeeting objects filtered by the jm_plm_ID column
 * @psalm-method Collection&\Traversable<ChildPersonLastJitsiMeeting> findById(int|array<int> $jm_plm_ID) Return ChildPersonLastJitsiMeeting objects filtered by the jm_plm_ID column
 * @method     ChildPersonLastJitsiMeeting[]|Collection findByPersonId(int|array<int> $jm_plm_person_id) Return ChildPersonLastJitsiMeeting objects filtered by the jm_plm_person_id column
 * @psalm-method Collection&\Traversable<ChildPersonLastJitsiMeeting> findByPersonId(int|array<int> $jm_plm_person_id) Return ChildPersonLastJitsiMeeting objects filtered by the jm_plm_person_id column
 * @method     ChildPersonLastJitsiMeeting[]|Collection findByPersonMeetingId(int|array<int> $jm_plm_personmeeting_pm_id) Return ChildPersonLastJitsiMeeting objects filtered by the jm_plm_personmeeting_pm_id column
 * @psalm-method Collection&\Traversable<ChildPersonLastJitsiMeeting> findByPersonMeetingId(int|array<int> $jm_plm_personmeeting_pm_id) Return ChildPersonLastJitsiMeeting objects filtered by the jm_plm_personmeeting_pm_id column
 *
 * @method     ChildPersonLastJitsiMeeting[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildPersonLastJitsiMeeting> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class PersonLastJitsiMeetingQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PluginStore\Base\PersonLastJitsiMeetingQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'pluginstore', $modelName = '\\PluginStore\\PersonLastJitsiMeeting', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPersonLastJitsiMeetingQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPersonLastJitsiMeetingQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildPersonLastJitsiMeetingQuery) {
            return $criteria;
        }
        $query = new ChildPersonLastJitsiMeetingQuery();
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
     * @return ChildPersonLastJitsiMeeting|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PersonLastJitsiMeetingTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = PersonLastJitsiMeetingTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildPersonLastJitsiMeeting A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT jm_plm_ID, jm_plm_person_id, jm_plm_personmeeting_pm_id FROM personlastjitsimeeting_plm WHERE jm_plm_ID = :p0';
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
            /** @var ChildPersonLastJitsiMeeting $obj */
            $obj = new ChildPersonLastJitsiMeeting();
            $obj->hydrate($row);
            PersonLastJitsiMeetingTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildPersonLastJitsiMeeting|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the jm_plm_ID column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE jm_plm_ID = 1234
     * $query->filterById(array(12, 34)); // WHERE jm_plm_ID IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE jm_plm_ID > 12
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
                $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the jm_plm_person_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPersonId(1234); // WHERE jm_plm_person_id = 1234
     * $query->filterByPersonId(array(12, 34)); // WHERE jm_plm_person_id IN (12, 34)
     * $query->filterByPersonId(array('min' => 12)); // WHERE jm_plm_person_id > 12
     * </code>
     *
     * @param mixed $personId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPersonId($personId = null, ?string $comparison = null)
    {
        if (is_array($personId)) {
            $useMinMax = false;
            if (isset($personId['min'])) {
                $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSON_ID, $personId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($personId['max'])) {
                $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSON_ID, $personId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSON_ID, $personId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the jm_plm_personmeeting_pm_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPersonMeetingId(1234); // WHERE jm_plm_personmeeting_pm_id = 1234
     * $query->filterByPersonMeetingId(array(12, 34)); // WHERE jm_plm_personmeeting_pm_id IN (12, 34)
     * $query->filterByPersonMeetingId(array('min' => 12)); // WHERE jm_plm_personmeeting_pm_id > 12
     * </code>
     *
     * @param mixed $personMeetingId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPersonMeetingId($personMeetingId = null, ?string $comparison = null)
    {
        if (is_array($personMeetingId)) {
            $useMinMax = false;
            if (isset($personMeetingId['min'])) {
                $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSONMEETING_PM_ID, $personMeetingId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($personMeetingId['max'])) {
                $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSONMEETING_PM_ID, $personMeetingId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_PERSONMEETING_PM_ID, $personMeetingId, $comparison);

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildPersonLastJitsiMeeting $personLastJitsiMeeting Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($personLastJitsiMeeting = null)
    {
        if ($personLastJitsiMeeting) {
            $this->addUsingAlias(PersonLastJitsiMeetingTableMap::COL_JM_PLM_ID, $personLastJitsiMeeting->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the personlastjitsimeeting_plm table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PersonLastJitsiMeetingTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            PersonLastJitsiMeetingTableMap::clearInstancePool();
            PersonLastJitsiMeetingTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(PersonLastJitsiMeetingTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PersonLastJitsiMeetingTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            PersonLastJitsiMeetingTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PersonLastJitsiMeetingTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
