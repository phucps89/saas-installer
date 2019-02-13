# Saas Installer
The Saas Installer package for the Saas Model.

## Get Started
**Requirements**
* OS: Linux 
* Compiler: `g++` >= 4.4 || `clang++` >= 3.x || `vc++` >= 11
* PHP 7 or higher
* Ext Zip
* Ext Posix
* Running as Root

#### Install package globally

```bash
composer global require phucps89/saas-installer:dev-master
```
With specific version
```bash
composer global require phucps89/saas-installer:{{version}}
```

#### Bind composer binary path to system
````bash
export PATH=~/.composer/vendor/bin:$PATH
````

**And now, you can use `saas` command**