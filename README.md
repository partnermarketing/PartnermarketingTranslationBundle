TranslationBundle
=================

[![Build Status](https://travis-ci.org/partnermarketing/PartnermarketingTranslationBundle.svg?branch=master)](https://travis-ci.org/partnermarketing/PartnermarketingTranslationBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/partnermarketing/PartnermarketingTranslationBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/partnermarketing/PartnermarketingTranslationBundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/partnermarketing/PartnermarketingTranslationBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/partnermarketing/PartnermarketingTranslationBundle/?branch=master)
[![HHVM Status](http://hhvm.h4cc.de/badge/partnermarketing/translation-bundle.svg)](http://hhvm.h4cc.de/package/partnermarketing/translation-bundle)

TranslationBundle is a translation component supporting supporting different translation adapters -- by default, OneSky. 

The existing adapters are:

* OneSky

## Workflow

When you're developing you should add all translation keys into a file under `app/Resources/base-translations`.

When you pull translations those will be placed in Symfony 2 standard directory for translations `app/Resources/translations`.

The directory structure should be similar to the one in `base-translations` directory, but all files will have
an extension to the name.

e.g.
```
base-translations/hello_world.yml
```
After translation will become:

```
translations/hello_world.en-GB.yml
translations/hello_world.pt-PT.yml
...
```


## How to configure

in your `parameters.yml`

```yml
parameters:
    partnermarketing_translation.one_sky.project_id: 123
    partnermarketing_translation.one_sky.api_key: yourOneskyKey
    partnermarketing_translation.one_sky.api_secret: youroneskysecret
```

## How to use

See documentation in [Symfony 2](http://symfony.com/doc/current/book/translation.html#basic-translation).

```php
$translator = $this->get('translator');
$translator->trans('your_key');
```


## How to push translations

```sh
app/console partnermarketing:translations:push_base_translations
```

## How to pull translations

This will pull all latest translations.

```sh
app/console partnermarketing:translations:pull_translations
```

## How to run tests

Since [`phpunit`](https://phpunit.de) is a dependency you can run tests using:

```sh
vendor/bin/phpunit
```


## How to contribute

* [Raise issues to request new features or report problems.](https://github.com/partnermarketing/PartnermarketingTranslationBundle/issues)
* [Create pull requests.](https://github.com/partnermarketing/PartnermarketingTranslationBundle/pulls)
