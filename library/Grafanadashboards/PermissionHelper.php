<?php

namespace Icinga\Module\Grafanadashboards;

use Icinga\Authentication\Auth;
use Icinga\Util\StringHelper;

class PermissionHelper
{
    public function __construct()
    {

    }
    public function filterDashboards($dashboards)
    {
        return array_filter($dashboards, function ($dashboard) {
            return $this->hasPermissionForDashboard($dashboard['title']);
        });
    }
    public function filterFolders($folders)
    {
        return array_filter($folders, function ($folder) {
            return $this->hasPermissionForFolder($folder['title']);
        });
    }

    public function filterMenuFolders($folders)
    {
        return array_filter($folders, function ($folder) {
            return $this->hasPermissionForFolder($folder);
        });
    }



    public function hasPermissionForInstance($instancename)
    {
        return $this->hasPermission($instancename, 'grafanadashboards/allowinstances');
    }
    public function hasPermissionForFolder($foldername)
    {
        return $this->hasPermission($foldername, 'grafanadashboards/allowfolders');
    }
    public function hasPermissionForDashboard($dashboardname)
    {
        return $this->hasPermission($dashboardname, 'grafanadashboards/allowdashboards');
    }
    public function hasPermissionToRebuild()
    {
        return $this->getAuth()->hasPermission('grafanadashboards/rebuild');
    }
    public function hasPermission($instance, $type)
    {
        if ($this->getAuth()->getUser()->isUnrestricted()) {
            return true;
        }
        foreach ($this->getAuth()->getUser()->getRoles() as $role) {

            if (($restriction = $role->getRestrictions($type))) {
                if($restriction !== null){
                    $multiple = StringHelper::trimSplit($restriction, ',');
                    foreach ($multiple as $partialMatch){
                        if (fnmatch($partialMatch, $instance)) {
                            return true;
                        }
                    }
                }
            }

        }
        return false;
    }
    public function getAuth()
    {
        return Auth::getInstance();
    }
}