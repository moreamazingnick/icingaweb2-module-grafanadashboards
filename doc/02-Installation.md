# Installation <a id="module-grafanadashboards-installation"></a>

## Requirements <a id="module-grafanadashboards-installation-requirements"></a>

* Icinga Web 2 (&gt;= 2.10.3)
* PHP (&gt;= 7.3)

## Installation from .tar.gz <a id="module-grafanadashboards-installation-manual"></a>

Download the latest version and extract it to a folder named `grafanadashboards`
in one of your Icinga Web 2 module path directories.

## Enable the newly installed module <a id="module-grafanadashboards-installation-enable"></a>

Enable the `grafanadashboards` module either on the CLI by running

```sh
icingacli module enable grafanadashboards
```

Or go to your Icinga Web 2 frontend, choose `Configuration` -&gt; `Modules`, chose the `grafanadashboards` module and `enable` it.

It might afterward be necessary to refresh your web browser to be sure that
newly provided styling is loaded.