<?php


namespace EcclesiaCRM\Service;

use EcclesiaCRM\PluginQuery;


class MailService
{
    private $services;

    public function __construct()
    {
        $this->services = [];
        $plugin = PluginQuery::create()->findByCategory("Communication");

        Foreach ($plugin as $plug) {            
            $this->services = array_merge($this->services, $plug->getAllClassesServices());
        }        
    }

    /**
     *  getAllServices.
     *
     */
    public function getAllServices() : array
    {
        return $this->services;
    }

    public function isActive() : bool
    {
         Foreach ($this->services as $service) {
            $ser = new $service();
            if ($ser->isActive() and $ser->isLoaded()) {
                return true;
            }
        }
        return false;
    }

    public function isLoaded() : bool
    {
         Foreach ($this->services as $service) {
            $ser = new $service();
            if ($ser->isActive() and $ser->isLoaded()) {
                return true;
            }
        }
        return false;
    }

    public function getFirstActiveService() : ?object
    {
        Foreach ($this->services as $service) {
            $ser = new $service();

            if ($ser->isActive() and $ser->isLoaded()) {
                return $ser;
            }
        }
        return null;
    }

    public function getListNameFromEmail($email) : string
    {
        $ret = [];
        Foreach ($this->services as $service) {
            $ser = new $service();

            if ($ser->isActive() and $ser->isLoaded()) {
                $ret = array_merge($ret, [str_replace('Service', '', end(explode('\\', $service))) => $ser->getListNameFromEmail($email)]);
            }
        }
        return  "<ul class=\"list-group list-group-unbordered mb-3\">".implode(' ', array_map(
            function ($k, $v) { return !empty($v) ? "<li class=\"list-item\">$k : ". htmlspecialchars($v) ."</li>" : ''; },
            array_keys($ret), $ret
        ))."</ul>";
    }

    public function getListNameAndStatus($email) : array
    {
        $ret = [];
        Foreach ($this->services as $service) {
            $ser = new $service();

            if ($ser->isActive() and $ser->isLoaded()) {
                $ret = array_merge($ret, [end(explode('\\', $service)) => $ser->getListNameAndStatus($email)]);
            }
        }
        return $ret;
    }


    public function deleteMemberEmail($email) : array {
        $ret = [];
        Foreach ($this->services as $service) {
            $ser = new $service();

            if ($ser->isActive() and $ser->isLoaded()) {
                $ret[] = $ser->deleteMemberEmail( $email);
            }
        }
        return $ret;
    }

    public function updateMemberEmail($oldEmail, $newEmail) : array
    {
        $ret = [];
        Foreach ($this->services as $service) {
            $ser = new $service();

            if ($ser->isActive() and $ser->isLoaded()) {
                $ret[] = $ser->updateMemberEmail( $oldEmail, $newEmail );
            }
        }
        return $ret;
    }

    public function updateGlobalMember($fname, $lname, $email, $status) : array
    {
        $ret = [];
        Foreach ($this->services as $service) {
            $ser = new $service();

            if ($ser->isActive() and $ser->isLoaded()) {
                $ret[] = $ser->updateGlobalMember( $fname, $lname, $email, $status);
            }
        }
        return $ret;
    }
}