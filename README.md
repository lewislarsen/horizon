<p align="center"><img width="373" height="60" src="/art/logo.svg" alt="Laravel Horizon"></p>

<p align="center">
<a href="https://github.com/lewislarsen/horizon/actions"><img src="https://github.com/lewislarsen/horizon/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/lewislarsen/horizon"><img src="https://img.shields.io/packagist/dt/lewislarsen/horizon" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/lewislarsen/horizon"><img src="https://img.shields.io/packagist/v/lewislarsen/horizon" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/lewislarsen/horizon"><img src="https://img.shields.io/packagist/l/lewislarsen/horizon" alt="License"></a>
</p>

## Introduction

Horizon provides a beautiful dashboard and code-driven configuration for your Laravel powered Redis queues. Horizon allows you to easily monitor key metrics of your queue system such as job throughput, runtime, and job failures.

All of your worker configuration is stored in a single, simple configuration file, allowing your configuration to stay in source control where your entire team can collaborate.

<p align="center">
<img src="https://i.postimg.cc/kG0xJgxy/horizon-new-ui.png">
</p>

## Differences

This fork aims to maintain full parity with the official Laravel Horizon repository. We only intend to improve upon Laravel Horizon with an enhanced UI and additional features that wouldn't necessarily be merged into the main repository.

**What's Different:**
- Modern UI redesign with visual design language inspired by Laravel Forge and Laravel Cloud
- Clear queue functionality directly from the dashboard UI

## Installation

Install via Composer:
```bash
composer require lewislarsen/horizon
```

After installation, publish the Horizon assets:
```bash
php artisan horizon:install
```

Then continue with the standard Horizon setup as documented in the [official documentation](https://laravel.com/docs/horizon#installation).

## Official Documentation

Documentation for Horizon can be found on the [Laravel website](https://laravel.com/docs/horizon). Since we maintain parity with the official repository, all documentation applies to this fork as well.

## Contributing

Thank you for considering contributing to Horizon! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

Please review [our security policy](https://github.com/lewislarsen/horizon/security/policy) on how to report security vulnerabilities.

## License

Laravel Horizon is open-sourced software licensed under the [MIT license](LICENSE.md).