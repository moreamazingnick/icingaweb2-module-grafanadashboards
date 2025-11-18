<?php

/* Originally from Icinga Web 2 Reporting Module (c) Icinga GmbH | GPLv2+ */
/* icingaweb2-module-scaffoldbuilder 2023 | GPLv2+ */

namespace Icinga\Module\Grafanadashboards\Controllers;


use Icinga\Application\Config;
use Icinga\Web\Controller;
use Icinga\Module\Grafanadashboards\Forms\ModuleconfigForm;

class ModuleconfigController extends Controller
{



    public function indexAction()
    {
        $form = (new ModuleconfigForm())
            ->setIniConfig(Config::module('grafanadashboards', "config"));

        $form->handleRequest();

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('config/moduleconfig');
        $this->view->form = $form;
    }


    public function createTabs()
    {
        $tabs = $this->getTabs();

        $tabs->add('grafanadashboards/config', [
            'label' => $this->translate('Configure Grafanadashboards'),
            'url' => 'grafanadashboards/config'
        ]);

        return $tabs;

    }

}