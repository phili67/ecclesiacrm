<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\CollectionsinstancesQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\DocumentSecurityUtils;

class SharedDocumentService
{
    public function resolve(int $shareId, int $personId): ?array
    {
        $share = CollectionsinstancesQuery::create()->findOneById($shareId);
        if (is_null($share) || is_null($share->getCollections())) {
            return null;
        }

        $collection = $share->getCollections();
        $ownerId = (int)$collection->getOwnerid();
        $isOwner = $ownerId === $personId;
        $isGuest = (int)$share->getGuestid() === $personId;

        if (!$isOwner && !$isGuest) {
            return null;
        }

        // Propel maps share_invitestatus to a boolean, so the raw value is read
        $inviteStatus = (int)CollectionsinstancesQuery::create()
            ->filterById($shareId)
            ->select('ShareInvitestatus')
            ->findOne();

        if ($isGuest && ($inviteStatus !== 2
            || !in_array((int)$share->getAccess(), [2, 3], true))) {
            return null;
        }

        $owner = UserQuery::create()->findOneByPersonId($ownerId);
        if (is_null($owner)) {
            return null;
        }

        $ownerBasePath = rtrim($owner->getUserRootDir(), '/') . '/' . strtolower($owner->getUserName()) . '/';
        $ownerPath = str_replace('\\', '/', $collection->getOwnerpath());
        if (strpos($ownerPath, $ownerBasePath) !== 0) {
            return null;
        }

        $documentRoot = SystemURLs::getDocumentRoot();
        $relativePath = substr($ownerPath, strlen($ownerBasePath));
        $pathSegments = explode('/', $relativePath);

        if ($pathSegments[0] === 'public') {
            $baseDirectory = DocumentSecurityUtils::resolveDirectoryFromRelativeBase(
                $documentRoot,
                $owner->getUserPublicDir()
            );
            $relativePath = implode('/', array_slice($pathSegments, 1));
        } else {
            $baseDirectory = DocumentSecurityUtils::resolveDirectoryFromRelativeBase(
                $documentRoot,
                $owner->getUserRootDir(),
                '/' . trim($owner->getUserName(), '/')
            );
        }

        if (is_null($baseDirectory)) {
            return null;
        }

        $realPath = DocumentSecurityUtils::resolvePathWithinBase($baseDirectory, $relativePath);
        if (is_null($realPath)) {
            return null;
        }

        $currentUser = $isOwner ? $owner : UserQuery::create()->findOneByPersonId($personId);
        $currentUserPath = $isOwner ? $ownerPath : str_replace('\\', '/', (string)$share->getGuestpath());
        $currentUserBasePath = is_null($currentUser)
            ? ''
            : rtrim($currentUser->getUserDir(), '/') . '/';
        $navigationRelativePath = $currentUserBasePath !== ''
            && strpos($currentUserPath, $currentUserBasePath) === 0
            ? DocumentSecurityUtils::normalizeRelativePath(substr($currentUserPath, strlen($currentUserBasePath)))
            : null;

        return [
            'share' => $share,
            'owner' => $owner,
            'ownerId' => $ownerId,
            'ownerPath' => $ownerPath,
            'relativePath' => substr($ownerPath, strlen($ownerBasePath)),
            'navigationRelativePath' => $navigationRelativePath,
            'path' => $realPath,
            'name' => basename($ownerPath),
            'isDirectory' => is_dir($realPath),
            'canWrite' => $isOwner || (int)$share->getAccess() === 3
        ];
    }
}
