**[Services ⟹](https://totum.online/services)**

## Service Installation

For local installation, you need access to the repository [github/totumonline/totum-services](https://github.com/totumonline/totum-services). To receive an invitation, star the [PRO-repository](https://github.com/totumonline/totum-pro), and we will send an invitation within a couple of days.

The installation is done on a clean Ubuntu 24.04. This should be a separate server from the Totum installation!

Server requirements depend on the load, but we recommend at least 2C 2Gb.

```
sudo curl -O https://raw.githubusercontent.com/totumonline/totum-mit/master/totum/moduls/install/totum_services_autoinstall.sh && sudo bash totum_services_autoinstall.sh
```

During the installation process, you will need to enter the **data (username and email) of your GitHub user** (who has access to the services repository). The installer will generate an SSH key for the server, which needs to be added to [https://github.com/settings/keys](https://github.com/settings/keys) on GitHub for the corresponding user.

Key format:

```
ssh-ed25519 AAAAC3NzaC1JSDYSGJDAAAII1xBM65sdrDUEll6AeQwd2Cszn80IoA9Bpk8g5 some@email.com
```

The server configuration is done via ansible, so if there are errors during the installation process (e.g., lack of network between the server and repositories), you can rerun the installation script, and you will be prompted to continue the installation.

Totum V4 can only be connected to an installation with a domain and SSL certificate (automatically obtained by the installation script if a valid domain is available).

Totum V5 can work with services both over SSL and without, via IP. If the services are installed via IP (without a domain), you need to disable certificate verification on the Totum side.

```
nano /home/totum/totum-mit/Conf.php
```

Add the following line after `protected $execSSHOn`:

```
protected $checkSSLservices = false;
```

You can first install without a domain and then switch to an installation with a domain. To do this, point the domain to the server's IP, check for ping availability, and rerun the installer from the folder where it was originally run. You will be prompted to enter the domain and obtain a certificate.

## Connecting Totum to Services

To add a server to the available services on the Services server, you need to fill in the `services_list`:

```
nano /home/totum/totum-services/services_list
```

```
{"number":"3922029074","key":"kIUTdlkUGSdvjadhfKJGSYUdgdfsdf","back_url":"https://live.ttmapp.ru","check_back_url_certificate":true}
```

- **number** — arbitrary number. Each connected host must have its unique number! Access logs are recorded using this number. It must be added to the Totum schema in the `ttm__services` table, in the `h_services_number` field.

- **key** — access key. Generate a random set of numbers and letters and add it to the Totum schema in the `ttm__services` table, in the `h_services_key` field.

- **back_url** — HOST of your Totum server with the protocol `http://` or `https://`.

- **check_back_url_certificate** — `true | false`. If `false`, SSL certificate validity check for your Totum will be disabled. This is necessary in cases where Totum is installed without a certificate.

You can add multiple Totum server configurations to one Services server. To do this, add a server configuration line in the same format on a new line.

If you want to disable a server, comment out the line. Be sure to **SAVE** the file before proceeding to the settings in Totum!

**Settings in Totum:**

In Totum, fill in the fields in the `ttm__services` table.

- **h_services_url** — `https://SERVICES_HOST` or `http://SERVICES_IP` (available only in V5)

- **h_services_number** — service number that you specified in `services_list`.

- **h_services_key** — service key that you specified in `services_list`.

- **h_check_service_server** — connection check. The response should be `number: OK`

## Logs

The service log is recorded in services_log and is cleared once a day (the last 1000 lines are kept).

To view:

```
tail -f /home/totum/totum-services/services_log
```
