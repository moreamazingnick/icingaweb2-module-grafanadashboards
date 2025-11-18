<?php

namespace Icinga\Module\Grafanadashboards;


use Icinga\Application\Config;
use Icinga\Authentication\Auth;
use Icinga\Module\Grafana\Helpers\JwtToken;

class JwtGrafanaModuleWrapper
{
    public static function getJwtToken(){
        $config = Config::module('grafana')->getSection('grafana');
        $jwtIssuer = $config->get('jwtIssuer');
        $jwtEnable = $config->get('jwtEnable', false);
        $jwtExpires = $config->get('jwtExpires', 30);
        $jwtUser = $config->get('jwtUser', Auth::getInstance()->getUser()->getUsername());
        $authToken = JwtToken::create($jwtUser, $jwtExpires, !empty($jwtIssuer) ? $jwtIssuer:null, [ 'roles' => [ 'Viewer' ] ]);
        if($jwtEnable === false){
            return false;
        }else{
            return $authToken;
        }

    }
}