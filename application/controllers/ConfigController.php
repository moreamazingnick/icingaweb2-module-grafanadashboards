<?php

/* Icinga Notifications Web | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Grafanadashboards\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Grafanadashboards\Forms\ModuleconfigForm;
use Icinga\Web\Notification;
use Icinga\Web\Widget\Tab;
use Icinga\Web\Widget\Tabs;
use ipl\Web\Compat\CompatController;

class ConfigController extends CompatController
{
    public function init()
    {
        $this->assertPermission('config/modules');

        parent::init();
    }

    public function indexAction()
    {
        $moduleConfig = Config::module('grafanadashboards');
        $form = (new ModuleconfigForm())
            ->populate($moduleConfig->getSection('settings')->toArray())
            ->on(ModuleconfigForm::ON_SUCCESS, function ($form) use ($moduleConfig) {
                $moduleConfig->setSection('settings', $form->getValues());
                $moduleConfig->saveIni();

                Notification::success(t('New configuration has successfully been stored'));
            })->handleRequest($this->getServerRequest());

        $this->mergeTabs($this->Module()->getConfigTabs()->activate('config'));

        $this->addContent($form);
    }

    /**
     * Merge tabs with other tabs contained in this tab panel
     *
     * @param Tabs $tabs
     *
     * @return void
     */
    protected function mergeTabs(Tabs $tabs): void
    {
        /** @var Tab $tab */
        foreach ($tabs->getTabs() as $tab) {
            $this->tabs->add($tab->getName(), $tab);
        }
    }
}
