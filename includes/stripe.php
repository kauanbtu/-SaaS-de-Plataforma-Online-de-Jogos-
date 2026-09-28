<?php
declare(strict_types=1);

/**
 * Cliente HTTP mínimo para a API da Stripe via cURL — sem depender do SDK
 * oficial (composer), para facilitar instalação em hospedagem simples.
 * Requer a extensão PHP `curl`.
 */
function stripe_configured(): bool
{
    return setting('stripe_secret_key') !== '';
}

/**
 * @param array<string, mixed> $params
 * @return array<string, mixed>
 */
function stripe_request(string $method, string $endpoint, array $params = []): array
{
    $secretKey = setting('stripe_secret_key');

    $url = 'https://api.stripe.com/v1/' . ltrim($endpoint, '/');
    if ($method === 'GET' && $params) {
        $url .= '?' . http_build_query($params);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => $secretKey . ':',
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_TIMEOUT        => 20,
    ]);

    if ($method !== 'GET' && $params) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('Falha ao comunicar com a Stripe: ' . $error);
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Cria uma Checkout Session e retorna a URL para redirecionar o comprador.
 */
function stripe_create_checkout_session(string $productName, float $amountUsd, string $successUrl, string $cancelUrl): ?string
{
    $result = stripe_request('POST', 'checkout/sessions', [
        'mode'                       => 'payment',
        'success_url'                => $successUrl,
        'cancel_url'                 => $cancelUrl,
        'line_items[0][quantity]'    => 1,
        'line_items[0][price_data][currency]'                => strtolower(setting('currency', 'usd')),
        'line_items[0][price_data][unit_amount]'              => (int) round($amountUsd * 100),
        'line_items[0][price_data][product_data][name]'       => $productName,
    ]);

    return $result['url'] ?? null;
}

function stripe_get_checkout_session(string $sessionId): array
{
    return stripe_request('GET', 'checkout/sessions/' . urlencode($sessionId));
}
