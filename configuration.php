<?php

/** @var \Icinga\Application\Modules\Module $this */

use Icinga\Application\Icinga;
use Icinga\Application\Modules\Module;
use Icinga\Module\Grafanadashboards\GrafanaHelper;
use Icinga\Module\Grafanadashboards\JwtHelper;
use Icinga\Module\Grafanadashboards\InstanceIniRepository;
use Icinga\Web\Url;




?>


<?php


$this->providePermission('config/grafanadashboards', $this->translate('allow access to grafanadashboards configuration'));
$this->provideRestriction('grafanadashboards/allowinstances', $this->translate('allow user to access instances that match the filter, comma separated'));
$this->provideRestriction('grafanadashboards/allowdashboards', $this->translate('allow user to access dashboards that match the filter, comma separated'));
$this->provideRestriction('grafanadashboards/allowfolders', $this->translate('allow user to access folders that match the filter, comma separated'));


?>
<?php

$this->providePermission('grafanadashboards/instance', $this->translate('allow access to instance'));

$this->provideConfigTab('config/instance', array(
    'title' => $this->translate('Instances'),
    'label' => $this->translate('Instances'),
    'url' => 'instance'
));





$this->providePermission('grafanadashboards/rebuild', $this->translate('allow access to rebuild the cache'));

$this->provideConfigTab(
    'config',
    [
        'title' => $this->translate('Config'),
        'label' => $this->translate('Config'),
        'url' => 'config'
    ]
);
$index = 0;

if($this->isRegistered() && !Icinga::app()->isCli() && \Icinga\Authentication\Auth::getInstance()->isAuthenticated()){
    $permissionHelper = new \Icinga\Module\Grafanadashboards\PermissionHelper();
    try {

        $instanceRepo = new InstanceIniRepository();
        foreach ($instanceRepo->select() as $instance){
            if(!$permissionHelper->hasPermissionForInstance($instance->name)){
                continue;
            }
            $name = $instance->name;

            $section = $this->menusection($name, array(
                'icon' => 'chart-area',
                'url' => 'grafanadashboards/grafana?name='.$instance->name,
                //'target' => '_blank',
                'priority' => 400,
            ));
            if(isset($instance->url) && !empty($instance->url)) {
                if($instance->type == 'jwt'){
                    $username = \Icinga\Authentication\Auth::getInstance()->getUser()->getUsername();
                    $module = Module::get('grafanadashboards');
                    $pkiDir = $module->getConfig()->get('settings','pkidir',$module->getConfigDir().DIRECTORY_SEPARATOR."pki");
                    $jwt = new JwtHelper($instance->name,$username,100,$username."@jwtlogin",$pkiDir);
                    $token = $jwt->generateJwtToken();
                }else{
                    $token = false;
                }

                    $instanceUrl = $instance->url;
                    if($token !== false){
                        $instanceUrl = $instance->url."?auth_token=".$token;
                    }
                    $section->add('Grafana', array(
                        'icon' => 'chart-area',
                        'url' => $instanceUrl,
                        'target' => '_blank',
                        'priority' => 0));


                }else{
                    $section->add('Grafana', array(
                        'icon' => 'chart-area',
                        'url' => $instance->url,
                        'target' => '_blank',
                        'priority' => 0));


                }



            $module = Module::get('grafanadashboards');


            $cacheDir = $module->getConfigDir() . DIRECTORY_SEPARATOR . "cache";
            if (isset($instance->url) && isset($instance->apikey)) {
                $helper = new GrafanaHelper(
                    $instance->name,
                    $instance->url,
                    $instance->apikey,
                    $instance->disable_ssl == '1',
                    $cacheDir
                );
                $items = $helper->provideMenuItems();
                $items = $permissionHelper->filterMenuFolders($items);
                if ($items !== false) {
                    $index = 10;
                    foreach ($items as $item) {

                    $urlAttributes = ['folder'=>$item, 'name'=>$instance->name];

                    $finalUrl = Url::fromPath('grafanadashboards/grafana/folder', $urlAttributes);
                        $section->add($item, array(
                            'icon' => 'chart-area',
                            'url' => \ipl\Web\Url::fromPath('grafanadashboards/grafana/folder',$urlAttributes),
                            'priority' => $index));

                        $index = $index + 10;
                    }

                }

            }
            if($permissionHelper->hasPermissionToRebuild()){
                $section->add("Reload from Grafana", array(
                        'icon' => 'spin6',
                        'url' => \ipl\Web\Url::fromPath('grafanadashboards/grafana/rebuild',['name'=>$instance->name]),
                        'priority' => $index)
                );
            }



        }





    } catch (Throwable $e) {
        \Icinga\Application\Logger::error("grafanadashboards " . $e->getMessage());
    }
}

?>
