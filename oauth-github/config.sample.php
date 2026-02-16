<?php
session_start();

const GITHUB_CLIENT_ID = 'YOUR_CLIENT_ID_HERE';
const GITHUB_CLIENT_SECRET = 'YOUR_CLIENT_SECRET_HERE';

const GITHUB_REDIRECT_URI = 'http://localhost/php_wt/oauth-github/callback.php';

// GitHub OAuth endpoints
const GITHUB_AUTHORIZE_URL = 'https://github.com/login/oauth/authorize';
const GITHUB_TOKEN_URL = 'https://github.com/login/oauth/access_token';
const GITHUB_USER_API = 'https://api.github.com/user';
const GITHUB_EMAILS_API = 'https://api.github.com/user/emails';
?>
