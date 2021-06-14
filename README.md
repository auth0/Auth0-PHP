# Auth0 PHP SDK

[![Build Status](https://img.shields.io/circleci/project/github/auth0/auth0-PHP/master.svg)](https://circleci.com/gh/auth0/auth0-PHP)
[![Code Coverage](https://codecov.io/gh/auth0/auth0-PHP/branch/master/graph/badge.svg)](https://codecov.io/gh/auth0/auth0-PHP)
[![License](https://img.shields.io/packagist/l/auth0/auth0-php)](https://packagist.org/packages/auth0/auth0-PHP)
[![FOSSA Status](https://app.fossa.com/api/projects/custom%2B4989%2Fgit%40github.com%3Aauth0%2Fauth0-PHP.git.svg?type=shield)](https://app.fossa.com/projects/custom%2B4989%2Fgit%40github.com%3Aauth0%2Fauth0-PHP.git?ref=badge_shield)

The Auth0 PHP SDK is a straightforward and rigorously tested library for accessing Auth0's Authentication and Management API endpoints using modern PHP releases. Auth0 enables you to integrate authentication and authorization for your applications rapidly so that you can focus on your core business. [Learn more.](https://auth0.com/why-auth0)

- [1. Requirements](#1-requirements)
- [2. Installation](#2-installation)
- [3. Using the SDK](#3-using-the-sdk)
  - [3.1. Getting Started](#31-getting-started)
  - [3.2. SDK Initialization](#32-sdk-initialization)
  - [3.3. Checking for an active session and returning tokens and user data](#33-checking-for-an-active-session-and-returning-tokens-and-user-data)
  - [3.4. Logging in an end-user](#34-logging-in-an-end-user)
  - [3.5. Logging out an end-user](#35-logging-out-an-end-user)
  - [3.6. Decoding an Id Token](#36-decoding-an-id-token)
  - [3.7. Using the Authentication API](#37-using-the-authentication-api)
  - [3.8. Using the Management API](#38-using-the-management-api)
  - [3.9. Using the Organizations API](#39-using-the-organizations-api)
    - [3.9.1. Initializing the SDK with Organizations](#391-initializing-the-sdk-with-organizations)
    - [3.9.2. Logging in with an Organization](#392-logging-in-with-an-organization)
    - [3.9.3. Accepting user invitations](#393-accepting-user-invitations)
    - [3.9.4. Validation guidance for supporting multiple organizations](#394-validation-guidance-for-supporting-multiple-organizations)
- [4. Documentation](#4-documentation)
- [5. Contributing](#5-contributing)
- [6. Support + Feedback](#6-support--feedback)
- [7. Vulnerability Reporting](#7-vulnerability-reporting)
- [8. What is Auth0?](#8-what-is-auth0)
- [9. License](#9-license)

## 1. Requirements

- PHP 7.4 or 8.0+
- [Composer](https://getcomposer.org/)

## 2. Installation

The supported method of SDK installation is through [Composer](https://getcomposer.org/):

```bash
$ composer require auth0/auth0-php
```

Guidance on setting up Composer can be found in our [documentation](https://auth0.com/docs/libraries/auth0-php#installation).

## 3. Using the SDK

### 3.1. Getting Started

To get started, you'll need to create a [free Auth0 account](https://auth0.com/signup) and register an [Application](https://auth0.com/docs/applications).

### 3.2. SDK Initialization

Begin by instantiating the SDK and passing the appropriate configuration options:

```PHP
<?php
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;

$configuration = new SdkConfiguration(
    // The values below are found in the Auth0 dashboard, under application settings:
    domain: '{{YOUR_TENANT}}.auth0.com',
    clientId: '{{YOUR_APPLICATION_CLIENT_ID}}',
    clientSecret: '{{YOUR_APPLICATION_CLIENT_SECRET}}',

    // This is your application URL that will be used to process the login.
    // Save this URL in the "Allowed Callback URLs" field on the Auth0 dashboard, under application settings.
    redirectUri: 'https://{{YOUR_APPLICATION_CALLBACK_URL}}',
);

$auth0 = new Auth0($configuration);
```

⚠️ **Note:** _You should **never** hard-code these values in a real-world application. Consider using environment variables to store and pass these values to your application._

### 3.3. Checking for an active session and returning tokens and user data

```PHP
<?php
// 🧩 Include the configuration code from the 'SDK Initialization' step above here.

/**
 * Auth0::getCredentials() returns either null if no session is active, or an object.
 */
$session = $auth0->getCredentials();

if ($session !== null) {
    // The Id Token for the user as a string.
    $idToken = $session->idToken;

    // The access token for the user, as a string.
    $accessToken = $session->accessToken;

    // A unix timestamp representing when the access token is expected to expire, as an int.
    $accessTokenExpiration = $session->accessTokenExpiration;

    // A bool; if time() has beyond the value of $accessTokenExpiration, this will be true.
    $accessTokenExpired = $session->accessTokenExpired;

    // A refresh token, if available, as a string.
    $refreshToken = $session->refreshToken;

    // Data about the user as an array.
    $user = $session->user;
}
```

### 3.4. Logging in an end-user

```PHP
<?php
// 🧩 Include the configuration code from the 'SDK Initialization' step above here.

$session = $auth0->getCredentials();

// Is this end-user already signed in?
if ($session === null) {
  // They are not. Redirect the end user to the login page.
  $auth0->login();
  exit;
}
```

### 3.5. Logging out an end-user

When signing out an end-user from your application, it's important to use Auth0's /logout endpoint to sign them out properly:

```PHP
<?php
// 🧩 Include the configuration code from the 'SDK Initialization' step above here.

$session = $auth0->getCredentials();

if ($session) {
  // Clear the end-user's session, and redirect them to the Auth0 /logout endpoint.
  $auth0->logout();
  exit;
}
```

### 3.6. Decoding an Id Token

In instances where you need to manually decode an Id Token, such as a custom API service you've built, you can use the `Auth0::decode()` method:

```PHP
<?php
// 🧩 Include the configuration code from the 'SDK Initialization' step above here.

try {
  $token = $auth0->decode('{{YOUR_ID_TOKEN}}');
} catch (\Auth0\SDK\Exception\InvalidTokenException $exception) {
  die("Unable to decode Id Token; " . $exception->getMessage());
}
```

### 3.7. Using the Authentication API

More advanced applications can access the SDK's full suite of authentication API functions using the `Auth0\SDK\API\Authentication` class:

```PHP
<?php
// 🧩 Include the configuration code from the 'SDK Initialization' step above here.

// Get a configured instance of the Auth0\SDK\API\Authentication class:
$authentication = $auth0->authentication();

// Start a passwordless login:
$auth0->emailPasswordlessStart(/* ...configuration */);
```

### 3.8. Using the Management API

This SDK offers an interface for Auth0's Management API, which, to access, requires an Access Token that is explicitly issued for your tenant's Management API by specifying the corresponding Audience.

The process for retrieving such an Access Token is described in our [documentation](https://auth0.com/docs/libraries/auth0-php/using-the-management-api-with-auth0-php).

```PHP
<?php
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;

$configuration = new SdkConfiguration(
    // 🧩  Include other required configuration options, such as outlined in the 'SDK Initialization' step above here.

    managementToken: '{{YOUR_ACCESS_TOKEN}}'
);

$auth0 = new Auth0($configuration);

// Get a configured instance of the Auth0\SDK\API\Management class:
$management = $auth0->management();
```

⚠️ **Note:** _You should **never** hard-code these values in a real-world application. Consider using environment variables to store and pass these values to your application._

### 3.9. Using the Organizations API

[Organizations](https://auth0.com/docs/organizations) is a set of features that provide better support for developers who build and maintain SaaS and Business-to-Business (B2B) applications.

Using Organizations, you can:

- Represent teams, business customers, partner companies, or any logical grouping of users that should have different ways of accessing your application as organizations.
- Manage their membership in a variety of ways, including user invitation.
- Configure branded, federated login flows for each organization.
- Implement role-based access control, such that users can have different roles when authenticating in the context of various organizations.
- Build administration capabilities into your products, using the Organizations API, so that those businesses can manage their organizations.

Note that Organizations is currently only available to customers on our Enterprise and Startup subscription plans.

#### 3.9.1. Initializing the SDK with Organizations

Configure the SDK with your Organization ID:

```PHP
<?php
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;

$configuration = new SdkConfiguration(
    // 🧩  Include other required configuration options, such as outlined in the 'SDK Initialization' step above here.

    // Found in your Auth0 dashboard, under your organization settings:
    organization: [ '{{YOUR_ORGANIZATION_ID}}' ]
);
```

#### 3.9.2. Logging in with an Organization

With the SDK initialized using your Organization, you can simply use the `Auth0::login()` method as you normally would. Methods throughout the SDK will use the organization you configured in their API calls.

```PHP
<?php
// 🧩 Include the configuration code from the 'Initializing the SDK with Organizations' step above here.

$session = $auth0->getCredentials();

// Is this end-user already signed in?
if ($session === null) {
  // They are not. Redirect the end user to the login page.
  $auth0->login();
  exit;
}
```

#### 3.9.3. Accepting user invitations

Auth0 Organizations allow users to be invited using emailed links, which will direct a user back to your application. The user will be sent to your application URL based on your configured `Application Login URI,` which you can change from your application's settings inside the Auth0 dashboard.

When the user arrives at your application using an invite link, you can expect three query parameters to be provided: `invitation,` `organization,` and `organization_name.` These will always be delivered using a GET request.

A helper function is provided to handle extracting these query parameters and automatically redirecting to the Universal Login page:

```PHP
<?php
// 🧩 Include the configuration code from the 'Initializing the SDK with Organizations' step above here.

$auth0->handleInvitation();
```

Suppose you prefer to have more control over this process. In that case, extract the relevant query parameters using `getInvitationParameters(),` and then initiate the Universal Login redirect yourself:

```PHP
<?php
// 🧩 Include the configuration code from the 'Initializing the SDK with Organizations' step above here.

// Returns an object containing the invitation query parameters, or null if they aren't present
if ($invite = $auth0->getInvitationParameters()) {
  // Does the invite organization match your intended organization?
  if ($invite->organization !== '{{YOUR_ORGANIZATION_ID}}') {
    throw new Exception("This invitation isn't intended for this service. Please have your administrator check the service configuration and request a new invitation.");
  }

  // Redirect to Universal Login using the emailed invitation
  $auth0->login([
    'invitation' => $invite->invitation,
    'organization' => $invite->organization,
  ]);
}
```

After successful authentication via the Universal Login Page, the user will arrive back at your application using your configured `redirect_uri,` their token will be validated, and they will have an authenticated session. Use `Auth0::getCredentials()` to retrieve details about the authenticated user.

#### 3.9.4. Validation guidance for supporting multiple organizations

In the examples above, our application is operating with a single, configured Organization. By initializing the SDK with the `organization` argument as we have, we tell the internal token verifier to validate an `org_id` claim's presence and match what was provided.

In some cases, your application may need to support validating tokens' `org_id` claims for several different organizations. When initializing the SDK, the `organization` argument accepts an array of organizations; during token validation, if ANY of those organization ids match, the token is accepted. When creating links or issuing API calls, the first organization in that array will be used. You can alter this at any time by updating your `SdkConfiguration` or passing custom parameters to those methods.

This should cover most cases, but in the event you need to build a more complex application with custom token validation code, it's crucial your application should an `org_id` claim to ensure the value received is expected and known by your application. If the claim is not valid, your application should reject the token. See [https://auth0.com/docs/organizations/using-tokens](https://auth0.com/docs/organizations/using-tokens) for more information.

## 4. Documentation

- [Documentation](https://auth0.com/docs/libraries/auth0-php)
  - [Installation](https://auth0.com/docs/libraries/auth0-php#installation)
  - [Getting Started](https://auth0.com/docs/libraries/auth0-php#getting-started)
  - [Basic Usage](https://auth0.com/docs/libraries/auth0-php/auth0-php-basic-use)
  - [Authentication API](https://auth0.com/docs/libraries/auth0-php/using-the-authentication-api-with-auth0-php)
  - [Management API](https://auth0.com/docs/libraries/auth0-php/using-the-management-api-with-auth0-php)
  - [Troubleshooting](https://auth0.com/docs/libraries/auth0-php/troubleshoot-auth0-php-library)
- Quickstarts
  - [Basic authentication example](https://auth0.com/docs/quickstart/webapp/php/) ([GitHub repo](https://github.com/auth0-samples/auth0-php-web-app/tree/master/00-Starter-Seed))
  - [Authenticated backend API example](https://auth0.com/docs/quickstart/backend/php/) ([GitHub repo](https://github.com/auth0-samples/auth0-php-api-samples/tree/master/01-Authenticate))

## 5. Contributing

We appreciate your feedback and contributions to the project! Before you get started, please review the following:

- [Auth0's general contribution guidelines](https://github.com/auth0/open-source-template/blob/master/GENERAL-CONTRIBUTING.md)
- [Auth0's code of conduct guidelines](https://github.com/auth0/open-source-template/blob/master/CODE-OF-CONDUCT.md)
- [The Auth0 PHP SDK contribution guide](CONTRIBUTING.md)

## 6. Support + Feedback

- The [Auth0 Community](https://community.auth0.com/) is a valuable resource for asking questions and finding answers, staffed by the Auth0 team and a community of enthusiastic developers
- For code-level support (such as feature requests and bug reports), we encourage you to [open issues](https://github.com/auth0/auth0-PHP/issues) here on our repo
- For customers on [paid plans](https://auth0.com/pricing/), our [support center](https://support.auth0.com/) is available for opening tickets with our knowledgeable support specialists

Further details about our support solutions are [available on our website.](https://auth0.com/docs/support)

## 7. Vulnerability Reporting

Please do not report security vulnerabilities on the public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.

## 8. What is Auth0?

Auth0 helps you to:

- Add authentication with [multiple authentication sources](https://docs.auth0.com/identityproviders), either social like Google, Facebook, Microsoft, LinkedIn, GitHub, Twitter, Box, Salesforce (amongst others), or enterprise identity systems like Windows Azure AD, Google Apps, Active Directory, ADFS or any SAML Identity Provider.
- Add authentication through more traditional **[username/password databases](https://docs.auth0.com/mysql-connection-tutorial)**.
- Add support for [passwordless](https://auth0.com/passwordless) and [multi-factor authentication](https://auth0.com/docs/mfa).
- Add support for [linking different user accounts](https://docs.auth0.com/link-accounts) with the same user.
- Analytics of how, when, and where users are logging in.
- Pull data from other sources and add it to the user profile through [JavaScript rules](https://docs.auth0.com/rules).

[Why Auth0?](https://auth0.com/why-auth0)

## 9. License

The Auth0 PHP SDK is open source software licensed under [the MIT license](https://opensource.org/licenses/MIT). See the [LICENSE](LICENSE.txt) file for more info.

[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fauth0%2Fauth0-PHP.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fauth0%2Fauth0-PHP?ref=badge_large)
