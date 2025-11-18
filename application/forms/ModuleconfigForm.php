<?php

/* Icinga Notifications Web | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Grafanadashboards\Forms;

use ipl\Web\Compat\CompatForm;

class ModuleconfigForm extends CompatForm
{
    protected function assemble()
    {

        $this->addElement(
            'text',
            'pkidir',
            [
                'label'       => $this->translate('PKI Directory'),
                'description'       => $this->translate('The pki dir for example /etc/grafana/pki, the folder should have the permission 755, owned by www-data in case that is your webserver user'),
                'required'    => true,
                'value'       => ''
            ]
        );


        $this->addElement(
            'submit',
            'submit',
            [
                'label' => $this->translate('Save Changes')
            ]
        );
    }
}
