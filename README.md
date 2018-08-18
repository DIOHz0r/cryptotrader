# CryptoTrader

## Synopsis

This command client is for search cryptocurrencies advertisements or prices on different web pages like 
[Localbitcoins](https://www.localbicoins.com) or [Uphold](https://www.uphold.com) for now. 

More services and options will be available coming soon.

## Installation

You will need only PHP 7.1 or above and composer to use this.

Clone or download the repo and run 

```composer install --no-dev``` 

## How to use

Just execute the following command ```php bin/console``` and you will get the available options, for example
```php bin/console localbtc:sell:online EUR``` will search all sell ads on localbitcoin.com for EUR currency.

If you want to check the options available for each command execute its help, like 
```php bin/console localbtc:sell:online --help``` 


## Versioning

In order to provide transparency on our release cycle and to maintain backward compatibility, this project is 
maintained under [the Semantic Versioning guidelines](http://semver.org/). We are committed to following and 
complying with the rules, the best we can.

## How to contribute

Want to file a bug, contribute some code, or improve documentation? Excellent! Follow up our guidelines for contributing

### <a name="1"></a> 1. See what's going on! [:top:](#top)

#### <a name="1.1"></a> 1.1 Issue Dashboard
If you want to know all the issues or like to implement a new feature please we're dealing with right now, take a look 
at our [Issue Dashboard](https://github.com/DIOHz0r/cryptotrader/issues) and look for areas in which you can help.


#### <a name="1.2"></a> 1.2 Pull Request Dashboard
If you want to give us a hand solving issues then great, take a look at our [Pull Request Dashboard](https://github.com/DIOHz0r/cryptotrader/pulls) 
and check for an open or closed PR. We don’t want to duplicate efforts.

#### <a name="1.3"></a> 1.3 Commit Your Changes
For commits, we follow the [Conventional Commit](http://conventionalcommits.org/). This leads to **more readable 
messages** that are easy to follow when looking through the project history. But also, we use the git commit messages 
to **automatically generate changelogs** from these messages. 

Please read and follow the guide provided by Conventional Commit project
