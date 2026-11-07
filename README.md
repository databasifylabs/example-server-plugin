# Example server plugin for Databasify

A working [Databasify](https://github.com/databasifylabs/databasify) plugin and a template to start your own. It adds **Example Cloud**, a fake server provider that "orders" a server you already own, so you can try the whole ordering flow without a cloud account.

## Try it

1. In Databasify, open **Plugins**, click **Install plugin** and enter `databasifylabs/example-server-plugin`, or upload a zip of this repository.
2. Take a server you own that runs a system Databasify supports, such as Ubuntu 24.04 or Debian 13.
3. Add the Databasify SSH key to root on it. Open **Servers → Add server → Existing server**, choose **SSH key** under First sign-in, copy the command and run it on the server as `root`.
4. Open **Providers → Add provider**, pick **Example Cloud** and enter the server's public IP.
5. Open **Servers → Add server → New server at a provider**, pick the Example Cloud account and choose region **Your server**, plan **Your own server**, and the operating system the server already runs. Databasify then sets it up over SSH, as it does for a real cloud.

Nothing is ordered, started or destroyed. Keys, private networks and orders are written to `storage/app/example-cloud/<sha1 of the IP>.json`. Every order reports the server as running at the IP you entered, with no private IP, so Databasify talks to it on its public address. Destroying it at the provider only removes it from that file. Uninstalling the plugin deletes the folder.

## Layout

```
composer.json                                    package name, version, autoload, plugin class
src/ExampleServerPlugin.php                      the plugin, a Laravel service provider
src/ExampleCloud.php                             the server driver
src/ExampleCloudState.php                        what one account has stored: keys, networks, servers
src/StoredKey.php                                one SSH key as stored in the JSON file
src/StoredServer.php                             one ordered server as stored in the JSON file
public/icon.svg                                  plugin icon, shown on the Plugins page
public/icons/server-providers/example-cloud.svg  server provider icon
```

## Start your own plugin from this template

1. Rename the package in `composer.json` (`name`). The part after the slash becomes the plugin id.
2. Rename the namespace in `autoload.psr-4` and in every file under `src/`.
3. Rename the plugin class and update `extra.databasify.plugin`.
4. Change the provider id returned by `id()` and the label passed to `->label()`.
5. Rename the provider icon to `public/icons/server-providers/<your id>.svg` and replace `public/icon.svg`.
6. Map each operating system you offer to your cloud's image in `->images()`, and list the steps to get a token in `->instructions()`.
7. Set `extra.databasify.requires` to the Databasify versions you support.
8. Replace the fake driver with calls to your cloud's API.

A real driver calls its cloud through `App\Services\Providers\ProviderHttp`, which sets the token, timeouts and error messages. The built-in Hetzner and DigitalOcean drivers in `app/ServerProviders/Drivers` show the whole contract against a real API.

The full guide, with every builder method and driver contract, is at [docs.databasify.io](https://docs.databasify.io/plugins/writing-plugins/).

## Security

A plugin is PHP code that runs with Databasify's full access. Install only plugins you trust.
