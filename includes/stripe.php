<?php
declare(strict_types=1);

/**
 * Cliente HTTP mínimo para a API da Stripe via cURL — sem depender do SDK
 * oficial (composer), para facilitar instalação em hospedagem simples.
 * Requer a extensão PHP `curl`.
 *
 * As chaves podem ser definidas no `.env` (recomendado em produção) ou no
 * painel /admin/settings.php; quando preenchido, o `.env` tem prioridade.
 */
function stripe_secret_key(): string
{
    $fromEnv = env('STRIPE_SECRET_KEY', '');
    return $fromEnv !== '' ? (string) $fromEnv : setting('stripe_secret_key');
}

function stripe_public_key(): string
{
    $fromEnv = env('STRIPE_PUBLIC_KEY', '');
    return $fromEnv !== '' ? (string) $fromEnv : setting('stripe_public_key');
}

function stripe_webhook_secret(): string
{
    $fromEnv = env('STRIPE_WEBHOOK_SECRET', '');
    return $fromEnv !== '' ? (string) $fromEnv : setting('stripe_webhook_secret');
}

function stripe_configured(): bool
{
    return stripe_secret_key() !== '';
}

/**
 * @param array<string, mixed> $params
 * @return array<string, mixed>
 */
function stripe_request(string $method, string $endpoint, array $params = []): array
{
    $secretKey = stripe_secret_key();

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
 * Cria uma Checkout Session e retorna ['id' => ..., 'url' => ...]. O `id`
 * é gravado no pedido/compra ANTES do redirecionamento e é o único valor
 * usado depois para verificar o pagamento (ver includes/payments.php) —
 * nunca se confia num session_id vindo da URL do navegador.
 *
 * Quando a moeda configurada é BRL, o Pix é oferecido como forma de
 * pagamento ao lado do cartão (a Stripe só aceita Pix em sessões cobradas
 * em reais, com uma conta Stripe habilitada para o Brasil).
 */
function stripe_create_checkout_session(string $productName, float $amount, string $successUrl, string $cancelUrl): ?array
{
    $currency = strtolower(setting('currency', 'usd'));

    $params = [
        'mode'                                           => 'payment',
        'success_url'                                     => $successUrl,
        'cancel_url'                                       => $cancelUrl,
        'line_items[0][quantity]'                          => 1,
        'line_items[0][price_data][currency]'              => $currency,
        'line_items[0][price_data][unit_amount]'           => (int) round($amount * 100),
        'line_items[0][price_data][product_data][name]'    => $productName,
        'payment_method_types'                             => $currency === 'brl' ? ['card', 'pix'] : ['card'],
    ];

    $result = stripe_request('POST', 'checkout/sessions', $params);

    if (empty($result['id']) || empty($result['url'])) {
        return null;
    }

    return ['id' => (string) $result['id'], 'url' => (string) $result['url']];
}

function stripe_get_checkout_session(string $sessionId): array
{
    return stripe_request('GET', 'checkout/sessions/' . urlencode($sessionId));
}

/**
 * Confere a assinatura de um webhook da Stripe (esquema documentado em
 * stripe.com/docs/webhooks/signatures) sem depender do SDK oficial —
 * evita que qualquer pessoa possa forjar uma notificação de pagamento.
 */
function stripe_verify_webhook_signature(string $payload, string $sigHeader, string $secret): bool
{
    if ($secret === '' || $sigHeader === '') {
        return false;
    }

    $parts = [];
    foreach (explode(',', $sigHeader) as $part) {
        $kv = explode('=', $part, 2);
        if (count($kv) === 2) {
            $parts[trim($kv[0])] = trim($kv[1]);
        }
    }

    $timestamp = $parts['t'] ?? '';
    $signature = $parts['v1'] ?? '';
    if ($timestamp === '' || $signature === '') {
        return false;
    }

    $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    return hash_equals($expected, $signature);
}
