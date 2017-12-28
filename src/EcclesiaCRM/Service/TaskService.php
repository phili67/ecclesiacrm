<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\Tasks\ChurchAddress;
use EcclesiaCRM\Tasks\ChurchNameTask;
use EcclesiaCRM\Tasks\EmailTask;
use EcclesiaCRM\Tasks\HttpsTask;
use EcclesiaCRM\Tasks\IntegrityCheckTask;
use EcclesiaCRM\Tasks\PrerequisiteCheckTask;
use EcclesiaCRM\Tasks\iTask;
use EcclesiaCRM\Tasks\LatestReleaseTask;
use EcclesiaCRM\Tasks\RegisteredTask;
use EcclesiaCRM\Tasks\PersonGenderDataCheck;
use EcclesiaCRM\Tasks\PersonClassificationDataCheck;
use EcclesiaCRM\Tasks\PersonRoleDataCheck;
use EcclesiaCRM\Tasks\UpdateFamilyCoordinatesTask;
use EcclesiaCRM\Tasks\CheckUploadSizeTask;

class TaskService
{
    /**
     * @var ObjectCollection|iTask[]
     */
    private $taskClasses;

    public function __construct()
    {

        $this->taskClasses = [
            new PrerequisiteCheckTask(),
            new ChurchNameTask(),
            new ChurchAddress(),
            new EmailTask(),
            new HttpsTask(),
            new IntegrityCheckTask(),
            new LatestReleaseTask(),
            new RegisteredTask(),
            new PersonGenderDataCheck(),
            new PersonClassificationDataCheck(),
            new PersonRoleDataCheck(),
            new UpdateFamilyCoordinatesTask(),
            new CheckUploadSizeTask()
        ];
    }

    public function getCurrentUserTasks()
    {
        $tasks = [];
        foreach ($this->taskClasses as $taskClass) {
            if ($taskClass->isActive()) {
                array_push($tasks, ['title' => $taskClass->getTitle(),
                    'link' => $taskClass->getLink(),
                    'admin' => $taskClass->isAdmin(),
                    'desc' => $taskClass->getDesc()]);
            }
        }
        return $tasks;
    }

}
