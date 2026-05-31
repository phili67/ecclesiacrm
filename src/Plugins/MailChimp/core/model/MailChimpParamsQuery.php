<?php

namespace PluginStore;

use EcclesiaCRM\dto\SystemConfig;
use PluginStore\Base\MailChimpParamsQuery as BaseMailChimpParamsQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'mc_params' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class MailChimpParamsQuery extends BaseMailChimpParamsQuery
{
	public static function getOrCreateOne(): MailChimpParams
	{
		$params = static::create()->findOne();

		if ($params === null) {
			$params = new MailChimpParams();
		}

		return $params;
	}

	public static function extractSettings(): array
	{
		$params = static::create()->findOne();

		return [
			'apiKey' => (string) $params->getApiKey(),
			'requestTimeOut' => (int) $params->getRequestTimeout(),
			'externalCssFont' => (string) $params->getContentsExternalCssFont(),
			'bWithAddressPhone' => (bool) $params->getWithAddressPhone(),
			'sMailChimpEmailSender' => (string) $params->getEmailSender(),
			'sMailChimpExtraFont' => (string) $params->getExtraFont(),
		];
	}

	public static function saveSettings(array $data): MailChimpParams
	{
		$params = static::getOrCreateOne();
		$requestTimeOut = filter_var($data['requestTimeOut'] ?? 30, FILTER_VALIDATE_INT);

		$params->setApiKey(trim((string) ($data['apiKey'] ?? '')));
		$params->setRequestTimeout($requestTimeOut === false ? 30 : max(1, (int) $requestTimeOut));
		$params->setContentsExternalCssFont(trim((string) ($data['externalCssFont'] ?? '')));
		$params->setWithAddressPhone(!empty($data['bWithAddressPhone']));
		$params->setEmailSender(trim((string) ($data['sMailChimpEmailSender'] ?? '')));
		$params->setExtraFont(trim((string) ($data['sMailChimpExtraFont'] ?? '')));
		$params->save();

		return $params;
	}

}
