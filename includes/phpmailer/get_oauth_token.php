<?php

/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.5
 * @package PHPMailer
 * @see https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Get an OAuth2 token from an OAuth2 provider.
 * * Install this script on your server so that it's accessible
 * as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
 * e.g.: http://localhost/phpmailer/get_oauth_token.php
 * * Ensure dependencies are installed with 'composer install'
 * * Set up an app in your Google/Yahoo/Microsoft account
 * * Set the script address as the app's redirect URL
 * If no refresh token is obtained when running this file,
 * revoke access to your app and run the script again.
 */

namespace PHPMailer\PHPMailer;

/**
 * Aliases for League Provider Classes
 * Make sure you have added these to your composer.json and run `composer install`
 * Plenty to choose from here:
 * @see http://oauth2-client.thephpleague.com/providers/thirdparty/
 */
//@see https://github.com/thephpleague/oauth2-google
use League\OAuth2\Client\Provider\Google;
//@see https://packagist.org/packages/hayageek/oauth2-yahoo
use Hayageek\OAuth2\Client\Provider\Yahoo;
//@see https://github.com/stevenmaguire/oauth2-microsoft
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

if (!isset($_GET['code']) && !isset($_GET['provider'])) {
    ?>
<html>
<body>Select Provider:<br>
<a href='?provider=Google'>Google</a><br>
<a href='?provider=Yahoo'>Yahoo</a><br>
<a href='?provider=Microsoft'>Microsoft/Outlook/Hotmail/Live/Office365</a><br>
</body>
</html>
    <?php
    exit;
}

//require 'vendor/autoload.php';
require '../../autoload.php';

session_start();

$providerName = '';

if (array_key_exists('provider', $_GET)) {
    $providerName = $_GET['provider'];
    $_SESSION['provider'] = $providerName;
} elseif (array_key_exists('provider', $_SESSION)) {
    $providerName = $_SESSION['provider'];
}
if (!in_array($providerName, ['Google', 'Microsoft', 'Yahoo'])) {
    exit('Only Google, Microsoft and Yahoo OAuth2 providers are currently supported in this script.');
}

//These details are obtained by setting up an app in the Google developer console,
//or whichever provider you're using.
//$clientId = 'RANDOMCHARS-----duv1n2.apps.googleusercontent.com';
//$clientSecret = 'RANDOMCHARS-----lGyjPcRtvP';

// revdev
//$clientId = '623603562289-no5ll7oss4rss40coa038sjl61u9v218.apps.googleusercontent.com';
//$clientSecret = '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';
//$redirectUri = 'https://revdev.woodsware.com/includes/phpmailer/vendor/phpmailer/phpmailer/get_oauth_token.php';

// revdevbible (stf server)
//$clientId = '1088583187107-78tsn1r5htrvaqe58j3ac6vt8qghp13f.apps.googleusercontent.com';
//$clientSecret = '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';
//$redirectUri = 'https://www.revdevbible.com/includes/phpmailer/vendor/phpmailer/phpmailer/get_oauth_token.php';

// revdevbible2 (stf server)
$clientId = '520115560694-99vbq76bv1nl71jqs41ak9qrehljh2sd.apps.googleusercontent.com';
$clientSecret = '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';
//$redirectUri = 'https://www.revdevbible2.com/includes/phpmailer/vendor/phpmailer/phpmailer/get_oauth_token.php';
$redirectUri = 'https://www.revdevbible2.com/includes/phpmailer/get_oauth_token.php';

// live
//$clientId = '141752347047-shs5ggv57kvi1t6o3ac8aelbl5dg5qmq.apps.googleusercontent.com';
//$clientSecret = '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';
//$redirectUri = 'https://www.revisedenglishversion.com/includes/phpmailer/vendor/phpmailer/phpmailer/get_oauth_token.php';

//If this automatic URL doesn't work, set it yourself manually to the URL of this script
//$redirectUri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
//$redirectUri = 'http://localhost/PHPMailer/redirect';

$params = [
    'clientId' => $clientId,
    'clientSecret' => $clientSecret,
    'redirectUri' => $redirectUri,
    'accessType' => 'offline'
];

$options = [];
$provider = null;

switch ($providerName) {
    case 'Google':
        $provider = new Google($params);
        $options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
        break;
    case 'Yahoo':
        $provider = new Yahoo($params);
        break;
    case 'Microsoft':
        $provider = new Microsoft($params);
        $options = [
            'scope' => [
                'wl.imap',
                'wl.offline_access'
            ]
        ];
        break;
}

if (null === $provider) {
    exit('Provider missing');
}

if (!isset($_GET['code'])) {
    //If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
    //Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    unset($_SESSION['provider']);
    exit('Invalid state');
} else {
    unset($_SESSION['provider']);
    //Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken(
        'authorization_code',
        [
        //    'code' => $_GET['code']
        'code' => '4/0AWtgzh4GpjpuzRHxSRPdmlPsLbv71KgdWxFRjJHFmtFxYV1tllX5RVSPt7ht2gZpF4wEnA'
        ]
    );
    //Use this to interact with an API on the users behalf
    //Use this to get a new access token if the old one expires
    echo 'Refresh Token: ', $token->getRefreshToken();
}
