# Vanilo Cart Module Changelog

## 3.x Series

## Unreleased
##### 2022-06-XX

- Added Enum 4.0 Support
- Added `__call` to `CartManager` that proxies unhandled calls to the underlying cart model
- Changed minimum Laravel requirement to 9.2
- Changed minimum Konekt module requirements to:
    - Concord: 1.11
    - Enum: 3.1.1

## 3.0.1
##### 2022-05-22

- Bump module version to mainline (no change)

## 3.0.0
##### 2022-02-28

- Added Laravel 9 support
- Added PHP 8.1 support
- Dropped PHP 7.4 Support
- Dropped Laravel 6-8 Support
- Removed Admin from "Framework" - it is available as an optional separate package see [vanilo/admin](https://github.com/vanilophp/admin) 
- Minimum Laravel version is 8.22.1. [See GHSA-3p32-j457-pg5x](https://github.com/advisories/GHSA-3p32-j457-pg5x)


---

## 2.x Series

### 2.2.0
##### 2021-09-11

- Changed internal CS ruleset from PSR-2 to PSR-12
- Dropped PHP 7.3 support

### 2.1.1
##### 2020-12-31

- Added PHP 8 support
- Changed CI from Travis to Github
- Only works with Vanilo 2.1+ modules

### 2.1.0
##### 2020-10-27

- Added configuration option to explicitly define the cart's user model class
- Works with Vanilo 2.0 modules

### 2.0.0
##### 2020-10-11

- BC: interfaces comply with vanilo/contracts v2
- BC: Upgrade to Enum v3
- Added Laravel 8 support
- Dropped Laravel 5 support
- Dropped PHP 7.2 support

## 1.x Series

### 1.2.0
##### 2020-03-29

- Added Laravel 7 Support
- Added PHP 7.4 support
- Dropped PHP 7.1 support

### 1.1.1
##### 2019-12-21

- Fixed bug with cart id stuck in session without matching DB entry.

### 1.1.0
##### 2019-11-25

- Added Laravel 6 Support
- Dropped Laravel 5.4 Support

### 1.0.0
##### 2019-11-11

- Added protection against missing cart session config key value
- Added merge cart feature on login

## 0.5 Series

### 0.5.1
##### 2019-03-17

- Complete Laravel 5.8 compatibility (likely works with 0.4.0 & 0.5.0 as well)
- PHP 7.0 support has been dropped

### 0.5.0
##### 2019-02-11

- No change, version has been bumped for v0.5 series

## 0.4 Series

### 0.4.0
##### 2018-11-12

- Possibility to preserve cart for users (across logins) feature
- Laravel 5.7 compatibility
- Tested with PHP 7.3

## 0.3 Series

### 0.3.0
##### 2018-08-11

- Custom product attributes can be passed/configured when adding cart items
- Works with product images
- Test suite improvements for Laravel 5.4 compatibility
- Doc improvements

## 0.2 Series

### 0.2.0
##### 2018-02-19

- Cart user handling works
- Laravel 5.6 compatible


## 0.1 Series

### 0.1.0
##### 2017-12-11

- 🐣 -> 🛂 -> 🤦 -> 💁
