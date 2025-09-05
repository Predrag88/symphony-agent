<?php
/**
 * Webhook Deployment Script za server 199.247.1.220
 * Ovaj script omogućava deployment bez GitHub Actions secrets
 * Poziva se direktno sa GitHub webhook-a
 */

// Security token - promeni ovo!
$secret_token = 'your-secret-webhook-token-here';

// Proveri da li je request valjan
if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
    http_response_code(403);
    die('Missing signature header');
}

$payload = file_get_contents('php://input');
$signature = hash_hmac('sha256', $payload, $secret_token);
$expected_signature = 'sha256=' . $signature;

if (!hash_equals($expected_signature, $_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
    http_response_code(403);
    die('Invalid signature');
}

// Decode payload
$data = json_decode($payload, true);

// Proveri da li je push na main branch
if ($data['ref'] !== 'refs/heads/main') {
    http_response_code(200);
    die('Not main branch, skipping deployment');
}

// Log deployment
$log_file = '/var/log/webhook-deploy.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Starting deployment\n", FILE_APPEND);

// Execute deployment script
$output = [];
$return_code = 0;

// Change to project directory and run deployment
exec('cd /var/www/html/symphony-agent && ./deploy.sh 2>&1', $output, $return_code);

// Log results
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Deployment finished with code: $return_code\n", FILE_APPEND);
file_put_contents($log_file, implode("\n", $output) . "\n", FILE_APPEND);

// Return response
if ($return_code === 0) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Deployment completed successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Deployment failed',
        'output' => $output,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

?>