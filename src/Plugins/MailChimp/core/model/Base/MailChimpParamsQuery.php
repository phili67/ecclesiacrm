<?php

namespace PluginStore\Base;

use \Exception;
use \PDO;
use PluginStore\MailChimpParams as ChildMailChimpParams;
use PluginStore\MailChimpParamsQuery as ChildMailChimpParamsQuery;
use PluginStore\Map\MailChimpParamsTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the `mc_params` table.
 *
 * @method     ChildMailChimpParamsQuery orderById($order = Criteria::ASC) Order by the mc_p_id column
 * @method     ChildMailChimpParamsQuery orderByApiKey($order = Criteria::ASC) Order by the mc_p_api_key column
 * @method     ChildMailChimpParamsQuery orderByRequestTimeout($order = Criteria::ASC) Order by the mc_p_request_timeout column
 * @method     ChildMailChimpParamsQuery orderByWithAddressPhone($order = Criteria::ASC) Order by the mc_p_with_address_phone column
 * @method     ChildMailChimpParamsQuery orderByEmailSender($order = Criteria::ASC) Order by the mc_p_email_sender column
 * @method     ChildMailChimpParamsQuery orderByContentsExternalCssFont($order = Criteria::ASC) Order by the mc_p_contents_external_css_font column
 * @method     ChildMailChimpParamsQuery orderByExtraFont($order = Criteria::ASC) Order by the mc_p_extra_font column
 *
 * @method     ChildMailChimpParamsQuery groupById() Group by the mc_p_id column
 * @method     ChildMailChimpParamsQuery groupByApiKey() Group by the mc_p_api_key column
 * @method     ChildMailChimpParamsQuery groupByRequestTimeout() Group by the mc_p_request_timeout column
 * @method     ChildMailChimpParamsQuery groupByWithAddressPhone() Group by the mc_p_with_address_phone column
 * @method     ChildMailChimpParamsQuery groupByEmailSender() Group by the mc_p_email_sender column
 * @method     ChildMailChimpParamsQuery groupByContentsExternalCssFont() Group by the mc_p_contents_external_css_font column
 * @method     ChildMailChimpParamsQuery groupByExtraFont() Group by the mc_p_extra_font column
 *
 * @method     ChildMailChimpParamsQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildMailChimpParamsQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildMailChimpParamsQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildMailChimpParamsQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildMailChimpParamsQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildMailChimpParamsQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildMailChimpParams|null findOne(?ConnectionInterface $con = null) Return the first ChildMailChimpParams matching the query
 * @method     ChildMailChimpParams findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildMailChimpParams matching the query, or a new ChildMailChimpParams object populated from the query conditions when no match is found
 *
 * @method     ChildMailChimpParams|null findOneById(int $mc_p_id) Return the first ChildMailChimpParams filtered by the mc_p_id column
 * @method     ChildMailChimpParams|null findOneByApiKey(string $mc_p_api_key) Return the first ChildMailChimpParams filtered by the mc_p_api_key column
 * @method     ChildMailChimpParams|null findOneByRequestTimeout(int $mc_p_request_timeout) Return the first ChildMailChimpParams filtered by the mc_p_request_timeout column
 * @method     ChildMailChimpParams|null findOneByWithAddressPhone(boolean $mc_p_with_address_phone) Return the first ChildMailChimpParams filtered by the mc_p_with_address_phone column
 * @method     ChildMailChimpParams|null findOneByEmailSender(string $mc_p_email_sender) Return the first ChildMailChimpParams filtered by the mc_p_email_sender column
 * @method     ChildMailChimpParams|null findOneByContentsExternalCssFont(string $mc_p_contents_external_css_font) Return the first ChildMailChimpParams filtered by the mc_p_contents_external_css_font column
 * @method     ChildMailChimpParams|null findOneByExtraFont(string $mc_p_extra_font) Return the first ChildMailChimpParams filtered by the mc_p_extra_font column
 *
 * @method     ChildMailChimpParams requirePk($key, ?ConnectionInterface $con = null) Return the ChildMailChimpParams by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMailChimpParams requireOne(?ConnectionInterface $con = null) Return the first ChildMailChimpParams matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildMailChimpParams requireOneById(int $mc_p_id) Return the first ChildMailChimpParams filtered by the mc_p_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMailChimpParams requireOneByApiKey(string $mc_p_api_key) Return the first ChildMailChimpParams filtered by the mc_p_api_key column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMailChimpParams requireOneByRequestTimeout(int $mc_p_request_timeout) Return the first ChildMailChimpParams filtered by the mc_p_request_timeout column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMailChimpParams requireOneByWithAddressPhone(boolean $mc_p_with_address_phone) Return the first ChildMailChimpParams filtered by the mc_p_with_address_phone column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMailChimpParams requireOneByEmailSender(string $mc_p_email_sender) Return the first ChildMailChimpParams filtered by the mc_p_email_sender column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMailChimpParams requireOneByContentsExternalCssFont(string $mc_p_contents_external_css_font) Return the first ChildMailChimpParams filtered by the mc_p_contents_external_css_font column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMailChimpParams requireOneByExtraFont(string $mc_p_extra_font) Return the first ChildMailChimpParams filtered by the mc_p_extra_font column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildMailChimpParams[]|Collection find(?ConnectionInterface $con = null) Return ChildMailChimpParams objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildMailChimpParams> find(?ConnectionInterface $con = null) Return ChildMailChimpParams objects based on current ModelCriteria
 *
 * @method     ChildMailChimpParams[]|Collection findById(int|array<int> $mc_p_id) Return ChildMailChimpParams objects filtered by the mc_p_id column
 * @psalm-method Collection&\Traversable<ChildMailChimpParams> findById(int|array<int> $mc_p_id) Return ChildMailChimpParams objects filtered by the mc_p_id column
 * @method     ChildMailChimpParams[]|Collection findByApiKey(string|array<string> $mc_p_api_key) Return ChildMailChimpParams objects filtered by the mc_p_api_key column
 * @psalm-method Collection&\Traversable<ChildMailChimpParams> findByApiKey(string|array<string> $mc_p_api_key) Return ChildMailChimpParams objects filtered by the mc_p_api_key column
 * @method     ChildMailChimpParams[]|Collection findByRequestTimeout(int|array<int> $mc_p_request_timeout) Return ChildMailChimpParams objects filtered by the mc_p_request_timeout column
 * @psalm-method Collection&\Traversable<ChildMailChimpParams> findByRequestTimeout(int|array<int> $mc_p_request_timeout) Return ChildMailChimpParams objects filtered by the mc_p_request_timeout column
 * @method     ChildMailChimpParams[]|Collection findByWithAddressPhone(boolean|array<boolean> $mc_p_with_address_phone) Return ChildMailChimpParams objects filtered by the mc_p_with_address_phone column
 * @psalm-method Collection&\Traversable<ChildMailChimpParams> findByWithAddressPhone(boolean|array<boolean> $mc_p_with_address_phone) Return ChildMailChimpParams objects filtered by the mc_p_with_address_phone column
 * @method     ChildMailChimpParams[]|Collection findByEmailSender(string|array<string> $mc_p_email_sender) Return ChildMailChimpParams objects filtered by the mc_p_email_sender column
 * @psalm-method Collection&\Traversable<ChildMailChimpParams> findByEmailSender(string|array<string> $mc_p_email_sender) Return ChildMailChimpParams objects filtered by the mc_p_email_sender column
 * @method     ChildMailChimpParams[]|Collection findByContentsExternalCssFont(string|array<string> $mc_p_contents_external_css_font) Return ChildMailChimpParams objects filtered by the mc_p_contents_external_css_font column
 * @psalm-method Collection&\Traversable<ChildMailChimpParams> findByContentsExternalCssFont(string|array<string> $mc_p_contents_external_css_font) Return ChildMailChimpParams objects filtered by the mc_p_contents_external_css_font column
 * @method     ChildMailChimpParams[]|Collection findByExtraFont(string|array<string> $mc_p_extra_font) Return ChildMailChimpParams objects filtered by the mc_p_extra_font column
 * @psalm-method Collection&\Traversable<ChildMailChimpParams> findByExtraFont(string|array<string> $mc_p_extra_font) Return ChildMailChimpParams objects filtered by the mc_p_extra_font column
 *
 * @method     ChildMailChimpParams[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildMailChimpParams> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class MailChimpParamsQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PluginStore\Base\MailChimpParamsQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'pluginstore', $modelName = '\\PluginStore\\MailChimpParams', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildMailChimpParamsQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildMailChimpParamsQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildMailChimpParamsQuery) {
            return $criteria;
        }
        $query = new ChildMailChimpParamsQuery();
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
     * @return ChildMailChimpParams|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(MailChimpParamsTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = MailChimpParamsTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildMailChimpParams A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT mc_p_id, mc_p_api_key, mc_p_request_timeout, mc_p_with_address_phone, mc_p_email_sender, mc_p_contents_external_css_font, mc_p_extra_font FROM mc_params WHERE mc_p_id = :p0';
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
            /** @var ChildMailChimpParams $obj */
            $obj = new ChildMailChimpParams();
            $obj->hydrate($row);
            MailChimpParamsTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildMailChimpParams|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the mc_p_id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE mc_p_id = 1234
     * $query->filterById(array(12, 34)); // WHERE mc_p_id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE mc_p_id > 12
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
                $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the mc_p_api_key column
     *
     * Example usage:
     * <code>
     * $query->filterByApiKey('fooValue');   // WHERE mc_p_api_key = 'fooValue'
     * $query->filterByApiKey('%fooValue%', Criteria::LIKE); // WHERE mc_p_api_key LIKE '%fooValue%'
     * $query->filterByApiKey(['foo', 'bar']); // WHERE mc_p_api_key IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $apiKey The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByApiKey($apiKey = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($apiKey)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_API_KEY, $apiKey, $comparison);

        return $this;
    }

    /**
     * Filter the query on the mc_p_request_timeout column
     *
     * Example usage:
     * <code>
     * $query->filterByRequestTimeout(1234); // WHERE mc_p_request_timeout = 1234
     * $query->filterByRequestTimeout(array(12, 34)); // WHERE mc_p_request_timeout IN (12, 34)
     * $query->filterByRequestTimeout(array('min' => 12)); // WHERE mc_p_request_timeout > 12
     * </code>
     *
     * @param mixed $requestTimeout The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByRequestTimeout($requestTimeout = null, ?string $comparison = null)
    {
        if (is_array($requestTimeout)) {
            $useMinMax = false;
            if (isset($requestTimeout['min'])) {
                $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_REQUEST_TIMEOUT, $requestTimeout['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($requestTimeout['max'])) {
                $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_REQUEST_TIMEOUT, $requestTimeout['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_REQUEST_TIMEOUT, $requestTimeout, $comparison);

        return $this;
    }

    /**
     * Filter the query on the mc_p_with_address_phone column
     *
     * Example usage:
     * <code>
     * $query->filterByWithAddressPhone(true); // WHERE mc_p_with_address_phone = true
     * $query->filterByWithAddressPhone('yes'); // WHERE mc_p_with_address_phone = true
     * </code>
     *
     * @param bool|string $withAddressPhone The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByWithAddressPhone($withAddressPhone = null, ?string $comparison = null)
    {
        if (is_string($withAddressPhone)) {
            $withAddressPhone = in_array(strtolower($withAddressPhone), array('false', 'off', '-', 'no', 'n', '0', ''), true) ? false : true;
        }

        $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_WITH_ADDRESS_PHONE, $withAddressPhone, $comparison);

        return $this;
    }

    /**
     * Filter the query on the mc_p_email_sender column
     *
     * Example usage:
     * <code>
     * $query->filterByEmailSender('fooValue');   // WHERE mc_p_email_sender = 'fooValue'
     * $query->filterByEmailSender('%fooValue%', Criteria::LIKE); // WHERE mc_p_email_sender LIKE '%fooValue%'
     * $query->filterByEmailSender(['foo', 'bar']); // WHERE mc_p_email_sender IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $emailSender The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByEmailSender($emailSender = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($emailSender)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_EMAIL_SENDER, $emailSender, $comparison);

        return $this;
    }

    /**
     * Filter the query on the mc_p_contents_external_css_font column
     *
     * Example usage:
     * <code>
     * $query->filterByContentsExternalCssFont('fooValue');   // WHERE mc_p_contents_external_css_font = 'fooValue'
     * $query->filterByContentsExternalCssFont('%fooValue%', Criteria::LIKE); // WHERE mc_p_contents_external_css_font LIKE '%fooValue%'
     * $query->filterByContentsExternalCssFont(['foo', 'bar']); // WHERE mc_p_contents_external_css_font IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $contentsExternalCssFont The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByContentsExternalCssFont($contentsExternalCssFont = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($contentsExternalCssFont)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_CONTENTS_EXTERNAL_CSS_FONT, $contentsExternalCssFont, $comparison);

        return $this;
    }

    /**
     * Filter the query on the mc_p_extra_font column
     *
     * Example usage:
     * <code>
     * $query->filterByExtraFont('fooValue');   // WHERE mc_p_extra_font = 'fooValue'
     * $query->filterByExtraFont('%fooValue%', Criteria::LIKE); // WHERE mc_p_extra_font LIKE '%fooValue%'
     * $query->filterByExtraFont(['foo', 'bar']); // WHERE mc_p_extra_font IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $extraFont The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByExtraFont($extraFont = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($extraFont)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_EXTRA_FONT, $extraFont, $comparison);

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildMailChimpParams $mailChimpParams Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($mailChimpParams = null)
    {
        if ($mailChimpParams) {
            $this->addUsingAlias(MailChimpParamsTableMap::COL_MC_P_ID, $mailChimpParams->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the mc_params table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(MailChimpParamsTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            MailChimpParamsTableMap::clearInstancePool();
            MailChimpParamsTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(MailChimpParamsTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(MailChimpParamsTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            MailChimpParamsTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            MailChimpParamsTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
