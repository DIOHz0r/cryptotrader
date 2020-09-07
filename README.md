# CryptoTrader

## Synopsis

This command client is for search cryptocurrencies advertisements different web pages like 
[Localbitcoins](https://www.localbicoins.com) and [localcryptos](https://www.localcryptos.com), 
it also allows you to search the best prices to sell your cryptos on [Uphold](https://www.uphold.com) for now. 

More services and options will be available coming soon.

## Installation

You will need only PHP 7.1 or above and composer to use this.

Clone or download the repo and run.

```composer install --no-dev``` 

## How to use

### Localbitcoins

Suppose that you want to list all ads on localbitcoin.com payed in Euros just execute the following command.

```php bin/console localbtc:sell:online EUR```

This will order all the ads from the lowes price to the highest 
price in ascending order, that means that the last listed ads are the highest prices found.

Now if you want to filter the results for the top 15 ads with a price of 100 EUR execute the previous command as follows.

```php bin/console localbtc:sell:online EUR -a 100 -t 15```

### Localcryptos

For localcryptos the command is almost the same but you have to add the country [ISO](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements) 
code as the first argument.

```php bin/console lc:sell:online AU -a 100 -t 15```

This command lists ETH as the default coin, but you can use all the coins listed in that platform.
You only need to add the `--coin (-n)` option and the corresponding code.

```php bin/console lc:sell:online AU -a 100 -t 15 -o LTC```

## Selling from Uphold

If you want to sell your Uphold card balance the script will look on each trading platform and will print the results 
as previous commands, you need to set at least the country and the currency to obtain, for instance to find the top 20 
ads in Venezuela (VE) for obtain 10000 Bolivares Soberanos (VES) selling your BTC or ETH excluding the not compatible 
ads execute:

```php bin/console uphold:sell VE VES -a 10000 -t 20 -x``` 

---

If you want to check the options available execute the console help.

```php bin/console --help``` 

If you want to check the options available for each command execute its help, for instance:

```php bin/console localbtc:sell:online --help``` 


## Versioning

In order to provide transparency on the release cycle and to maintain backward compatibility, this project is 
maintained under [the Semantic Versioning guidelines](http://semver.org/). I am committed to following and 
complying with the rules, the best I can.

## How to contribute

Want to file a bug, contribute some code, or improve documentation? Excellent! Follow up the guidelines for contributing

#### Issue Dashboard
If you want to know all the issues I'm dealing with right now or like to implement a new feature please take a look 
at the [Issue Dashboard](https://github.com/DIOHz0r/cryptotrader/issues) and look for areas in which you can help.


#### Pull Request Dashboard
If you want to give me a hand solving issues then great, take a look at the [Pull Request Dashboard](https://github.com/DIOHz0r/cryptotrader/pulls) 
and check for an open or closed PR. I don’t want to duplicate efforts.

#### Commit Your Changes
For commits, I follow the [Conventional Commit](http://conventionalcommits.org/). This leads to **more readable 
messages** that are easy to follow when looking through the project history. But also, I use the git commit messages 
to **automatically generate changelogs** from these messages. 

Please read and follow the guide provided by Conventional Commit project

#### Donations
Do you like our work and want to donate some cryptos? 

I ask at least the amount of 0.0001 of BTC, DASH, ETH or LTC that can be sent to one of the following addresses

* BTC: `1DniVH7mHTZo8sa8gsqBzfspv5MQkFwFcx`
* DASH: `XwQoZy71zp3V54nA44GiUhEL14hvy7zwzh`
* ETH: `0xc0A47F4c950f908083EF360c4eFA941aCaCCede6`
* LTC: `LcbMxHcPfcBWF6FyRPrNUBHhi8EVVn2z33`

Thank you so much for your support!