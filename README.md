# Wopi

**Minimum PHP version:** *8.0*

## Installation

```bash
composer require rock-ms/wopi
```

## Publish Migrations

```bash
php artisan vendor:publish --tag=wopi-migrations
```

## Publish Model

```bash
php artisan vendor:publish --tag=wopi-file-model
```

## Publish Configuration

```bash
php artisan vendor:publish --tag=wopi-config
```

## Publish Document Manager

```bash
php artisan vendor:publish --tag=wopi-document-manager
```

## Usage Overview

Goto config/wopi.php and change `document_manager` to newly publish document manager class

WOPI UI component for wopi preview

`<x-wopi-frame id="pass file id here" />`

## Documentation

- **https://learn.microsoft.com/en-us/microsoft-365/cloud-storage-partner-program/online/**

- `There is a community for wopi called Yammer(` **https://techcommunity.microsoft.com/t5/yammer/ct-p/Yammer** `)`

## Test Cases

- Create empty file by extension .wopitest
- Add it in files table
- Pass file id to wopi ui component

## Important

- WOPI does not work on your local instance because your local environment is not registered on Microsoft
- WOPI only works on domains registered with Microsoft

## How to apply for WOPI

- **https://learn.microsoft.com/en-us/microsoft-365/cloud-storage-partner-program/**
- Fill out the form on the given link **https://forms.office.com/Pages/ResponsePage.aspx?id=v4j5cvGGr0GRqy180BHbRwbPdsr48XhFleFl6bDpsG1UMDhVV005Q0RNVUZZRzVYMFk5SzlCWkkzTiQlQCN0PWcu**