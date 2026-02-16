<?php
require __DIR__ . '/config.php';

if (!isset($_GET['code'], $_GET['state'])) {
  exit('Missing code/state.');
}

// Validate state (CSRF)
if (!hash_equals($_SESSION['oauth_state'] ?? '', $_GET['state'])) {
  exit('Invalid state. Possible CSRF.');
}

$code = $_GET['code'];

// 1) Exchange code for access token
$tokenResponse = githubPostJson(GITHUB_TOKEN_URL, [
  'client_id' => GITHUB_CLIENT_ID,
  'client_secret' => GITHUB_CLIENT_SECRET,
  'code' => $code,
  'redirect_uri' => GITHUB_REDIRECT_URI,
]);

if (!isset($tokenResponse['access_token'])) {
  exit('Failed to get access token: ' . htmlspecialchars(json_encode($tokenResponse)));
}

$accessToken = $tokenResponse['access_token'];

// 2) Fetch user profile
$user = githubGetJson(GITHUB_USER_API, $accessToken);

// 3) Fetch primary email (GitHub may not include email in /user)
$emails = githubGetJson(GITHUB_EMAILS_API, $accessToken);
$primaryEmail = null;

if (is_array($emails)) {
  foreach ($emails as $e) {
    if (($e['primary'] ?? false) === true && ($e['verified'] ?? false) === true) {
      $primaryEmail = $e['email'] ?? null;
      break;
    }
  }
  // fallback: first verified email
  if (!$primaryEmail) {
    foreach ($emails as $e) {
      if (($e['verified'] ?? false) === true) {
        $primaryEmail = $e['email'] ?? null;
        break;
      }
    }
  }
}

$_SESSION['user'] = [
  'provider' => 'github',
  'id' => $user['id'] ?? null,
  'login' => $user['login'] ?? null,
  'name' => $user['name'] ?? ($user['login'] ?? ''),
  'email' => $primaryEmail,
  'avatar_url' => $user['avatar_url'] ?? null,
];

header('Location: dashboard.php');
exit;


// ---------- helpers ----------
function githubPostJson(string $url, array $data): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_HTTPHEADER => [
      'Accept: application/json',
    ],
    CURLOPT_TIMEOUT => 20,
  ]);

  $raw = curl_exec($ch);
  if ($raw === false) {
    $err = curl_error($ch);
    curl_close($ch);
    exit('cURL error: ' . htmlspecialchars($err));
  }

  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $json = json_decode($raw, true);
  if (!is_array($json)) {
    exit("Invalid JSON from GitHub. HTTP $status. Raw: " . htmlspecialchars($raw));
  }
  return $json;
}

function githubGetJson(string $url, string $token): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      'Authorization: Bearer ' . $token,
      'User-Agent: XAMPP-PHP-OAuth-App',  // REQUIRED by GitHub API
      'Accept: application/vnd.github+json',
    ],
    CURLOPT_TIMEOUT => 20,
  ]);

  $raw = curl_exec($ch);
  if ($raw === false) {
    $err = curl_error($ch);
    curl_close($ch);
    exit('cURL error: ' . htmlspecialchars($err));
  }

  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $json = json_decode($raw, true);
  if (!is_array($json)) {
    exit("Invalid JSON. HTTP $status. Raw: " . htmlspecialchars($raw));
  }

  // GitHub error shape
  if (isset($json['message']) && $status >= 400) {
    exit("GitHub API error HTTP $status: " . htmlspecialchars($json['message']));
  }

  return $json;
}
?>